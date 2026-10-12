<?php

/**
 * Script để insert custom_cup_icons vào database online.
 *
 * Cách dùng:
 * 1. Mở terminal tại thư mục project
 * 2. Chạy: php database/seeders/import_icons.php
 * 3. Nhập thông tin database khi được yêu cầu
 */

// Thông tin kết nối (sửa trực tiếp nếu muốn)
$config = [
    'host' => readline('Host (vd: localhost hoặc mysql.myhosting.com): '),
    'port' => readline('Port (vd: 3306): ') ?: '3306',
    'database' => readline('Database name: '),
    'username' => readline('Username: '),
    'password' => readline('Password: '),
];

echo "\nĐang kết nối...\n";

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Kết nối thành công\n\n";
} catch (PDOException $e) {
    exit('✗ Kết nối thất bại: '.$e->getMessage()."\n");
}

// Icons: id => [decimal_code, glyph_for_display]
$icons = [
    1 => [128525, '💗'],
    2 => [11088,  '⭐'],
    3 => [128140, '🌸'],
    4 => [128569, '😻'],
    5 => [128054, '🐶'],
    6 => [128293, '🔥'],
    7 => [127757, '🌈'],
    8 => [127874, '🎂'],
    9 => [9749,   '☕'],
    10 => [128640, '🚀'],
    11 => [128526, '😎'],
    12 => [127807, '🍀'],
];

// Bước 1: Xóa dữ liệu cũ
echo "Xóa dữ liệu cũ trong custom_cup_icons...\n";
$pdo->exec('TRUNCATE TABLE custom_cup_icons');
echo "✓ Đã xóa\n";

// Bước 2: Insert từng icon
$stmt = $pdo->prepare('
    INSERT INTO custom_cup_icons (id, glyph, sort_order, created_at, updated_at)
    VALUES (:id, CHAR(:code USING utf8mb4), :sort, NOW(), NOW())
');

echo "\nĐang insert icons...\n";
foreach ($icons as $id => [$code, $glyph]) {
    $stmt->execute(['id' => $id, 'code' => $code, 'sort' => $id - 1]);
    echo "  ✓ [{$id}] {$glyph} (U+".dechex($code).")\n";
}

// Bước 3: Verify
echo "\n=== Kết quả ===\n";
$result = $pdo->query('SELECT id, glyph, sort_order FROM custom_cup_icons ORDER BY id');
echo 'Tổng cộng: '.$result->rowCount()." dòng\n";
echo "\n✓ Hoàn tất!\n";
