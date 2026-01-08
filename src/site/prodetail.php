<?php
session_start();
require_once __DIR__ . '/db.php';

$q = trim($_GET['q'] ?? '');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(404);
    echo 'Thiếu id sản phẩm.';
    exit;
}



function table_exists(PDO $pdo, string $table): bool {
    $st = $pdo->prepare("SHOW TABLES LIKE ?");
    $st->execute([$table]);
    return (bool) $st->fetchColumn();
}
function column_exists(PDO $pdo, string $table, string $column): bool {
    try {
        $st = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $st->execute([$column]);
        return (bool) $st->fetchColumn();
    } catch (Throwable $e) {
        return false;
    }
}
function fetch_main_image(PDO $pdo, int $product_id): ?string {
    // Ưu tiên bảng ảnh tách riêng nếu có
    foreach (['product_images', 'product_id'] as $t) {
        if (table_exists($pdo, $t)) {
            // dự kiến cột: product_id, hinhAnh
            try {
                $st = $pdo->prepare("SELECT hinhAnh FROM `$t` WHERE product_id = ? LIMIT 1");
                $st->execute([$product_id]);
                $img = $st->fetchColumn();
                if ($img) return $img;
            } catch (Throwable $e) {
                // bỏ qua nếu bảng có cấu trúc khác
            }
        }
    }
    return null;
}
function fetch_thumbnails(PDO $pdo, int $product_id): array {
    // Lấy ảnh thumbnail từ DB nếu có (1 ảnh hoặc nhiều ảnh cách nhau bởi dấu phẩy)
    foreach (['product_images', 'product_id'] as $t) {
        if (!table_exists($pdo, $t)) continue;
        try {
            $st = $pdo->prepare("SELECT anhthumbnail FROM `$t` WHERE product_id = ? LIMIT 1");
            $st->execute([$product_id]);
            $thumb = $st->fetchColumn();
            if ($thumb) {
                $arr = array_filter(array_map('trim', explode(',', (string)$thumb)));
                return array_values($arr);
            }
        } catch (Throwable $e) {}
    }
    return [];
}

function main_img(PDO $pdo, array $row): string {
    $img = $row['hinhAnh'] ?? null;
    if (!$img && !empty($row['product_id'])) {
        $img = fetch_main_image($pdo, (int)$row['product_id']);
    }
    return $img ?: 'images/no-image.png';
}

$stmt = $pdo->prepare(
    "SELECT p.*, b.tenHang
     FROM products p
     JOIN brand b ON b.brand_id = p.brand_id
     WHERE p.product_id = ?"
);
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    http_response_code(404);
    echo 'Không tìm thấy sản phẩm.';
    exit;
}
// ===== Lấy các biến thể: cùng tên + cùng hãng (mỗi biến thể là 1 dòng trong products) =====
// Quy ước: trang chi tiết đang đứng trên 1 biến thể cụ thể (theo product_id = $id).
// Khi render:
//  - "Phiên bản" (boNho): lấy tất cả boNho thuộc cùng tenSp + brand_id
//  - "Màu sắc": chỉ lấy các màu thuộc boNho đang chọn (boNho của biến thể hiện tại hoặc từ GET)

$boNhoChon = isset($_GET['boNho']) ? (int)$_GET['boNho'] : (int)($product['boNho'] ?? 0);

// Base select (sẽ bổ sung ảnh nếu DB tách bảng ảnh)
$select = "SELECT p.product_id, p.boNho, p.mauSac, p.giaBan";
$from   = " FROM products p";
$join   = "";

// WHERE: tất cả biến thể của cùng model
$whereAll   = " WHERE p.tenSp = ? AND p.brand_id = ?";
// WHERE: màu theo phiên bản đang chọn
$whereColor = " WHERE p.tenSp = ? AND p.brand_id = ? AND p.boNho = ?";

// Nếu products còn cột hinhAnh thì lấy trực tiếp
if (column_exists($pdo, 'products', 'hinhAnh')) {
  $select .= ", p.hinhAnh";
} else {
  // Nếu đã tách bảng ảnh, join để lấy ảnh đại diện (MIN cho đơn giản)
  if (table_exists($pdo, 'product_images')) {
    $join = " LEFT JOIN product_images pi ON pi.product_id = p.product_id";
    $select .= ", MIN(pi.hinhAnh) AS hinhAnh";
  } elseif (table_exists($pdo, 'product_id')) {
    $join = " LEFT JOIN product_id pi ON pi.product_id = p.product_id";
    $select .= ", MIN(pi.hinhAnh) AS hinhAnh";
  } else {
    $select .= ", NULL AS hinhAnh";
  }
}

$group = "";
if (strpos($select, "MIN(pi.hinhAnh)") !== false) {
  $group = " GROUP BY p.product_id, p.boNho, p.mauSac, p.giaBan";
}

