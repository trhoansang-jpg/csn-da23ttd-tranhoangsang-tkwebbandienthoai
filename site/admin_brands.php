<?php
require_once __DIR__ . '/db.php';
session_start();

// Chỉ admin được vào
if (!isset($_SESSION['user_id']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
  header('Location: login.php');
  exit;
}

$thongBaoOk  = "";
$thongBaoLoi = "";

$action = $_POST['action'] ?? '';

// ===== CREATE =====
if ($action === 'create') {
  $tenHang = trim($_POST['tenHang'] ?? '');
  $xuatXu  = trim($_POST['xuatXu'] ?? '');

  if ($tenHang === '') {
    $thongBaoLoi = "Tên hãng không được để trống.";
  } else {
    try {
      $st = $pdo->prepare("INSERT INTO brand (tenHang, xuatXu) VALUES (?, ?)");
      $st->execute([$tenHang, ($xuatXu !== '' ? $xuatXu : null)]);
      $thongBaoOk = "Đã thêm hãng thành công.";
    } catch (PDOException $e) {
      // tenHang có UNIQUE trong DB -> sẽ rơi vào đây nếu trùng
      $thongBaoLoi = "Không thể thêm hãng. " . $e->getMessage();
    }
  }
}

// ===== UPDATE =====
if ($action === 'update') {
  $brand_id = (int)($_POST['brand_id'] ?? 0);
  $tenHang  = trim($_POST['tenHang'] ?? '');
  $xuatXu   = trim($_POST['xuatXu'] ?? '');

  if ($brand_id <= 0) {
    $thongBaoLoi = "Thiếu brand_id.";
  } elseif ($tenHang === '') {
    $thongBaoLoi = "Tên hãng không được để trống.";
  } else {
    try {
      $st = $pdo->prepare("UPDATE brand SET tenHang = ?, xuatXu = ? WHERE brand_id = ?");
      $st->execute([$tenHang, ($xuatXu !== '' ? $xuatXu : null), $brand_id]);
      $thongBaoOk = "Đã cập nhật hãng thành công.";
    } catch (PDOException $e) {
      $thongBaoLoi = "Không thể cập nhật hãng. " . $e->getMessage();
    }
  }
}

// ===== DELETE =====
if ($action === 'delete') {
  $brand_id = (int)($_POST['brand_id'] ?? 0);

  if ($brand_id <= 0) {
    $thongBaoLoi = "Thiếu brand_id.";
  } else {
    try {
      $st = $pdo->prepare("DELETE FROM brand WHERE brand_id = ?");
      $st->execute([$brand_id]);
      $thongBaoOk = "Đã xóa hãng thành công.";
    } catch (PDOException $e) {
      // nếu brand đang được products dùng FK -> sẽ báo lỗi
      $thongBaoLoi = "Không thể xóa hãng (có thể đang có sản phẩm thuộc hãng này). " . $e->getMessage();
    }
  }
}

// ===== Lấy dữ liệu để EDIT (nếu có ?edit=ID) =====
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$brandEdit = null;

if ($editId > 0) {
  $st = $pdo->prepare("SELECT brand_id, tenHang, xuatXu FROM brand WHERE brand_id = ?");
  $st->execute([$editId]);
  $brandEdit = $st->fetch(PDO::FETCH_ASSOC);
  if (!$brandEdit) {
    $thongBaoLoi = "Không tìm thấy hãng cần sửa.";
  }
}

// ===== Danh sách brands =====
$brands = $pdo->query("SELECT brand_id, tenHang, xuatXu FROM brand ORDER BY brand_id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin - Quản lý danh mục </title>
  <link rel="stylesheet" href="css/styleadmin.css">
</head>
<body>
  <h1>Admin - Quản lý danh mục</h1>

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
    <!-- Cột trái: Form thêm/sửa -->
    <div>
      <?php if ($brandEdit): ?>
        <h2>Sửa hãng</h2>
        <form method="post">
          <input type="hidden" name="action" value="update">
          <input type="hidden" name="brand_id" value="<?= (int)$brandEdit['brand_id'] ?>">

          <div style="margin:10px 0;">
            <label><b>Tên hãng</b></label><br>
            <input type="text" name="tenHang" value="<?= htmlspecialchars($brandEdit['tenHang'] ?? '') ?>" style="width:100%;padding:10px;">
          </div>

          <div style="margin:10px 0;">
            <label><b>Xuất xứ</b></label><br>
            <input type="text" name="xuatXu" value="<?= htmlspecialchars($brandEdit['xuatXu'] ?? '') ?>" style="width:100%;padding:10px;">
          </div>

          <div class="actions" style="margin-top:10px;">
            <button type="submit">Lưu</button>
            <a href="admin_brand.php" style="text-decoration:none;display:inline-block;padding:8px 10px;">Hủy</a>
          </div>
        </form>
      <?php else: ?>
        <h2>Thêm hãng</h2>
        <form method="post">
            <input type="hidden" name="action" value="create">

            <div style="margin:10px 0;">
              <label><b>Tên hãng</b></label><br>
              <input type="text" name="tenHang" placeholder="" style="width:100%;padding:10px;">
            </div>

            <div style="margin:10px 0;">
              <label><b>Xuất xứ</b></label><br>
              <input type="text" name="xuatXu" placeholder="" style="width:100%;padding:10px;">
            </div>

            <div class="actions" style="margin-top:10px;">
              <button type="submit">Thêm</button>
            </div>
        </form>
      <?php endif; ?>

      <hr>
      
    </div>

    <!-- Cột phải: Danh sách -->
    <div>
      <h2>Danh sách hãng</h2>

      <?php if (!$brands): ?>
        <p>Chưa có hãng nào.</p>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Tên hãng</th>
              <th>Xuất xứ</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($brands as $b): ?>
              <tr>
                <td><?= (int)$b['brand_id'] ?></td>
                <td><?= htmlspecialchars($b['tenHang'] ?? '') ?></td>
                <td><?= htmlspecialchars($b['xuatXu'] ?? '') ?></td>
                <td>
                  <div class="actions">
                    <a href="admin_brand.php?edit=<?= (int)$b['brand_id'] ?>" style="text-decoration:none;display:inline-block;padding:6px 10px;">Sửa</a>

                    <form method="post" style="display:inline;" onsubmit="return confirm('Xóa hãng này? Nếu hãng đang có sản phẩm thì sẽ không xóa được.');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="brand_id" value="<?= (int)$b['brand_id'] ?>">
                      <button class="danger" type="submit">Xóa</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

    </div>
  </div>
</body>
</html>
