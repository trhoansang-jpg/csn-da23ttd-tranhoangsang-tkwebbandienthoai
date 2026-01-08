<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$pdo = null;
$dbFile = __DIR__ . '/db.php';
if (file_exists($dbFile)) $pdo = require $dbFile;

// Lấy user để tự điền thông tin
$nguoiDung = null;
if (!empty($_SESSION['user_id']) && $pdo) {
  $st = $pdo->prepare("SELECT user_id, hoTen, email, sdt, diaChi FROM users WHERE user_id = ?");
  $st->execute([(int)$_SESSION['user_id']]);
  $nguoiDung = $st->fetch(PDO::FETCH_ASSOC);
}

$thongBaoLoi = "";
$thongBaoOk = "";
$daDatHangThanhCong = false;
// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$pdo) {
    $thongBaoLoi = "Chưa cấu hình kết nối DB (thiếu db.php).";
  } else {
    $userId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($userId <= 0) {
      $thongBaoLoi = "Bạn cần đăng nhập để đặt hàng.";
    } else {
      $email = trim($_POST['email'] ?? '');
      $tenNguoiNhan = trim($_POST['ten_nguoi_nhan'] ?? '');
      $sdtNguoiNhan = trim($_POST['sdt_nguoi_nhan'] ?? '');

      $tinh = trim($_POST['tinh'] ?? '');
      $quan = trim($_POST['quan'] ?? '');
      $phuong = trim($_POST['phuong'] ?? '');
      $diaChiChiTiet = trim($_POST['dia_chi_chi_tiet'] ?? '');
      $ghiChu = trim($_POST['ghi_chu'] ?? '');

      $gioHangJson = $_POST['gio_hang_json'] ?? '';
      $gioHang = json_decode($gioHangJson, true);

      if ($tenNguoiNhan === '' || $sdtNguoiNhan === '' || $tinh === '' || $quan === '' || $phuong === '' || $diaChiChiTiet === '') {
        $thongBaoLoi = "Vui lòng nhập đầy đủ thông tin nhận hàng.";
      } else if (!is_array($gioHang) || empty($gioHang)) {
        $thongBaoLoi = "Giỏ hàng trống hoặc dữ liệu giỏ hàng không hợp lệ.";
      } else {
        // Update email nếu user sửa
        if ($email !== '') {
          $up = $pdo->prepare("UPDATE users SET email=? WHERE user_id=?");
          $up->execute([$email, $userId]);
        }

        $diaChiDayDu = $diaChiChiTiet . ", " . $phuong . ", " . $quan . ", " . $tinh;
        if ($ghiChu !== '') $diaChiDayDu .= " | Ghi chú: " . $ghiChu;

        // Tính tổng tiền
        $tongTien = 0;
        foreach ($gioHang as $it) {
          $soLuong = (int)($it['so_luong'] ?? 0);
          $donGia = (float)($it['don_gia'] ?? 0);
          if ($soLuong > 0 && $donGia >= 0) $tongTien += $soLuong * $donGia;
        }

        if ($tongTien <= 0) {
          $thongBaoLoi = "Tổng tiền không hợp lệ.";
        } else {
          try {
            $pdo->beginTransaction();

            $insDon = $pdo->prepare("
              INSERT INTO orders (user_id, tenKH, sdtKH, diaChi, tongTien)
              VALUES (?, ?, ?, ?, ?)
            ");
            $insDon->execute([$userId, $tenNguoiNhan, $sdtNguoiNhan, $diaChiDayDu, $tongTien]);
            $orderId = (int)$pdo->lastInsertId();

            $insCT = $pdo->prepare("
              INSERT INTO order_items (order_id, product_id, soLuong, donGia, thanhTien)
              VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($gioHang as $it) {
              $productId = (int)($it['product_id'] ?? 0);
              $soLuong   = (int)($it['so_luong'] ?? 0);
              $donGia    = (float)($it['don_gia'] ?? 0);
              if ($productId <= 0 || $soLuong <= 0 || $donGia < 0) continue;

              $thanhTien = $soLuong * $donGia;
              $insCT->execute([$orderId, $productId, $soLuong, $donGia, $thanhTien]);
              $upd = $pdo->prepare("
              UPDATE products
              SET soLuongTon = soLuongTon - ?
              WHERE product_id = ? AND soLuongTon >= ?
            ");
            $upd->execute([$soLuong, $productId, $soLuong]);

            if ($upd->rowCount() === 0) {
              throw new Exception("Sản phẩm ID $productId không đủ tồn kho.");
            }

            }

            $pdo->commit();
            $thongBaoOk = "Đặt hàng thành công! Mã đơn: #".$orderId;
            $daDatHangThanhCong = true;
          } catch (Exception $e) {
            $pdo->rollBack();
            $thongBaoLoi = "Lỗi khi đặt hàng: " . $e->getMessage();
          }
        }
      }
    }
  }
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
</head>
<body>
    <!--Header-->
    <section id="header">
        <a href="home.php"> <img style="width: 70px; border-radius: 50%; margin-left: 25px;" src="images/P.jpg" class="logo"> S Phone</a>

        <div>
            <ul id="navbar">
                <li><a href="home.php">Trang chủ</a></li>
                <li><a href="product.php">Sản phẩm</a></li>
                <li class="thanhtimkiem">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type ="text" placeholder="Bạn tìm gì...">
                    
                </li>
                <li id="lg-bag"><a class="active" href="cart.php"><i class="fa-solid fa-cart-shopping"></i>Giỏ hàng</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><span class="user-name">👤 <?= htmlspecialchars($_SESSION['hoTen'] ?? '') ?></span></li>
                <?php else: ?>
                    <li><a class="login" href="login.php">Đăng nhập</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </section>
    <main class="khung-thanh-toan">
    <?php if ($thongBaoLoi): ?>
      <div class="hop-thong-bao loi"><?= htmlspecialchars($thongBaoLoi) ?></div>
    <?php endif; ?>
    <?php if ($thongBaoOk): ?>
      <div class="hop-thong-bao thanh-cong" id="hop-thanh-cong"><?= htmlspecialchars($thongBaoOk) ?></div>
      <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (window.xoaGioHangCheckout) window.xoaGioHangCheckout(); // ✅ xoá giỏ hàng localStorage
    });
  </script>
    <?php endif; ?>

    <div class="luoi-thanh-toan">
      <!-- CỘT TRÁI (to) -->
      <section class="cot-trai">
        <form method="post" id="form-dat-hang">
          <input type="hidden" name="gio_hang_json" id="gio_hang_json">

          <div class="the thong-tin-khach-hang">
            <h3 class="tieu-de-the">THÔNG TIN KHÁCH HÀNG</h3>

            <div class="dong-2cot">
              <div class="o-nhap">
                <label>Họ tên</label>
                <input type="text" value="<?= htmlspecialchars($nguoiDung['hoTen'] ?? ($_SESSION['hoTen'] ?? '')) ?>" disabled>
                
              </div>

              <div class="o-nhap">
                <label>Số điện thoại</label>
                <input type="text" value="<?= htmlspecialchars($nguoiDung['sdt'] ?? '') ?>" disabled>
              </div>
            </div>

            <div class="duong-phan-cach"></div>

            <div class="o-nhap">
              <label>Email </label>
              <input type="email" name="email" value="<?= htmlspecialchars($nguoiDung['email'] ?? ($_SESSION['email'] ?? '')) ?>" placeholder="Nhập email để nhận hoá đơn VAT">
              
            </div>
          </div>

          <div class="khoang-cach"></div>

          <div class="the thong-tin-nhan-hang">
            <h3 class="tieu-de-the">THÔNG TIN NHẬN HÀNG</h3>

            <div class="dong-2cot">
              <div class="o-nhap">
                <label>Tên người nhận</label>
                <input type="text" name="ten_nguoi_nhan" value="<?= htmlspecialchars($nguoiDung['hoTen'] ?? ($_SESSION['hoTen'] ?? '')) ?>" required>
              </div>

              <div class="o-nhap">
                <label>SĐT người nhận</label>
                <input type="text" name="sdt_nguoi_nhan" value="<?= htmlspecialchars($nguoiDung['sdt'] ?? '') ?>" required>
              </div>
            </div>

            <div class="dong-2cot">
              <div class="o-nhap">
                <label>Tỉnh / Thành phố</label>
                <input type="text" name="tinh" placeholder="Vĩnh Long" required>
              </div>

              <div class="o-nhap">
                <label>Quận / Huyện</label>
                <input type="text" name="quan" placeholder="Vinh Kim" required>
              </div>
            </div>

            <div class="dong-2cot">
              <div class="o-nhap">
                <label>Phường / Xã</label>
                <input type="text" name="phuong" placeholder="Ấp Rẫy" required>
              </div>

              <div class="o-nhap">
                <label>Số nhà, tên đường</label>
                <input type="text" name="dia_chi_chi_tiet" placeholder="00" required>
              </div>
            </div>

            <div class="o-nhap">
              <label>Ghi chú đơn hàng</label>
              <textarea name="ghi_chu" placeholder=""></textarea>
            </div>
          </div>
        </form>
      </section>

      <!-- CỘT PHẢI (nhỏ) -->
      <aside class="cot-phai">
        <div class="the tom-tat-don-hang">
          <h3 class="tieu-de-the">ĐƠN HÀNG</h3>
          <p class="chu-mo-ta">Sản phẩm trong giỏ hàng</p>

          <table class="bang-don-hang">
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th class="can-phai">Thành tiền</th>
              </tr>
            </thead>
            <tbody id="noi-dung-don-hang"></tbody>
          </table>

          <div class="duong-phan-cach"></div>

          <div class="dong-tinh-tien">
            <span class="chu-mo-ta">Tạm tính</span>
            <span id="tam-tinh" class="can-phai">0₫</span>
          </div>

          <div class="dong-tinh-tien tong">
            <span>Tổng tiền</span>
            <span id="tong-tien" class="can-phai">0₫</span>
          </div>

          <button class="nut-dat-hang" id="nut-dat-hang" form="form-dat-hang" type="submit">
            Đặt hàng
          </button>

          <p class="chu-mo-ta nho">* Thanh toán khi nhận hàng</p>
        </div>
      </aside>
    </div>
  </main>
  <script src="javascript/cart.js"></script>
</body>