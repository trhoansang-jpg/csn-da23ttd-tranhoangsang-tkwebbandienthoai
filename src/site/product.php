<?php
session_start();
require_once __DIR__ . '/db.php';
$q = trim($_GET['q'] ?? '');
$limit = 12; // mỗi trang 12 sp (bạn đổi 9/12/15 tuỳ ý)
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// lọc theo brand_id: product.php?brand=1
$brand_id = isset($_GET['brand']) ? (int)$_GET['brand'] : 0;

// ===== ĐẾM TỔNG SP theo filter =====
if ($brand_id > 0) {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM (
        SELECT MIN(product_id)
        FROM products
        WHERE brand_id = :brand_id
        GROUP BY tenSp, brand_id
    ) t ");
    $countStmt->execute([':brand_id' => $brand_id]);
    $totalProducts = (int)$countStmt->fetchColumn();
} else {
    $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM (
        SELECT MIN(product_id)
        FROM products
        GROUP BY tenSp, brand_id
    ) t")->fetchColumn();
}

$totalPages = (int)ceil($totalProducts / $limit);
if ($totalPages < 1) $totalPages = 1;
if ($page > $totalPages) $page = $totalPages;

// ===== LẤY SP theo trang + filter =====
if ($brand_id > 0) {
    $sql = "SELECT 
            p.product_id, p.tenSp, p.giaBan,
            pi.hinhAnh AS hinhAnh,
            b.tenHang
        FROM products p
        JOIN brand b ON b.brand_id = p.brand_id

        LEFT JOIN (
            SELECT product_id, MIN(img_id) AS img_id
            FROM product_images
            WHERE hinhAnh IS NOT NULL AND hinhAnh <> ''
            GROUP BY product_id
        ) pick ON pick.product_id = p.product_id
        LEFT JOIN product_images pi ON pi.img_id = pick.img_id

        WHERE p.brand_id = :brand_id
        AND p.product_id = (
      SELECT MIN(p2.product_id)
      FROM products p2
      WHERE p2.tenSp = p.tenSp
        AND p2.brand_id = p.brand_id
  )
        ORDER BY p.product_id DESC
        LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':brand_id', $brand_id, PDO::PARAM_INT);
} else {
    $sql = "SELECT 
            p.product_id, p.tenSp, p.giaBan,
            pi.hinhAnh AS hinhAnh,
            b.tenHang
        FROM products p
        JOIN brand b ON b.brand_id = p.brand_id

        LEFT JOIN (
            SELECT product_id, MIN(img_id) AS img_id
            FROM product_images
            WHERE hinhAnh IS NOT NULL AND hinhAnh <> ''
            GROUP BY product_id
        ) pick ON pick.product_id = p.product_id
        LEFT JOIN product_images pi ON pi.img_id = pick.img_id

        WHERE p.product_id = (
    SELECT MIN(p2.product_id)
    FROM products p2
    WHERE p2.tenSp = p.tenSp
      AND p2.brand_id = p.brand_id
)
        ORDER BY p.product_id DESC
        LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
}

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// giữ các query khác khi bấm trang (brand, search sau này...)
function pageUrl($newPage) {
    $params = $_GET;
    $params['page'] = $newPage;
    return '?' . http_build_query($params);
}
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  
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

                <li class="nav-item"><a href="home.php">Trang chủ</a></li>

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

    <section id="page-product-header">
        <h2>#trangchu</h2>
        <p>Tiết kiệm nhiều hơn với phiếu giảm giá</p>
    </section>

    <!--Button cac hang dien thoai-->
    <section class="brand-section">
        <h3 class="brand-title">Điện thoại</h3>
        <div class="brand" id="brandphanloai">
            <a href="product.php?brand=1" class="brand-nho"><img src="/site/images/brand/apple.png" alt="Apple"></a>
            <a href="product.php?brand=2" class="brand-nho"><img src="/site/images/brand/samsung.avif" alt="Samsung"></a>
            <a href="product.php?brand=3" class="brand-nho"><img src="/site/images/brand/oppo.png" alt="Oppo"></a>
            <a href="product.php?brand=6" class="brand-nho"><img src="/site/images/brand/xiaomi.avif" alt="Xiaomi"></a>
            <a href="product.php?brand=4" class="brand-nho" ><img src="/site/images/brand/realme.png" alt="Realme"></a>
            <a href="product.php?brand=5" class="brand-nho"><img src="/site/images/brand/vivo.png" alt="Vivo"></a>

        </div>
    </section>

    <!--Product-->
    <section id="product1" class="section-p1">
        <div class="pro-container">
            <?php foreach ($products as $p): ?>
            <div class="pro" onclick="window.location.href='prodetail.php?id=<?= (int)$p['product_id'] ?>';">
                <img src="<?= htmlspecialchars($p['hinhAnh'] ?? '') ?>" alt="">
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

                <!-- giữ lại nút giỏ hàng, chuyển data-* theo DB -->
                <a class="xemct" href="#">Xem chi tiết</i></a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="pagination" class="section-p1">
        <?php if ($page > 1): ?>
            <a href="<?= htmlspecialchars(pageUrl($page - 1)) ?>">
                <i class="fa-solid fa-arrow-left-long"></i>
            </a>
        <?php endif; ?>

        <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
        ?>
            <a href="<?= htmlspecialchars(pageUrl($i)) ?>" class="<?= ($i === $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?= htmlspecialchars(pageUrl($page + 1)) ?>">
                <i class="fa-solid fa-arrow-right-long"></i>
            </a>
        <?php endif; ?>
    </section>


    

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

    <script src="javascript/cart.js"></script>
    
</body>
</html>
