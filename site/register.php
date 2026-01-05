<?php
// register.php
session_start();
require_once __DIR__ . "/db.php";

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $hoTen   = trim($_POST["hoTen"] ?? "");
    $email   = trim($_POST["email"] ?? "");
    $sdt     = trim($_POST["sdt"] ?? "");
    $matKhau = $_POST["matKhau"] ?? "";

    // (tùy chọn) nếu bạn muốn lưu thêm
    $diaChi  = trim($_POST["diaChi"] ?? "");
    $ngaySinh = trim($_POST["ngaySinh"] ?? ""); // yyyy-mm-dd hoặc rỗng

    // Mặc định role khách hàng (tuỳ hệ thống của bạn)
    $role_id = 2;

    if ($hoTen === "") $errors[] = "Vui lòng nhập họ tên.";
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email không hợp lệ.";
    if ($matKhau === "" || strlen($matKhau) < 6) $errors[] = "Mật khẩu tối thiểu 6 ký tự.";
    if ($sdt !== "" && strlen($sdt) > 15) $errors[] = "Số điện thoại quá dài (tối đa 15 ký tự).";

    if (!$errors) {
        // Email đã tồn tại?
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email đã được sử dụng. Vui lòng dùng email khác.";
        } else {
            $hash = password_hash($matKhau, PASSWORD_DEFAULT);

            // Nếu bạn không dùng diaChi/ngaySinh ở form thì vẫn insert được (để NULL)
            $stmt = $pdo->prepare("
                INSERT INTO users (role_id, hoTen, email, matKhau, sdt, diaChi, ngaySinh)
                VALUES (:role_id, :hoTen, :email, :matKhau, :sdt, :diaChi, :ngaySinh)
            ");

            $stmt->execute([
                ":role_id" => $role_id,
                ":hoTen" => $hoTen,
                ":email" => $email,
                ":matKhau" => $hash,
                ":sdt" => ($sdt === "" ? null : $sdt),
                ":diaChi" => ($diaChi === "" ? null : $diaChi),
                ":ngaySinh" => ($ngaySinh === "" ? null : $ngaySinh),
            ]);

            $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <title>Đăng Ký Tài Khoản</title>
    <style>
        body{
            background-color: #eceff4;

        }
         #wrapper {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        }
        /* Nền form */
        .form-custom {
            background-color: #fffcf7ff;
        }
        #wrapper h1{
            font-size: 30px;
            color: #281010ff;
            font-weight: 650;
        }
        /* Nút đăng nhập / đăng ký */
        .btn-custom {
            background-color: #ab000eff;
            color: #fff;
            border: none;
        }

        .btn-custom:hover {
            background-color: #e00b1dff;
            color: #fff;
        }
        .text-center a{
            text-decoration: none;
            color: black;
        }
        .text-center strong{
            color: red;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <div class="container">
            <div class="row justify-content-around">
                <form method="POST" action="register.php" class="col-md-4 p-4 mt-3 rounded-3 shadow-sm form-custom">

                    <h1 class="text-center text-uppercase h3 mb-3">Đăng ký tài khoản</h1>

                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                            <div class="mt-2">
                                <a class="btn btn-sm btn-success" href="login.php">Đi tới đăng nhập</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="hoTen" class="form-label">Họ và Tên</label>
                        <input type="text" name="hoTen" id="hoTen" class="form-control" required
                               value="<?= htmlspecialchars($_POST['hoTen'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="sdt" class="form-label">Số điện thoại</label>
                        <input type="text" name="sdt" id="sdt" class="form-control"
                               value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="matKhau" class="form-label">Mật khẩu</label>
                        <input type="password" name="matKhau" id="matKhau" class="form-control" required>
                        <div class="form-text">Tối thiểu 6 ký tự.</div>
                    </div>

                    <!-- Nếu bạn muốn lưu thêm diaChi/ngaySinh thì mở 2 input này -->
                    <!--
                    <div class="mb-3">
                        <label for="ngaySinh" class="form-label">Ngày sinh</label>
                        <input type="date" name="ngaySinh" id="ngaySinh" class="form-control"
                               value="<?= htmlspecialchars($_POST['ngaySinh'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="diaChi" class="form-label">Địa chỉ</label>
                        <textarea name="diaChi" id="diaChi" class="form-control" rows="2"><?= htmlspecialchars($_POST['diaChi'] ?? '') ?></textarea>
                    </div>
                    -->

                    <input type="submit" value="Đăng ký" class="btn btn-custom w-100">
                    <div class="text-center mt-3">
                        <a href="login.php">Bạn đã có tài khoản <strong>Đăng nhập  ngay</strong></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