// 1) Lấy tất cả biến thể (để gom danh sách phiên bản boNho)
$sqlAll = $select . $from . $join . $whereAll . $group . " ORDER BY p.boNho ASC, p.mauSac ASC";
$stmtAll = $pdo->prepare($sqlAll);
$stmtAll->execute([$product['tenSp'], $product['brand_id']]);
$allVariants = $stmtAll->fetchAll();
if (!$allVariants) $allVariants = [$product];

// 2) Lấy danh sách màu theo boNho đang chọn
$sqlColors = $select . $from . $join . $whereColor . $group . " ORDER BY p.mauSac ASC";
$stmtC = $pdo->prepare($sqlColors);
$stmtC->execute([$product['tenSp'], $product['brand_id'], $boNhoChon]); // ✅ đủ 3 tham số
$variants = $stmtC->fetchAll();
if (!$variants) $variants = [$product];

// Map nhanh để điều hướng đúng biến thể theo (boNho, mauSac)
$variantMap = []; // $variantMap[boNho][mauSac] = product_id
foreach ($allVariants as $v) {
  $bn = (string)($v['boNho'] ?? '');
  $ms = trim((string)($v['mauSac'] ?? ''));
  if ($bn !== '' && $ms !== '') {
    $variantMap[$bn][$ms] = (int)$v['product_id'];
  }
}
$currentBoNho = (string)($product['boNho'] ?? '');
$currentMau   = trim((string)($product['mauSac'] ?? ''));

// Gom phiên bản (boNho) và màu (mauSac)

$byStorage = [];
$byColor = [];

// Phiên bản: lấy từ tất cả biến thể (allVariants)
foreach ($allVariants as $v) {
  $storageKey = (string)($v['boNho'] ?? '');
  if ($storageKey !== '' && !isset($byStorage[$storageKey])) $byStorage[$storageKey] = $v;
}

// Màu sắc: chỉ lấy theo boNho đang chọn (variants)
foreach ($variants as $v) {
  $colorKey = trim((string)($v['mauSac'] ?? ''));
  if ($colorKey !== '' && !isset($byColor[$colorKey])) $byColor[$colorKey] = $v;
}

// Quy ước ảnh (không đổi SQL):
// Big: /images/products/{product_id}.jpg
// Small: /images/products/{product_id}_1.jpg ... _4.jpg
function img_big($id) { return 'images/products/' . (int)$id . '.jpg'; }
function img_small($id, $i) { return 'images/products/' . (int)$id . '_' . (int)$i . '.jpg'; }

