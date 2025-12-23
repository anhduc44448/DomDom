<?php
header('Content-Type: application/json');

// Register shutdown function to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        // Clear any previous output (like partial HTML)
        if (ob_get_length()) ob_clean();
        echo json_encode(['success' => false, 'message' => 'Fatal Error: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']]);
    }
});

// Start output buffering to catch unwanted output
ob_start();

// Disable display errors to user, we handle it via JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require '../config.php';

function sendError($message, $debug = null) {
    // Clear buffer
    if (ob_get_length()) ob_clean();
    echo json_encode(['success' => false, 'message' => $message, 'debug' => $debug]);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    sendError('No data received or invalid JSON', $input);
}

// 1. Tạo order
if (!isset($conn)) {
    sendError('Database connection failed (conn variable missing)');
}

$stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, table_number, customer_note, total_amount) 
                        VALUES (?, ?, ?, ?, ?)");

if (!$stmt) {
    sendError('Prepare failed (orders): ' . $conn->error);
}

$userId = isset($data['user_id']) ? $data['user_id'] : null;
$customerName = isset($data['customer_name']) ? $data['customer_name'] : '';
$tableNumber = isset($data['table_number']) ? $data['table_number'] : '';
$customerNote = isset($data['customer_note']) ? $data['customer_note'] : '';
$totalAmount = isset($data['total_amount']) ? $data['total_amount'] : 0;

if (!$stmt->bind_param("isssd", 
    $userId,
    $customerName,
    $tableNumber,
    $customerNote,
    $totalAmount
)) {
    sendError('Bind param failed: ' . $stmt->error);
}

if (!$stmt->execute()) {
    sendError('Execute failed (orders): ' . $stmt->error);
}
$order_id = $conn->insert_id;
$stmt->close();

// 2. Lưu items
foreach ($data['items'] as $item) {
    $stmt2 = $conn->prepare("INSERT INTO order_items 
                            (order_id, product_name, quantity, size, unit_price, total_price) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt2) {
        sendError('Prepare failed (items): ' . $conn->error);
    }
    
    $productName = $item['product_name'];
    $quantity = $item['quantity'];
    $size = $item['size'];
    $unitPrice = $item['unit_price'];
    $totalPrice = $item['total_price'];

    $stmt2->bind_param("isisdd", 
        $order_id,
        $productName,
        $quantity,
        $size,
        $unitPrice,
        $totalPrice
    );
    
    if (!$stmt2->execute()) {
        sendError('Execute failed (items): ' . $stmt2->error);
    }
    $stmt2->close();
}

// 3. ZaloPay Integration
$config = [
    "appid" => 2554,
    "key1" => "sdngKKJmqEMzvh5QQcdD2A9XBSKUNaYn",
    "key2" => "trMrHtvjo6myautxDUiAcYsVtaeQ8nhf",
    "endpoint" => "https://sb-openapi.zalopay.vn/v2/create"
];

$embeddata = json_encode(["redirecturl" => "https://unswilled-shantel-domiciliary.ngrok-free.dev/domdom/paymentModule/redirect.php"]); 
$items = '[]'; 
$transID = time() . rand(1000,9999);
$order_code = date("ymd") . "_" . $transID;

$zp_order = [
    "app_id" => $config["appid"],
    "app_time" => round(microtime(true) * 1000), 
    "app_trans_id" => $order_code, 
    "app_user" => "user123",
    "item" => $items,
    "embed_data" => $embeddata,
    "amount" => $totalAmount,
    "description" => "Thanh toan don hang #$order_id",
    "bank_code" => "",
    "callback_url" => "https://unswilled-shantel-domiciliary.ngrok-free.dev/domdom/paymentModule/callback.php",
];

$data_string = $zp_order["app_id"] . "|" . $zp_order["app_trans_id"] . "|" . $zp_order["app_user"] . "|" . $zp_order["amount"]
    . "|" . $zp_order["app_time"] . "|" . $zp_order["embed_data"] . "|" . $zp_order["item"];
$zp_order["mac"] = hash_hmac("sha256", $data_string, $config["key1"]);

// Try using Curl if file_get_contents fails or unavailable
$resp = null;

if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config["endpoint"]);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($zp_order));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Disable SSL check for sandbox/dev if needed, but better keep it
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $resp = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($resp === false) {
        sendError('Curl failed: ' . $curl_error);
    }
} else {
    // Fallback to file_get_contents
    $context = stream_context_create([
        "http" => [
            "header" => "Content-type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => http_build_query($zp_order)
        ]
    ]);
    
    $resp = @file_get_contents($config["endpoint"], false, $context);
    
    if ($resp === false) {
        $error = error_get_last();
        sendError('file_get_contents failed: ' . ($error['message'] ?? 'Unknown error'));
    }
}

$result = json_decode($resp, true);

if(isset($result["return_code"]) && $result["return_code"] == 1){
    // Track payment transaction
    $stmt3 = $conn->prepare("INSERT INTO payments (order_id, payment_method, payment_status, transaction_id, amount, payment_date) VALUES (?, ?, ?, ?, ?, NOW())");
    if ($stmt3) {
        $method = 'zalopay';
        $status = 'pending';
        $stmt3->bind_param("iissd", $order_id, $method, $status, $order_code, $totalAmount);
        $stmt3->execute();
        $stmt3->close();
    }

    if (ob_get_length()) ob_clean();
    echo json_encode([
        'success' => true, 
        'order_id' => $order_id,
        'order_url' => $result["order_url"]
    ]);
} else {
    if (ob_get_length()) ob_clean();
    echo json_encode([
        'success' => true, 
        'order_id' => $order_id,
        'message' => 'Order created but ZaloPay init failed: ' . ($result['return_message'] ?? 'Unknown error'),
        'zp_error' => $result
    ]);
}

$conn->close();
ob_end_flush();
?>
