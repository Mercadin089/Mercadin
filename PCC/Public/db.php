<?php
declare(strict_types=1);

$DB_HOST = 'localhost';
$DB_NAME = 'mercadin'; // o nome que você criou no phpMyAdmin
$DB_USER = 'root';
$DB_PASS = ''; // vazio no XAMPP por padrão

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Erro de conexão com o banco: ' . $e->getMessage();
    exit;
}