// Gom thông số kỹ thuật từ DB
$specs = [
    'CPU' => $product['CPU'],
    'RAM' => $product['RAM'],
    'Bộ nhớ' => $product['boNho'],
    'Camera' => $product['Camera'],
    'Dung lượng pin' => $product['DLPin'],
    'Hệ điều hành' => $product['HDH'],
    'Màu sắc' => $product['mauSac'],
    'Khối lượng' => $product['khoiLuong'],
    'Kích thước' => $product['kichThuoc'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bi Phone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!--Header-->
    <section id="header">
        <a href="home.php"> <img style="width: 70px; border-radius: 50%; margin-left: 25px;" src="images/P.jpg" class="logo"> S Phone</a>

        <div>
            <ul id="navbar">
                <li><a href="home.php">Trang chủ</a></li>
                <li><a class="active" href="product.php">Sản phẩm</a></li>
                <li class="thanhtimkiem">
                  <form action="search.php" method="get">
                      <button type="submit" aria-label="Tìm kiếm" style="background:none;border:0;padding:0;cursor:pointer;">
                      <i class="fa-solid fa-magnifying-glass"></i>
                      </button>

                      <input type="text" name="q" placeholder="Bạn tìm gì..." value="<?= htmlspecialchars($q) ?>" required>
                  </form>
                </li>
                <li id="lg-bag"><a href="cart.php"><i class="fa-solid fa-cart-shopping"></i>Giỏ hàng</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><span class="user-name">👤 <?= htmlspecialchars($_SESSION['hoTen'] ?? '') ?></span></li>
                <?php else: ?>
                    <li><a class="login" href="login.php">Đăng nhập</a></li>
                <?php endif; ?>

            </ul>
        </div>
    </section>

    <!--Product detals-->
    <section id="prodetails" class="section-p1">
        <div class="single-img-big" >
            <img id="bigimg" src="<?= htmlspecialchars(main_img($pdo, $product)) ?>" class="main-image">



            <div class="small-imgs">
              <?php $thumbs = fetch_thumbnails($pdo, (int)$product['product_id']); ?>

              <?php if (!empty($thumbs)): ?>
                <?php foreach ($thumbs as $t): ?>
                  <div class="small-img-col">
                    <img src="<?= htmlspecialchars($t) ?>" width="100%" class="small-img" onerror="this.style.display='none'">
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <!-- Fallback theo quy ước cũ nếu DB chưa có thumbnail -->
                <?php for ($i=1; $i<=4; $i++): ?>
                  <div class="small-img-col">
                    <img src="<?= htmlspecialchars(img_small($id, $i)) ?>" width="" class="small-img" onerror="this.style.display='none'">
                  </div>
                <?php endfor; ?>
              <?php endif; ?>
            </div>
        </div>
    </div>
        <div class="pro-details">
            <h6 class="chitiet">Home / <?= htmlspecialchars($product['tenHang']) ?></h6>
            <h4><?= htmlspecialchars($product['tenSp']) ?></h4>
            <h2 style="color:red;"><?= vnd($product['giaBan']) ?></h2>

            <!-- NOTE: UI tùy chọn giữ nguyên như file gốc (nếu bạn muốn động hoá phiên bản/màu,
                 cần có bảng riêng trong SQL; hiện SQL chưa có) -->
           <div class="nhom-tuy-chon" data-nhom="phienban">
  <h4 class="tieu-de-tuy-chon" style="font-size: 26px;">Phiên bản</h4>

  <div class="luoi-tuy-chon luoi-2-cot">
    <?php if (!empty($byStorage)): ?>
      <?php foreach ($byStorage as $gb => $v): ?>
        <?php
          $isActive = ((string)$gb === $currentBoNho);
          // ưu tiên giữ màu hiện tại nếu có, nếu không thì dùng biến thể đầu tiên của boNho đó
          $targetId = $variantMap[(string)$gb][$currentMau] ?? (int)$v['product_id'];
        ?>
        <a class="the-tuy-chon <?= $isActive ? 'dang-chon' : '' ?>"
           href="prodetail.php?id=<?= (int)$targetId ?>"
           style="text-decoration:none; display:inline-block;">
          <div class="ten-tuy-chon"><?= htmlspecialchars($gb) ?>GB</div>
        </a>
      <?php endforeach; ?>
    <?php else: ?>
      <button class="the-tuy-chon dang-chon" type="button">
        <div class="ten-tuy-chon">Mặc định</div>
      </button>
    <?php endif; ?>
  </div>
</div>
<div class="nhom-tuy-chon" data-nhom="mausac">
  <h4 class="tieu-de-tuy-chon" style="font-size: 26px;">Màu sắc</h4>

  <div class="luoi-tuy-chon luoi-3-cot">
    <?php foreach ($byColor as $color => $v): ?>
      <?php
        $isActiveColor = (trim((string)$color) === $currentMau);
        $targetIdColor = $variantMap[(string)$boNhoChon][trim((string)$color)] ?? (int)$v['product_id'];
      ?>
      <a class="the-tuy-chon the-tuy-chon--mau <?= $isActiveColor ? 'dang-chon' : '' ?>"
         href="prodetail.php?id=<?= (int)$targetIdColor ?>"
         style="text-decoration:none; display:block;">
        <div class="dong-tuy-chon">
          <img class="anh-tuy-chon"
               src="<?= htmlspecialchars($v['hinhAnh'] ?? main_img($pdo, $v)) ?>"
               onerror="this.style.display='none'">
          <div>
            <div class="ten-tuy-chon"><?= htmlspecialchars($color) ?></div>
            <div class="gia-tuy-chon"><?= vnd($v['giaBan']) ?></div>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>

        
            <button type="button"
                class="normal-cart"
                onclick="themVaoGio(this)"
                data-id="<?= (int)$product['product_id'] ?>"
                data-ten="<?= htmlspecialchars($product['tenSp']) ?>"
                data-hang="<?= htmlspecialchars($product['tenHang']) ?>"
                data-gia="<?= (float)$product['giaBan'] ?>"
                data-anh="<?= htmlspecialchars(main_img($pdo, $product)) ?>"
              >
                <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ hàng
            </button>





            <button type="button"
                class="normal"
                onclick="muaNgay(this)"
                data-id="<?= (int)$product['product_id'] ?>"
                data-ten="<?= htmlspecialchars($product['tenSp']) ?>"
                data-hang="<?= htmlspecialchars($product['tenHang']) ?>"
                data-gia="<?= (float)$product['giaBan'] ?>"
                data-anh="<?= htmlspecialchars(main_img($pdo, $product)) ?>"
                >
                Mua ngay
            </button>

            <h4>Thông số kỹ thuật</h4>

            <table class="spec-table">
            <tbody>
                <?php foreach ($specs as $k => $v): if ($v === null || $v === '') continue; ?>
                <tr>
                    <th><?= htmlspecialchars($k) ?></th>
                    <td><?= htmlspecialchars((string)$v) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            </table>

            <?php if (!empty($product['moTa'])): ?>
            <div class="spec-desc">
                <strong>Mô tả:</strong> <?= nl2br(htmlspecialchars($product['moTa'])) ?>
            </div>
            <?php endif; ?>

        </div>
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

    <script>
        var bigimg = document.getElementById("bigimg");
        var smallimg = document.getElementsByClassName("small-img");
        for (let i = 0; i < smallimg.length; i++) {
            smallimg[i].onclick = function() {
                bigimg.src = smallimg[i].src;
            }
        }
        window.IS_LOGGED_IN = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    </script>
    <script src="javascript/cart.js"></script>
<script src="javascript/script.js"></script>

</body>
</html>