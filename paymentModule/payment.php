<?php
require_once '../config.php';

$config = [
    "appid" => 2554,
    "key1" => "sdngKKJmqEMzvh5QQcdD2A9XBSKUNaYn",
    "key2" => "trMrHtvjo6myautxDUiAcYsVtaeQ8nhf",
    "endpoint" => "https://sb-openapi.zalopay.vn/v2/create"
];

$embeddata = json_encode(["redirecturl" => "https://unswilled-shantel-domiciliary.ngrok-free.dev/domdom/paymentModule/redirect.php"]); // Merchant's data, page to show after payment

$items = '[]'; // Merchant's data
$transID = time() . rand(1000,9999); //Unique trans id based on time

$amount = $_POST['amount'] ?? 50000; // From form, default for test
$description = $_POST['description'] ?? "Đơn hàng test #$transID";

// Get customer data from POST (passed via form submission)
$customerData = json_decode($_POST['customer_data'] ?? '{}', true);
$customerName = isset($customerData['name']) ? trim($customerData['name']) : 'Khách hàng';
$customerTable = isset($customerData['tableNumber']) ? trim($customerData['tableNumber']) : '';
$customerNote = isset($customerData['note']) ? trim($customerData['note']) : '';

// Insert into Orders
$name = $customerName;
$table = $customerTable;
$note = $customerNote;
$amt = $amount;

$stmt = $conn->prepare("INSERT INTO orders (customer_name, table_number, customer_note, total_amount, status) VALUES (?, ?, ?, ?, 'pending')");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("sssd", $name, $table, $note, $amt);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$orderId = $conn->insert_id;
$stmt->close();

$order = [
    "app_id" => $config["appid"],
    "app_time" => round(microtime(true) * 1000), // miliseconds
    "app_trans_id" => date("ymd") . "_" . $transID, // translation missing: vi.docs.shared.sample_code.comments.app_trans_id
    "app_user" => "user123",
    "item" => $items,
    "embed_data" => $embeddata,
    "amount" => $amount,
    "description" => $description,
    "bank_code" => "",
    "callback_url" => "https://unswilled-shantel-domiciliary.ngrok-free.dev/domdom/paymentModule/callback.php",
];

// appid|app_trans_id|appuser|amount|apptime|embeddata|item
$data = $order["app_id"] . "|" . $order["app_trans_id"] . "|" . $order["app_user"] . "|" . $order["amount"]
    . "|" . $order["app_time"] . "|" . $order["embed_data"] . "|" . $order["item"];
$order["mac"] = hash_hmac("sha256", $data, $config["key1"]);

$context = stream_context_create([
    "http" => [
        "header" => "Content-type: application/x-www-form-urlencoded\r\n",
        "method" => "POST",
        "content" => http_build_query($order)
    ]
]);

$resp = file_get_contents($config["endpoint"], false, $context);
$result = json_decode($resp, true);

if(isset($result["return_code"]) && $result["return_code"] == 1){
    // Insert into Payments only if creation success
    $oid = $orderId;
    $method = 'zalopay';
    $status = 'pending';
    $trans_id = $order["app_trans_id"];
    $amt2 = $order["amount"];

    $stmt2 = $conn->prepare("INSERT INTO payments (order_id, payment_method, payment_status, transaction_id, amount, payment_date) VALUES (?, ?, ?, ?, ?, NOW())");
    if (!$stmt2) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt2->bind_param("iissd", $oid, $method, $status, $trans_id, $amt2);
    if (!$stmt2->execute()) {
        die("Execute failed: " . $stmt2->error);
    }
    $stmt2->close();

    header("Location:".$result["order_url"]);
    exit;
} else {
    // Payment creation failed, update orders status
    $stmt_fail = $conn->prepare("UPDATE orders SET status='payment_failed' WHERE id=?");
    if ($stmt_fail) {
        $stmt_fail->bind_param("i", $orderId);
        $stmt_fail->execute();
        $stmt_fail->close();
    }

    foreach ($result as $key => $value) {
        echo "$key: $value<br>";
    }
    exit;
}
