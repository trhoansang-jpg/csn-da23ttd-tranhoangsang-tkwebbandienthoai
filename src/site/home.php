<?php
session_start();
require_once __DIR__ . '/db.php';

$q = trim($_GET['q'] ?? '');

$sql = "SELECT 
            p.product_id, 
            p.tenSp, 
            p.giaBan, 
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
        WHERE p.is_home = 1
        AND p.product_id = (
            SELECT MIN(p2.product_id)
            FROM products p2
            WHERE p2.tenSp = p.tenSp
            AND p2.brand_id = p.brand_id
        )
        ORDER BY p.product_id DESC
        LIMIT 16";

$productsAll = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Chia không trùng: 8 sp đầu = NỔI BẬT, 8 sp sau = MỚI
$productsFeatured = array_slice($productsAll, 0, 8);
$productsNew      = array_slice($productsAll, 8, 8);


// Quy ước ảnh (không đổi SQL):
// /images/products/{product_id}.jpg
function product_image($id) {
    return '/images/products/' . (int)$id . '.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
       
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  
    <title>S Phone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
       
        .pro-container.row { --bs-gutter-x: 1rem; --bs-gutter-y: 1rem; }
        .pro-container.row > .pro { margin: 0; } 
        
        footer .row { --bs-gutter-x: 1rem; --bs-gutter-y: 1rem; }
    </style>
</head>

<body>
    <!--Header-->
    <!--logo-->
    <nav class="navbar navbar-expand-lg" id="header">
        <div class="container-fluid px-3">
        <a href="home.php"> <img style="width: 70px; border-radius: 50%; margin-left: 25px;" src="images/P.jpg" class="logo navbar-brand d-flex align-items-center gap-2"> S Phone</a>

        <button class="navbar-toggler" type="button"data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul id="navbar" class="navbar-nav ms-auto align-items-lg-center gap-lg-3">

                <li class="nav-item"><a class=" active" href="home.php">Home</a></li>

                <li class="nav-item"><a href="product.php">Sản phẩm</a></li>

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

   <section class="banner">
    <div class="slider" id="bannerSlider">

    <div class="slide active" data-link="/sanpham">
      <img src="images/banner/img1.jpg">
    </div>

    <div class="slide" data-link="/iphone-17">
      <img src="images/banner/img5.jpg">
    </div>

    <div class="slide" data-link="/">
      <img src="images/banner/img2.jpg">
    </div>

    <button class="nav prev">‹</button>
    <button class="nav next">›</button>

  </div>
</section>

    <!--Product-->
    <section id="product1" class="section-p1">
        <h2>Sản phẩm nổi bật</h2>
        <p>Các sản phẩm bán chạy trong tháng qua</p>

        <div class="pro-container row g-3">
            <?php foreach ($productsFeatured as $p): ?>
            <div class="pro col-6 col-md-4 col-lg-3" onclick="window.location.href='prodetail.php?id=<?= (int)$p['product_id'] ?>';">
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
                <a class="xemct" href="#">Xem chi tiết</i></a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!--banner nho phia duoi-->
    <section id="banner" class="section-m1">
        <a href="prodetail.php?id=13">
        <button class="normal">Tìm hiểu thêm</button>
            </a>
    </section>

    <!--New product-->
    <section id="product1" class="section-p1">
        <h2>Sản phẩm mới</h2>
        <p>Các sản mẫu điện thoại mới nhất trong năm 2025</p>

        <div class="pro-container row g-3">
            <?php foreach ($productsNew as $p): ?>
            <div class="pro col-6 col-md-4 col-lg-3" onclick="window.location.href='prodetail.php?id=<?= (int)$p['product_id'] ?>';">
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
                <a class="xemct" href="#">Xem chi tiết</i></a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!--banner sale-->
    <section id="banner-sale" class="section-p1">
        <div class="banner-box">
            <h4>FLASHSALE</h4>
            <h2>Giá Sốc</h2>
            <span>Ưu đãi độc quyền</span>
            
        </div>
        <div class="banner-box banner-box2">
            <h4>Ưu đãi sinh viên</h4>
            <h2>Giảm 20%</h2>
            <span>Back to school</span>
            
        </div>

    </section>

    <!--Đăng ký ng dùng mới-->
    <!--
    <section id="newuser" class="section-p1 section-m1">
        <div class="newtest">
            <h4>Đăng ký người dùng mới</h4>
            <p>Nhận thông tin cập nhật qua Email về cửa hàng của tôi và <span>nhận các ưu đãi đặc biệt</span></p>
        </div>
        <div class="form">
            <input type="text" placeholder="Your email address">
            <button class="normal">Đăng ký</button>
        </div>
    </section>
    -->
    <!--footer-->
    <footer id="section-p1">
        <div class="container">
            <div class="row">
<div class="col col-12 col-sm-6 col-lg-3">
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
        <div class="col col-12 col-sm-6 col-lg-3">
            <h4>Liên hệ</h4>
            <a href="#">Về chúng tôi</a>
            <a href="#">Thông tin giao hàng</a>
            <a href="#">Chính sách bảo hành</a>
            <a href="#">Điều khoản điều kiện</a>
        </div>

        <div class="col col-12 col-sm-6 col-lg-3">
            <h4>Tài khoản</h4>
            <a href="#">Đăng nhập</a>
            <a href="#">Xem giỏ hàng</a>
            <a href="#">Theo dõi đơn hàng</a>
            <a href="#">Chính sách đổi trả</a>
        </div>

        <div class="col col-12 col-sm-6 col-lg-3 pay">
            <h4>Phương thức thanh toán</h4>
            <p>Thanh toán khi nhận hàng</p>
        </div>
            </div>
        </div>
    </footer>
    <script src="javascript/script.js"></script>
</body>
</html>
