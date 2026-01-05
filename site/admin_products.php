<?php


require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();


if (!isset($_SESSION['user_id']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
  header('Location: login.php');
  exit;
}

// ====== Hàm tiện ích ======
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ====== Xử lý CRUD ======
$action = $_POST['action'] ?? '';
$thongBao = '';
$loi = '';

try {
    if ($action === 'create') {
        $is_home = isset($_POST['is_home']) ? 1 : 0;

        // 1) Tạo sản phẩm (KHÔNG còn cột hinhAnh trong products)
        $sql = "INSERT INTO products
            (brand_id, tenSp, moTa, giaBan, soLuongTon, CPU, RAM, boNho, Camera, DLPin, HDH, mauSac, khoiLuong, kichThuoc, is_home)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            (int)($_POST['brand_id'] ?? 0),
            trim($_POST['tenSp'] ?? ''),
            trim($_POST['moTa'] ?? ''),
            (float)($_POST['giaBan'] ?? 0),
            (int)($_POST['soLuongTon'] ?? 0),
            trim($_POST['CPU'] ?? ''),
            trim($_POST['RAM'] ?? ''),
            ($_POST['boNho'] === '' ? null : (int)$_POST['boNho']),
            trim($_POST['Camera'] ?? ''),
            trim($_POST['DLPin'] ?? ''),
            trim($_POST['HDH'] ?? ''),
            trim($_POST['mauSac'] ?? ''),
            ($_POST['khoiLuong'] === '' ? null : (float)$_POST['khoiLuong']),
            trim($_POST['kichThuoc'] ?? ''),
            $is_home,
        ]);

        $newId = (int)$pdo->lastInsertId();

        // 2) Lưu ảnh sang bảng product_images (1 dòng / 1 sản phẩm)
        $hinhAnh = trim($_POST['hinhAnh'] ?? '');
        $anhthumbnail = trim($_POST['anhthumbnail'] ?? ''); // gợi ý: nhập dạng: img1.jpg, img2.jpg, img3.jpg

        // Chỉ insert nếu người dùng có nhập (tránh tạo bản ghi rỗng)
        if ($hinhAnh !== '' || $anhthumbnail !== '') {
            $sqlImg = "INSERT INTO product_images (product_id, hinhAnh, anhthumbnail) VALUES (?, ?, ?)";
            $stImg = $pdo->prepare($sqlImg);
            $stImg->execute([$newId, ($hinhAnh === '' ? null : $hinhAnh), ($anhthumbnail === '' ? null : $anhthumbnail)]);
        }

        header('Location: admin_products.php?ok=1');
        exit;
    }

    if ($action === 'update') {
        $id = (int)($_POST['product_id'] ?? 0);
        if ($id <= 0) throw new Exception("Thiếu product_id.");

        $is_home = isset($_POST['is_home']) ? 1 : 0;

        // 1) Cập nhật sản phẩm (KHÔNG còn cột hinhAnh)
        $sql = "UPDATE products SET
                brand_id=?, tenSp=?, moTa=?, giaBan=?,
                soLuongTon=?, CPU=?, RAM=?, boNho=?, Camera=?,
                DLPin=?, HDH=?, mauSac=?, khoiLuong=?, kichThuoc=?, is_home=?
            WHERE product_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            (int)($_POST['brand_id'] ?? 0),
            trim($_POST['tenSp'] ?? ''),
            trim($_POST['moTa'] ?? ''),
            (float)($_POST['giaBan'] ?? 0),
            (int)($_POST['soLuongTon'] ?? 0),
            trim($_POST['CPU'] ?? ''),
            trim($_POST['RAM'] ?? ''),
            ($_POST['boNho'] === '' ? null : (int)$_POST['boNho']),
            trim($_POST['Camera'] ?? ''),
            trim($_POST['DLPin'] ?? ''),
            trim($_POST['HDH'] ?? ''),
            trim($_POST['mauSac'] ?? ''),
            ($_POST['khoiLuong'] === '' ? null : (float)$_POST['khoiLuong']),
            trim($_POST['kichThuoc'] ?? ''),
            $is_home,
            $id
        ]);

        // 2) Upsert ảnh
        $hinhAnh = trim($_POST['hinhAnh'] ?? '');
        $anhthumbnail = trim($_POST['anhthumbnail'] ?? '');

        // Kiểm tra có bản ghi ảnh chưa
        $chk = $pdo->prepare("SELECT img_id FROM product_images WHERE product_id=? ORDER BY img_id ASC LIMIT 1");
        $chk->execute([$id]);
        $row = $chk->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Update
            $up = $pdo->prepare("UPDATE product_images SET hinhAnh=?, anhthumbnail=? WHERE img_id=?");
            $up->execute([
                ($hinhAnh === '' ? null : $hinhAnh),
                ($anhthumbnail === '' ? null : $anhthumbnail),
                (int)$row['img_id']
            ]);
        } else {
            // Insert mới (nếu có nhập gì đó)
            if ($hinhAnh !== '' || $anhthumbnail !== '') {
                $ins = $pdo->prepare("INSERT INTO product_images (product_id, hinhAnh, anhthumbnail) VALUES (?, ?, ?)");
                $ins->execute([$id, ($hinhAnh === '' ? null : $hinhAnh), ($anhthumbnail === '' ? null : $anhthumbnail)]);
            }
        }

        header('Location: admin_products.php?ok=1');
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['product_id'] ?? 0);
        if ($id <= 0) throw new Exception("Thiếu product_id.");

        // Xoá ảnh trước (an toàn cả khi không có FK CASCADE)
        $pdo->prepare("DELETE FROM product_images WHERE product_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM products WHERE product_id=?")->execute([$id]);

        header('Location: admin_products.php?ok=1');
        exit;
    }
} catch (Throwable $ex) {
    $loi = $ex->getMessage();
}

