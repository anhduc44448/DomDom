<?php
require_once '../config.php';

$app_trans_id = $_GET['apptransid'] ?? '';
$zp_trans_id = $_GET['zptransid'] ?? '';

$status = 'Unknown';
$message = '';
$order_details = [];
$order_items = [];

$preparation_map = [
    'preparing' => 'Đang chuẩn bị',
    'completed' => 'Hoàn thành',
    'cancelled' => 'Đã hủy'
];

if ($app_trans_id) {
    // Get payment and order details
    $stmt = $conn->prepare("
        SELECT p.*,
               o.total_amount, o.status AS order_status, o.order_date,
               o.customer_name, o.table_number, o.customer_note
        FROM payments p
        JOIN orders o ON p.order_id = o.id
        WHERE p.transaction_id = ?
    ");

    if ($stmt) {
        $stmt->bind_param("s", $app_trans_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result->fetch_assoc();

        if ($payment) {
            $order_details = $payment;
            if ($payment['payment_status'] == 'success') {
                $status = 'Thành công';
                $message = 'Thanh toán đã được xử lý thành công.';
            } elseif ($payment['payment_status'] == 'failed') {
                $status = 'Thất bại';
                $message = 'Thanh toán thất bại.';
            } elseif ($payment['payment_status'] == 'cancelled') {
                $status = 'Đã hủy';
                $message = 'Thanh toán đã bị hủy.';
            } else {
                $status = 'Đang chờ';
                $message = 'Thanh toán đang được xử lý. Vui lòng kiểm tra lại sau.';
            }

            // Get order items
            $order_id = $payment['order_id'];
            $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
            if ($items_stmt) {
                $items_stmt->bind_param("i", $order_id);
                $items_stmt->execute();
                $items_result = $items_stmt->get_result();
                $order_items = $items_result->fetch_all(MYSQLI_ASSOC);
                $items_stmt->close();
            }
        } else {
            $status = 'Không tìm thấy';
            $message = 'Không tìm thấy đơn hàng này.';
        }

        $stmt->close();
    } else {
        $status = 'Lỗi';
        $message = 'Có lỗi xảy ra khi kiểm tra đơn hàng.';
        error_log("Redirect error: " . $conn->error);
    }
} else {
    $status = 'Thiếu thông tin';
    $message = 'Thiếu thông tin đơn hàng.';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="redirect.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/menu.css">
</head>
<body>

<div class="content-wrapper">
    <div class="status-card">
        <div class="status-header">
            <h1>Kết quả thanh toán</h1>
            <div class="status-result">Kết quả: <?php echo $status; ?></div>
        </div>

        <div class="status-message">
            <div class="alert <?php
                $alertClass = '';
                if ($status == 'Thành công') $alertClass = 'status-success';
                elseif ($status == 'Thất bại') $alertClass = 'status-failed';
                else $alertClass = 'status-warning';
                echo $alertClass;
            ?>">
                <i class="fas status-icon <?php
                    if ($status == 'Thành công') echo 'fa-check-circle';
                    elseif ($status == 'Thất bại') echo 'fa-times-circle';
                    else echo 'fa-clock';
                ?>"></i>
                <span><?php echo $message; ?></span>
            </div>
        </div>

        <?php if ($order_details): ?>
        <div class="order-details">
            <h3 class="section-title">Chi tiết đơn hàng:</h3>
            <div class="detail-list">
                <div class="detail-item"><strong>Mã giao dịch:</strong> <?php echo htmlspecialchars($order_details['transaction_id']); ?></div>
                <div class="detail-item"><strong>Zp Trans ID:</strong> <?php echo htmlspecialchars($zp_trans_id ?: 'N/A'); ?></div>
                <div class="detail-item"><strong>Số tiền:</strong> <?php echo number_format($order_details['amount']); ?> VND</div>
                <div class="detail-item"><strong>Ngày đặt hàng:</strong> <?php echo $order_details['order_date']; ?></div>
                <div class="detail-item"><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order_details['customer_name']); ?></div>
                <div class="detail-item"><strong>Số bàn:</strong> <?php echo htmlspecialchars($order_details['table_number'] ?: 'N/A'); ?></div>
                <div class="detail-item"><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order_details['customer_note'] ?: 'Không có'); ?></div>
                <div class="detail-item"><strong>Trạng thái:</strong> <?php echo htmlspecialchars($order_details['payment_status'] ?: 'Chưa cập nhật'); ?></div>
            </div>

            <?php if (!empty($order_items)): ?>
            <h3 class="section-title">Danh sách món ăn:</h3>
            <div class="items-list">
                <?php foreach ($order_items as $item): ?>
                <div class="item">
                    <div>
                        <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div class="item-details">Số lượng: <?php echo $item['quantity']; ?> - Size: <?php echo htmlspecialchars($item['size']); ?></div>
                    </div>
                    <div class="item-price"><?php echo number_format($item['total_price']); ?> VND</div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($order_details): ?>
        <div class="order-status-highlight">
            <h3>Trạng thái đơn hàng</h3>
            <div id="order-status-display" class="status-badge status-<?php echo $order_details['order_status']; ?>"><?php echo ($preparation_map[$order_details['order_status']] ?? $order_details['order_status']); ?></div>
        </div>
        <?php endif; ?>

        <div class="back-button-container">
            <a href="../index.html" class="btn btn-success">Quay về trang chính</a>
        </div>
    </div>
</div>

  <footer>
    <div class="container">
      <p>&copy; 2024 Đom đóm quán</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <?php if ($status == 'Thành công'): ?>
  <script>
      // Clear cart on successful payment
      localStorage.removeItem("cart");
  </script>
  <?php endif; ?>
  <?php if ($order_details): ?>
  <script>
  function updateStatus() {
      fetch('get_status.php?apptransid=<?php echo urlencode($app_trans_id); ?>')
          .then(response => response.json())
          .then(data => {
              if (data.order_status_text) {
                  const orderStatusDisplay = document.getElementById('order-status-display');
                  orderStatusDisplay.textContent = data.order_status_text;
                  // Reset classes and add the current one
                  orderStatusDisplay.className = 'status-badge status-' + data.order_status;
              }
          })
          .catch(error => console.error('Error updating status:', error));
  }

  // Update every 5 seconds
  setInterval(updateStatus, 5000);
  </script>
  <?php endif; ?>
</body>
</html>
