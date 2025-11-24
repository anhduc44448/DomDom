<?php
require_once '../config.php';

$result = [];

try {
  $key2 = "trMrHtvjo6myautxDUiAcYsVtaeQ8nhf";
  $postdata = file_get_contents('php://input');
  $postdatajson = json_decode($postdata, true);
  $mac = hash_hmac("sha256", $postdatajson["data"], $key2);

  $requestmac = $postdatajson["mac"];

  // Log callback data
  error_log("Callback received: " . json_encode($postdatajson) . "\n", 3, __DIR__ . '/callback_log.log');

  // kiểm tra callback hợp lệ (đến từ ZaloPay server)
  if (strcmp($mac, $requestmac) != 0) {
    // callback không hợp lệ
    $result["return_code"] = -1;
    $result["return_message"] = "mac not equal";
  } else {
    // merchant cập nhật trạng thái cho đơn hàng
    $datajson = json_decode($postdatajson["data"], true);

    // Log datajson
    error_log("Datajson decoded: " . json_encode($datajson) . "\n", 3, __DIR__ . '/callback_log.log');

    // Set status to success since callback is valid
    $payment_status = 'success';

    // Log payment status
    error_log("Set payment_status to: $payment_status for trans_id: " . $datajson["app_trans_id"] . "\n", 3, __DIR__ . '/callback_log.log');

    // Update Payments table
    $stmt = $conn->prepare("UPDATE payments SET payment_status=?, zp_trans_id=? WHERE transaction_id=?");
    if ($stmt) {
      $stmt->bind_param("sss", $payment_status, $datajson["zp_trans_id"], $datajson["app_trans_id"]);
      $stmt->execute();
      $stmt->close();
    }

    // Update Orders status if successful
    if ($payment_status === 'success') {
      $update_stmt = $conn->prepare("UPDATE orders SET status='preparing' WHERE id IN (SELECT order_id FROM payments WHERE transaction_id=?)");
      if ($update_stmt) {
        $update_stmt->bind_param("s", $datajson["app_trans_id"]);
        $update_stmt->execute();
        $update_stmt->close();
      }
    }

    $result["return_code"] = 1;
    $result["return_message"] = "success";
  }
} catch (Exception $e) {
  $result["return_code"] = 0; // ZaloPay server sẽ callback lại (tối đa 3 lần)
  $result["return_message"] = $e->getMessage();
}

// thông báo kết quả cho ZaloPay server
echo json_encode($result);
