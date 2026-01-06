<?php
require_once __DIR__ . '/db.php';
session_start();

// Chỉ admin được vào
if (!isset($_SESSION['user_id']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
  header('Location: login.php');
  exit;
}

// Helper format tiền
if (!function_exists('vnd')) {
  function vnd($n) {
    $n = (float)$n;
    return number_format($n, 0, ',', '.') . 'đ';
  }
}

$thongBaoOk = "";
$thongBaoLoi = "";

// ===== Xử lý actions =====
$action = $_POST['action'] ?? '';

if ($action === 'delete') {
  $order_id = (int)($_POST['order_id'] ?? 0);
  if ($order_id > 0) {
    try {
      // order_items có ON DELETE CASCADE, nên xoá orders là tự xoá items
      $st = $pdo->prepare("DELETE FROM orders WHERE order_id = ?");
      $st->execute([$order_id]);
      $thongBaoOk = "Đã xoá đơn hàng #{$order_id}.";
    } catch (Exception $e) {
      $thongBaoLoi = "Không xoá được đơn hàng. Lỗi: " . $e->getMessage();
    }
  }
}

if ($action === 'update_customer') {
  $order_id = (int)($_POST['order_id'] ?? 0);
  $tenKH  = trim($_POST['tenKH'] ?? '');
  $sdtKH  = trim($_POST['sdtKH'] ?? '');
  $diaChi = trim($_POST['diaChi'] ?? '');

  if ($order_id <= 0) {
    $thongBaoLoi = "Thiếu order_id.";
  } elseif ($tenKH === '' || $diaChi === '') {
    $thongBaoLoi = "Tên khách hàng và địa chỉ không được để trống.";
  } else {
    try {
      $st = $pdo->prepare("UPDATE orders SET tenKH = ?, sdtKH = ?, diaChi = ? WHERE order_id = ?");
      $st->execute([$tenKH, ($sdtKH === '' ? null : $sdtKH), $diaChi, $order_id]);
      $thongBaoOk = "Đã cập nhật thông tin giao hàng cho đơn #{$order_id}.";
    } catch (Exception $e) {
      $thongBaoLoi = "Cập nhật thất bại. Lỗi: " . $e->getMessage();
    }
  }
}

// ===== Xem chi tiết đơn (nếu có) =====
$view_id = isset($_GET['view']) ? (int)$_GET['view'] : 0;

$order = null;
$orderItems = [];

if ($view_id > 0) {
  $st = $pdo->prepare("
    SELECT o.*, u.email, u.hoTen AS userName
    FROM orders o
    JOIN users u ON u.user_id = o.user_id
    WHERE o.order_id = ?
  ");
  $st->execute([$view_id]);
  $order = $st->fetch(PDO::FETCH_ASSOC);

  if ($order) {
    $st2 = $pdo->prepare("
      SELECT oi.*, p.tenSp, p.hinhAnh, b.tenHang
      FROM order_items oi
      JOIN products p ON p.product_id = oi.product_id
      JOIN brand b ON b.brand_id = p.brand_id
      WHERE oi.order_id = ?
      ORDER BY oi.item_id DESC
    ");
    $st2->execute([$view_id]);
    $orderItems = $st2->fetchAll(PDO::FETCH_ASSOC);
  } else {
    $thongBaoLoi = "Không tìm thấy đơn hàng #{$view_id}.";
  }
}

// ===== Danh sách đơn hàng =====
$rows = $pdo->query("
  SELECT o.*, u.email, u.hoTen AS userName
  FROM orders o
  JOIN users u ON u.user_id = o.user_id
  ORDER BY o.order_id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin - Quản lý đơn hàng</title>
  <link rel="stylesheet" href="css/styleadmin.css">
</head>
<body>
  <h1>Admin - Quản lý đơn hàng</h1>

  <?php if ($thongBaoLoi): ?>
    <div style="max-width:1200px;margin:12px auto;padding:10px;border:1px solid #ffb3b3;background:#ffecec;">
      <?= htmlspecialchars($thongBaoLoi) ?>
    </div>
  <?php endif; ?>

  <?php if ($thongBaoOk): ?>
    <div style="max-width:1200px;margin:12px auto;padding:10px;border:1px solid #b6f0c0;background:#eaffee;">
      <?= htmlspecialchars($thongBaoOk) ?>
    </div>
  <?php endif; ?>

  <div class="wrap">
    <!-- Cột trái: Chi tiết đơn -->
    <div>
      <h2>Chi tiết đơn hàng</h2>

      <?php if (!$order): ?>
        <p>Chọn đơn hàng để xem chi tiết.</p>
      <?php else: ?>
        <div style="line-height:1.8;">
          <div><b>Mã đơn:</b> #<?= (int)$order['order_id'] ?></div>
          <div><b>Khách đặt (user):</b> <?= htmlspecialchars($order['userName'] ?? '') ?> (<?= htmlspecialchars($order['email'] ?? '') ?>)</div>
          <div><b>Ngày đặt:</b> <?= htmlspecialchars($order['ngayDat'] ?? '') ?></div>
          <div><b>Tổng tiền:</b> <?= vnd($order['tongTien'] ?? 0) ?></div>
        </div>

        <hr>

        <h3>Thông tin giao hàng</h3>
        <form method="post">
          <input type="hidden" name="action" value="update_customer">
          <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">

          <label>Tên khách hàng</label>
          <input type="text" name="tenKH" required value="<?= htmlspecialchars($order['tenKH'] ?? '') ?>">

          <label>Số điện thoại</label>
          <input type="text" name="sdtKH" value="<?= htmlspecialchars($order['sdtKH'] ?? '') ?>">

          <label>Địa chỉ</label>
          <textarea name="diaChi" rows="3" required><?= htmlspecialchars($order['diaChi'] ?? '') ?></textarea>

          <button type="submit">Lưu thông tin</button>
          <a href="admin_order.php" style="margin-left:8px; text-decoration: none;">Bỏ chọn</a>
        </form>

        <hr>

        <h3>Sản phẩm trong đơn</h3>
        <?php if (empty($orderItems)): ?>
          <p>Đơn này chưa có sản phẩm.</p>
        <?php else: ?>
          <table class="table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Sản phẩm</th>
                <th>Hãng</th>
                <th>SL</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orderItems as $it): ?>
                <tr>
                  <td><?= (int)$it['item_id'] ?></td>
                  <td>
                    <?= htmlspecialchars($it['tenSp'] ?? '') ?>
                    <div style="font-size:12px;opacity:.8;">
                      (product_id: <?= (int)$it['product_id'] ?>)
                    </div>
                  </td>
                  <td><?= htmlspecialchars($it['tenHang'] ?? '') ?></td>
                  <td><?= (int)$it['soLuong'] ?></td>
                  <td><?= vnd($it['donGia'] ?? 0) ?></td>
                  <td><?= vnd($it['thanhTien'] ?? 0) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <hr>

        <form method="post" onsubmit="return confirm('Xoá đơn #<?= (int)$order['order_id'] ?>? (Sẽ xoá cả chi tiết sản phẩm)');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="order_id" value="<?= (int)$order['order_id'] ?>">
          <button class="danger" type="submit">Xoá đơn hàng</button>
        </form>
      <?php endif; ?>
    </div>

    <!-- Cột phải: Danh sách đơn -->
    <div>
      <h2>Danh sách đơn hàng (<?= count($rows) ?>)</h2>

      <table class="table">
        <thead>
          <tr>
            <th>Mã</th>
            <th>Người đặt</th>
            <th>Khách nhận</th>
            <th>Ngày</th>
            <th>Tổng</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td>#<?= (int)$r['order_id'] ?></td>
              <td>
                <?= htmlspecialchars($r['userName'] ?? '') ?>
                <div style="font-size:12px;opacity:.8;"><?= htmlspecialchars($r['email'] ?? '') ?></div>
              </td>
              <td>
                <?= htmlspecialchars($r['tenKH'] ?? '') ?>
                <div style="font-size:12px;opacity:.8;"><?= htmlspecialchars($r['sdtKH'] ?? '') ?></div>
              </td>
              <td><?= htmlspecialchars($r['ngayDat'] ?? '') ?></td>
              <td><?= vnd($r['tongTien'] ?? 0) ?></td>
              <td>
                <div class="actions">
                  <a href="admin_order.php?view=<?= (int)$r['order_id'] ?>">Xem</a>

                  <form method="post" onsubmit="return confirm('Xoá đơn #<?= (int)$r['order_id'] ?>?');" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="order_id" value="<?= (int)$r['order_id'] ?>">
                    <button class="danger" type="submit">Xóa</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    </div>
  </div>
</body>
</html>
