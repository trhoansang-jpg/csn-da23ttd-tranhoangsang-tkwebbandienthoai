<?php
session_start();
require_once __DIR__ . '/db.php';

// ===== Helpers kiểm tra schema (tránh lỗi thiếu cột/bảng) =====
function table_exists(PDO $pdo, string $table): bool {
    $st = $pdo->prepare("SHOW TABLES LIKE ?");
    $st->execute([$table]);
    return (bool)$st->fetchColumn();
}

function column_exists(PDO $pdo, string $table, string $col): bool {
    $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $st->execute([$col]);
    return (bool)$st->fetch(PDO::FETCH_ASSOC);
}


$q = trim($_GET['q'] ?? '');
$products = [];

if ($q !== '') {
    // Chọn ảnh đại diện: ưu tiên cột products.hinhAnh; nếu không có thì join bảng ảnh
    $imgSelect = "NULL AS hinhAnh";
    $imgJoin   = "";
    $groupBy   = "";

    if (column_exists($pdo, 'products', 'hinhAnh')) {
        $imgSelect = "p.hinhAnh AS hinhAnh";
    } elseif (table_exists($pdo, 'product_images')) {
        $imgJoin   = " LEFT JOIN product_images pi ON pi.product_id = p.product_id";
        $imgSelect = "MIN(pi.hinhAnh) AS hinhAnh";
        $groupBy   = " GROUP BY p.product_id, p.tenSp, p.giaBan, b.tenHang";
    } elseif (table_exists($pdo, 'product_id')) {
        // Trường hợp bạn đặt tên bảng ảnh là product_id
        $imgJoin   = " LEFT JOIN product_id pi ON pi.product_id = p.product_id";
        $imgSelect = "MIN(pi.hinhAnh) AS hinhAnh";
        $groupBy   = " GROUP BY p.product_id, p.tenSp, p.giaBan, b.tenHang";
    }

    $sql = "SELECT p.product_id, p.tenSp, p.giaBan, $imgSelect, b.tenHang
            FROM products p
            JOIN brand b ON b.brand_id = p.brand_id
            $imgJoin
            WHERE p.tenSp LIKE ?" . $groupBy;
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['%' . $q . '%']);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Quy ước ảnh (nếu cần)
function product_image($id) {
    return '/images/products/' . (int)$id . '.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bi Phone</title>
    <link rel="stylesheet" href="/site/font/fontawesome-free-7.1.0-web/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  <!-- CSS của bạn -->
</head>
<body>
    <!--Header-->
   <nav class="navbar navbar-expand-lg" id="header">
        <div class="container-fluid px-3">
        <a href="home.php"> <img style="width: 70px; border-radius: 50%; margin-left: 25px;" src="images/P.jpg" class="logo navbar-brand d-flex align-items-center gap-2"> S Phone</a>

        <button class="navbar-toggler" type="button"data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul id="navbar" class="navbar-nav ms-auto align-items-lg-center gap-lg-3">

                <li class="nav-item"><a href="home.php">Home</a></li>

                <li class="nav-item"><a  class=" active" href="product.php">Sản phẩm</a></li>

                <li class="nav-item thanhtimkiem">
                    <form action="search.php" method="get">
                        <button type="submit" aria-label="Tìm kiếm" style="background:none;border:0;padding:0;cursor:pointer;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        </button>

                        <input type="text" name="q" placeholder="Bạn tìm gì..." value="<?= htmlspecialchars($q) ?>" required>
                    </form>
                </li>

                <li id="lg-bag" class="nav-item"><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i>Giỏ hàng</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><span class="user-name">👤 <?= htmlspecialchars($_SESSION['hoTen'] ?? '') ?></span></li>
                <?php else: ?>
                    <li class="nav-item"><a class="login" href="login.php">Đăng nhập</a></li>
                <?php endif; ?>

            </ul>
        </div>
    </nav>
   

    <!--Product đã tìm kiếm -->
    <!-- Breadcrumb -->
<nav class="ketqua" style="padding: 15px 80px; font-size: 20px; color: #3d3b3bff;">
    <a href="home.php" style="text-decoration: none; color: #343333ff;">
        <i class="fa-solid fa-house"></i> Trang chủ
    </a>
    <span style="margin: 0 6px;">/</span>
    <span>
        Kết quả tìm kiếm cho: 
        '<strong><?= htmlspecialchars($q) ?></strong>'
    </span>
</nav>

<section id="product1" class="section-p1">
    

    <div class="pro-container">
        <?php if ($q === ''): ?>
            <p style="font-size: 14px; opacity: 0.8;">Vui lòng nhập tên sản phẩm để tìm.</p>

        <?php elseif (empty($products)): ?>
            <p style="font-size: 14px; opacity: 0.8;">Không tìm thấy sản phẩm phù hợp.</p>

        <?php else: ?>
            <?php foreach ($products as $p): ?>
                <div class="pro" onclick="window.location.href='prodetail.php?id=<?= (int)$p['product_id'] ?>';">
                    <img src="/site/<?= htmlspecialchars(ltrim($p['hinhAnh'] ?? '', '/')) ?>" alt="">

                    <div class="des">
                        <span><?= htmlspecialchars($p['tenHang']) ?></span>
                        <h5><?= htmlspecialchars($p['tenSp']) ?></h5>
                        <h4><?= vnd($p['giaBan']) ?></h4>
                        <div class="star">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>

                    <a class="xemct" href="prodetail.php?id=<?= (int)$p['product_id'] ?>">Xem chi tiết</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>


    <!--footer-->
    <footer id="section-p1">
        <div class="col">
            <h4>Thông tin liên hệ</h4>
            <p><strong>Địa chỉ:</strong>VietNam, Vinh Long, Vinh Kim</p>
            <p><strong>Số điện thoại:</strong>0353044315</p>
            <p><strong>Giờ:</strong>09:00 - 18.00. Mon - Sat</p>
            <div class="follow">
                <h4>Liên hệ với BiPhone</h4>
                <div class="icon">
                    <i class="fa-brands fa-x-twitter"></i>
                    <i class="fa-brands fa-telegram"></i>
                    <i class="fa-brands fa-youtube"></i>
                    <i class="fa-brands fa-instagram"></i>
                </div>
            </div>
        </div>
        <div class="col">
            <h4>Liên hệ</h4>
            <a href="#">Về chúng tôi</a>
            <a href="#">Thông tin giao hàng</a>
            <a href="#">Chính sách bảo hành</a>
            <a href="#">Điều khoản điều kiện</a>
        </div>

        <div class="col">
            <h4>Tài khoản</h4>
            <a href="#">Đăng nhập</a>
            <a href="#">Xem giỏ hàng</a>
            <a href="#">Theo dõi đơn hàng</a>
            <a href="#">Chính sách đổi trả</a>
        </div>

        <div class="col pay">
            <h4>Phương thức thanh toán</h4>
            <p>Thanh toán khi nhận hàng</p>
        </div>
    </footer>
    <script src="javascript/script.js"></script>
</body>
</html>