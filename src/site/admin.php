<?php
// admin.php - Trang quản trị (Dashboard)
require_once __DIR__ . '/db.php';
session_start();

// Bắt buộc đăng nhập + đúng quyền Admin
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
if ((int)($_SESSION['role_id'] ?? 0) !== 1) {
  header('Location: home.php');
  exit;
}

$hoTen = $_SESSION['hoTen'] ?? 'Admin';
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Trang quản trị</title>

  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  
  <link rel="stylesheet" href="css/styleadmin.css">
</head>
<body>
  <header class="topbar">
    <div class="container admin-container">
      <div class="title">
        <h1>Trang quản trị</h1>
        <p class="sub">Xin chào, <b><?= htmlspecialchars($hoTen) ?></b></p>
      </div>

      <nav class="nav">
        <a class="nav-link" href="home.php">Về trang chủ</a>
      </nav>
    </div>
  </header>

  <main class="container admin-container">
    <div class="row g-3">
      <div class="col-12 col-md-6 col-lg-4">
        <a class="card h-100" href="admin_products.php">
          <div class="card-title">Quản lý sản phẩm</div>
          <div class="card-desc">Thêm / sửa / xoá sản phẩm, và thông tin cấu hình.</div>
          <div class="card-cta">Mở trang</div>
        </a>
      </div>

      <div class="col-12 col-md-6 col-lg-4">
        <a class="card h-100" href="admin_order.php">
          <div class="card-title">Quản lý đơn hàng</div>
          <div class="card-desc">Xem danh sách đơn, cập nhật thông tin giao hàng, xoá đơn.</div>
          <div class="card-cta">Mở trang</div>
        </a>
      </div>

      <div class="col-12 col-md-6 col-lg-4">
        <a class="card h-100" href="admin_brands.php">
          <div class="card-title">Quản lý danh mục</div>
          <div class="card-desc">Xem danh mục, thêm xóa danh mục.</div>
          <div class="card-cta">Mở trang</div>
        </a>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="container admin-container">© - Trang Admin</div>
  </footer>
</body>
</html>
