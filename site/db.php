<?php
// db.php - Kết nối MySQL (phpMyAdmin/XAMPP) bằng PDO
// Chỉ dùng để kết nối + chạy query (tối giản theo yêu cầu).

$DB_HOST = '127.0.0.1';
$DB_NAME = 'web-ban-dt';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP mặc định rỗng

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Lỗi kết nối database. Vui lòng kiểm tra cấu hình trong db.php';
    exit;
}

// Helper format tiền VNĐ (không phụ thuộc locale)
function vnd($amount) {
    return number_format((float)$amount, 0, ',', '.') . ' đ';
}
return $pdo; 
