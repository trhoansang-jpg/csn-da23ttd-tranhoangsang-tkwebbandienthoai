<?php
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('Thiếu ID sản phẩm');
}

// Khởi tạo giỏ
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Nếu đã có thì +1
if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]++;
} else {
    $_SESSION['cart'][$id] = 1;
}

// Quay về trang giỏ
header("Location: cart.php");
exit;
