<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/db.php';

// Bắt buộc đăng nhập
if (empty($_SESSION['user_id'])) {
  header('Location: login.php?next=' . urlencode('orderdetail.php'));
  exit;
}

$userId = (int)$_SESSION['user_id'];

// Lấy order_id "vừa đặt" (ưu tiên session), nếu không có thì lấy đơn mới nhất của user
$orderId = isset($_SESSION['last_order_id']) ? (int)$_SESSION['last_order_id'] : 0;

if ($orderId <= 0) {
  $st = $pdo->prepare("SELECT order_id FROM orders WHERE user_id = ? ORDER BY order_id DESC LIMIT 1");
  $st->execute([$userId]);
  $orderId = (int)($st->fetchColumn() ?: 0);
}

// Không có đơn nào
$order = null;
$items = [];
$tongTien = 0;

if ($orderId > 0) {
  // Lấy thông tin đơn
  $st = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
  $st->execute([$orderId, $userId]);
  $order = $st->fetch(PDO::FETCH_ASSOC);

  if ($order) {
    // Lấy chi tiết sản phẩm trong đơn
    $st = $pdo->prepare("
      SELECT 
        oi.product_id,
        oi.soLuong   AS quantity,
        oi.donGia    AS price,
        oi.thanhTien AS line_total,
        p.tenSp,
        pi.hinhAnh
      FROM order_items oi
      JOIN products p ON p.product_id = oi.product_id
      LEFT JOIN product_images pi ON pi.product_id = p.product_id
      WHERE oi.order_id = ?
      ORDER BY oi.item_id ASC
    ");
    $st->execute([$orderId]);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $it) {
      // Ưu tiên dùng thành tiền đã chốt trong DB; nếu thiếu thì tính lại
      if (isset($it['line_total'])) {
        $tongTien += (float)$it['line_total'];
      } else {
        $tongTien += ((float)($it['price'] ?? 0) * (int)($it['quantity'] ?? 0));
      }
    }
  }
}

if (!function_exists('vnd')) {
  function vnd($n) {
    return number_format((float)$n, 0, ',', '.') . 'đ';
  }
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đơn hàng</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <!-- CSS của bạn -->
  <style>
    /* CSS rất nhẹ để nhìn rõ - nếu bạn muốn, bạn có thể chuyển sang style.css */
    .wrap-order { max-width: 1100px; margin: 30px auto; padding: 0 16px; }
    .box { background: #fff; border: 1px solid #eee; border-radius: 10px; padding: 16px; margin-bottom: 16px; }
    .row { display: flex; gap: 16px; flex-wrap: wrap; }
    .col { flex: 1; min-width: 260px; }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { padding: 10px; border-bottom: 1px solid #eee; vertical-align: middle; }
    .sp { display: flex; align-items: center; gap: 10px; }
    .sp img { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
    .muted { color: #666; }
    .right { text-align: right; }
    .btn { display: inline-block; padding: 10px 14px; border-radius: 8px; border: 1px solid #ddd; text-decoration: none; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg" id="header">
        <div class="container-fluid px-3">
        <a href="home.php"> <img style="width: 70px; border-radius: 50%; margin-left: 25px;" src="images/P.jpg" class="logo navbar-brand d-flex align-items-center gap-2"> S Phone</a>

        <button class="navbar-toggler" type="button"data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul id="navbar" class="navbar-nav ms-auto align-items-lg-center gap-lg-3">

                <li class="nav-item"><a  href="home.php">Home</a></li>

                <li class="nav-item"><a href="product.php">Sản phẩm</a></li>

                <li id="lg-bag" class="nav-item"><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i>Giỏ hàng</a></li>

                <li id="oi-bag" class="nav-item"><a class=" active" href="orderdetail.php"><i class="fa-solid fa-receipt"></i>Đơn hàng</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><span class="user-name">👤 <?= htmlspecialchars($_SESSION['hoTen'] ?? '') ?></span></li>
                <?php else: ?>
                    <li class="nav-item"><a class="login" href="login.php">Đăng nhập</a></li>
                <?php endif; ?>

            </ul>
        </div>
    </nav>
<div class="wrap-order">
  <h2 style="margin: 0 0 12px 0;">Đơn hàng vừa đặt</h2>

  <?php if (!$order): ?>
    <div class="box">
      <p class="muted" style="margin:0;">
        Bạn chưa có đơn hàng nào, hoặc không tìm thấy “đơn vừa đặt”.
      </p>
      <div style="margin-top:12px;">
        <a class="btn" href="product.php">Tiếp tục mua sắm</a>
      </div>
    </div>
  <?php else: ?>
    <div class="box">
      <div class="row">
        <div class="col">
          <div><b>Mã đơn:</b> #<?= (int)$order['order_id'] ?></div>
          <div class="muted"><b>Ngày đặt:</b> <?= htmlspecialchars($order['ngayDat'] ?? '') ?></div>
          <div class="muted"><b>Trạng thái:</b> Đã đặt</div>
        </div>

        <div class="col">
          <div><b>Người nhận:</b> <?= htmlspecialchars($order['tenKH'] ?? ($_SESSION['hoTen'] ?? '')) ?></div>
          <div class="muted"><b>SĐT:</b> <?= htmlspecialchars($order['sdtKH'] ?? '') ?></div>
          <div class="muted"><b>Địa chỉ:</b> <?= htmlspecialchars($order['diaChi'] ?? '') ?></div>
        </div>
      </div>
    </div>

    <div class="box">
      <h3 style="margin: 0 0 10px 0;">Sản phẩm</h3>
      <table class="table">
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th class="right">Đơn giá</th>
            <th class="right">SL</th>
            <th class="right">Thành tiền</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <?php
              $sl = (int)$it['quantity'];
              $gia = (float)$it['price'];
              $tt  = $gia * $sl;
            ?>
            <tr>
              <td>
                <div class="sp">
                  <img src="/site/<?= htmlspecialchars(ltrim($it['hinhAnh'] ?? '', '/')) ?>" alt="">
                  <div>
                    <div><b><?= htmlspecialchars($it['tenSp'] ?? '') ?></b></div>
                    <div class="muted">#<?= (int)$it['product_id'] ?></div>
                  </div>
                </div>
              </td>
              <td class="right"><?= vnd($gia) ?></td>
              <td class="right"><?= $sl ?></td>
              <td class="right"><b><?= vnd($tt) ?></b></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="display:flex; justify-content:flex-end; margin-top:12px;">
        <div style="min-width: 280px;">
          <div style="display:flex; justify-content:space-between;">
            <span class="muted">Tạm tính</span>
            <span><?= vnd($tongTien) ?></span>
          </div>
          <div style="display:flex; justify-content:space-between; margin-top:8px; font-size:18px;">
            <b>Tổng</b>
            <b><?= vnd($order['tongTien'] ?? $tongTien) ?></b>
          </div>
        </div>
      </div>

      <div style="margin-top:14px;">
        <a class="btn" href="product.php">Mua thêm</a>
        <a class="btn" href="cart.php" style="margin-left:8px;">Về giỏ hàng</a>
      </div>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