// ====== Dữ liệu hiển thị ======
if (isset($_GET['ok'])) $thongBao = "Thao tác thành công.";

// Danh sách hãng
$brands = $pdo->query("SELECT brand_id, tenHang FROM brand ORDER BY tenHang ASC")->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách sản phẩm + 1 bản ghi ảnh (nếu có)
$sqlList = "
    SELECT
        p.*,
        b.tenHang,
        pi.hinhAnh AS anhDaiDien,
        pi.anhthumbnail
    FROM products p
    JOIN brand b ON b.brand_id = p.brand_id
    LEFT JOIN product_images pi
        ON pi.product_id = p.product_id
        AND pi.img_id = (
            SELECT MIN(pi2.img_id) FROM product_images pi2 WHERE pi2.product_id = p.product_id
        )
    ORDER BY p.product_id DESC
";
$products = $pdo->query($sqlList)->fetchAll(PDO::FETCH_ASSOC);

// Nếu có edit thì lấy dữ liệu để đổ form
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editing = null;

if ($editId > 0) {
    $st = $pdo->prepare("
        SELECT
            p.*,
            pi.hinhAnh AS anhDaiDien,
            pi.anhthumbnail
        FROM products p
        LEFT JOIN product_images pi
            ON pi.product_id = p.product_id
            AND pi.img_id = (
                SELECT MIN(pi2.img_id) FROM product_images pi2 WHERE pi2.product_id = p.product_id
            )
        WHERE p.product_id = ?
        LIMIT 1
    ");
    $st->execute([$editId]);
    $editing = $st->fetch(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
    
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
  
<title>Quản lý sản phẩm</title>
  
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f4f6f9;margin:0;padding:20px;color:#222}
    .wrap{max-width:1100px;margin:auto; background:}
    h1{margin:0 0 14px}
    .msg{padding:10px 12px;border-radius:10px;margin:10px 0}
    .ok{background:#e9fbef;border:1px solid #b7efc5}
    .err{background:#ffecec;border:1px solid #ffb3b3}
    .grid{display:grid;grid-template-columns:380px 1fr;gap:18px;align-items:start}
    .card{background:#fff;border:1px solid #e6e6e6;border-radius:14px;padding:14px;box-shadow:0 10px 26px rgba(0,0,0,.06)}
    .card table th{
      background: rgb(148, 10, 10);
      color: #fff;
    }
    label{display:block;font-size:13px;margin:8px 0 4px}
    input,select,textarea{width:100%;padding:9px 10px;border:1px solid #d7d7d7;border-radius:10px;font-size:14px;box-sizing:border-box}
    textarea{min-height:70px;resize:vertical}
    .row2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .btn{display:inline-block;padding:10px 12px;border-radius:10px;border:1px solid #d7d7d7;background:#fff;cursor:pointer}
    .btn.primary{background:#1e88e5;color:#fff;border-color:#1e88e5}
    .btn.danger{background:#e53935;color:#fff;border-color:#e53935}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee;text-align:left;font-size:14px;vertical-align:top}
    th{background:#fafafa}
    img{max-width:70px;max-height:70px;object-fit:cover;border-radius:10px;border:1px solid #eee}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    .small{font-size:12px;color:#555}
    .check{display:flex;align-items:center;gap:8px;margin-top:10px}
    .check input{width:auto}
  </style>
</head>
<body>
<div class="wrap container-xl px-0">
  <h1>Quản lý sản phẩm</h1>

  <?php if ($thongBao): ?><div class="msg ok"><?= e($thongBao) ?></div><?php endif; ?>
  <?php if ($loi): ?><div class="msg err">Lỗi: <?= e($loi) ?></div><?php endif; ?>

  <div class="row g-3 align-items-start">
    <!-- FORM -->
    <div class="col-12 col-lg-4"><div class="card">
      <h3 style="margin:0 0 10px"><?= $editing ? "Cập nhật sản phẩm #".(int)$editing['product_id'] : "Thêm sản phẩm" ?></h3>

      <form method="post">
        <input type="hidden" name="action" value="<?= $editing ? "update" : "create" ?>">
        <?php if ($editing): ?>
          <input type="hidden" name="product_id" value="<?= (int)$editing['product_id'] ?>">
        <?php endif; ?>

        <label>Hãng</label>
        <select name="brand_id" required>
          <option value="">-- Chọn hãng --</option>
          <?php foreach ($brands as $b): ?>
            <option value="<?= (int)$b['brand_id'] ?>" <?= ($editing && (int)$editing['brand_id']===(int)$b['brand_id']) ? "selected" : "" ?>>
              <?= e($b['tenHang']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Tên sản phẩm</label>
        <input name="tenSp" required value="<?= e($editing['tenSp'] ?? '') ?>">

        <label>Mô tả</label>
        <textarea name="moTa"><?= e($editing['moTa'] ?? '') ?></textarea>

        <div class="row g-2">
          <div class="col-12 col-md-6">
            <label>Giá bán</label>
            <input name="giaBan" type="number" step="0.01" min="0" required value="<?= e($editing['giaBan'] ?? 0) ?>">
          </div>
          <div class="col-12 col-md-6">
            <label>Số lượng tồn</label>
            <input name="soLuongTon" type="number" min="0" required value="<?= e($editing['soLuongTon'] ?? 0) ?>">
          </div>
        </div>

        <label>Ảnh đại diện</label>
        <input name="hinhAnh" value="<?= e($editing['anhDaiDien'] ?? '') ?>" placeholder="">

        <label>Ảnh thumbnail</label>
        <input name="anhthumbnail" value="<?= e($editing['anhthumbnail'] ?? '') ?>" placeholder="">

        <div class="row g-2">
          <div class="col-12 col-md-6"><label>CPU</label><input name="CPU" value="<?= e($editing['CPU'] ?? '') ?>"></div>
          <div class="col-12 col-md-6"><label>RAM</label><input name="RAM" value="<?= e($editing['RAM'] ?? '') ?>"></div>
        </div>

        <div class="row g-2">
          <div class="col-12 col-md-6"><label>Bộ nhớ (GB)</label><input name="boNho" type="number" min="0" value="<?= e($editing['boNho'] ?? '') ?>"></div>
          <div class="col-12 col-md-6"><label>Camera</label><input name="Camera" value="<?= e($editing['Camera'] ?? '') ?>"></div>
        </div>

        <div class="row g-2">
          <div class="col-12 col-md-6"><label>Dung lượng pin</label><input name="DLPin" value="<?= e($editing['DLPin'] ?? '') ?>"></div>
          <div class="col-12 col-md-6"><label>Hệ điều hành</label><input name="HDH" value="<?= e($editing['HDH'] ?? '') ?>"></div>
        </div>

        <div class="row g-2">
          <div class="col-12 col-md-6"><label>Màu sắc</label><input name="mauSac" value="<?= e($editing['mauSac'] ?? '') ?>"></div>
          <div class="col-12 col-md-6"><label>Khối lượng</label><input name="khoiLuong" type="number" step="0.01" min="0" value="<?= e($editing['khoiLuong'] ?? '') ?>"></div>
        </div>

        <label>Kích thước</label>
        <input name="kichThuoc" value="<?= e($editing['kichThuoc'] ?? '') ?>">

        <div class="check">
          <input type="checkbox" name="is_home" <?= (!empty($editing) ? ((int)$editing['is_home']===1 ? "checked" : "") : "") ?>>
          <span>Hiển thị ở trang chủ</span>
        </div>

        <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap">
          <button class="btn primary" type="submit"><?= $editing ? "Cập nhật" : "Thêm mới" ?></button>
          <?php if ($editing): ?>
            <a class="btn" href="admin_products.php">Hủy sửa</a>
          <?php endif; ?>
        </div>
        
      </form>
    </div>
</div>

    <!-- BẢNG DANH SÁCH -->
    <div class="col-12 col-lg-8"><div class="card">
      <h3 style="margin:0 0 10px">Danh sách sản phẩm (<?= count($products) ?>)</h3>
      <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Ảnh</th>
            <th>Tên</th>
            <th>Hãng</th>
            <th>Giá</th>
            <th>Tồn</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?= (int)$p['product_id'] ?></td>
            <td>
              <?php if (!empty($p['anhDaiDien'])): ?>
                <img src="<?= e($p['anhDaiDien']) ?>" alt="">
              <?php else: ?>
                <span class="small">Chưa có ảnh</span>
              <?php endif; ?>
            </td>
            <td>
              <div><b><?= e($p['tenSp']) ?></b></div>
              <div class="small"><?= !empty($p['is_home']) ? "★ Trang chủ" : "" ?></div>
            </td>
            <td><?= e($p['tenHang']) ?></td>
            <td><?= number_format((float)$p['giaBan'], 0, ',', '.') ?> đ</td>
            <td><?= (int)$p['soLuongTon'] ?></td>
            <td>
              <div class="actions">
                <a class="btn" href="admin_products.php?edit=<?= (int)$p['product_id'] ?>">Sửa</a>
                <form method="post" onsubmit="return confirm('Xóa sản phẩm #<?= (int)$p['product_id'] ?>?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                  <button class="btn danger" type="submit">Xóa</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div></div>
  </div>
</div>
</body>
</html>
