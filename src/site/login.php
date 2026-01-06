<?php
// login.php
session_start();

require_once __DIR__ . "/db.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $matKhau = $_POST["matKhau"] ?? "";

    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email không hợp lệ.";
    if ($matKhau === "") $errors[] = "Vui lòng nhập mật khẩu.";

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT user_id, role_id, hoTen, email, matKhau FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $errors[] = "Email hoặc mật khẩu không đúng.";
        } else {
            if (!password_verify($matKhau, $user["matKhau"])) {
                $errors[] = "Email hoặc mật khẩu không đúng.";
            } else {
                // Login OK
                $_SESSION["user_id"] = (int)$user["user_id"];
                $_SESSION["role_id"] = (int)$user["role_id"];
                $_SESSION["hoTen"]   = $user["hoTen"];
                $_SESSION["email"]   = $user["email"];

                // Bạn đổi trang đích tuỳ dự án
                if ((int)$user['role_id'] === 1) {
    header('Location: admin.php'); // trang admin của bạn
} else {
    header('Location: home.php'); // hoặc trang user
}
exit;

            }
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
    <title>Đăng nhập</title>
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
        #wrapper h1{
            font-size: 30px;
            color: #1a0e0eff;
            font-weight: 650;
        }

        /* Nền form */
        .form-custom {
            background-color: #fffcf7ff;
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
                <form method="POST" action="login.php" class="col-md-4 p-5 mt-3 rounded-3 shadow-sm form-custom">
                    <h1 class="text-center text-uppercase h3 mb-3">Đăng nhập</h1>

                    <?php if ($errors): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="matKhau" class="form-label">Mật khẩu</label>
                        <input type="password" name="matKhau" id="matKhau" class="form-control" required>
                    </div>

                    <input type="submit" value="Đăng nhập" class="btn btn-custom w-100">
                    <div class="text-center mt-3">
                        <a href="register.php">Bạn chưa có tài khoản? <strong>Đăng ký ngay</strong></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
