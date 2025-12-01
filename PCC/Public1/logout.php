<?php
session_start();

$pdo = new PDO("mysql:host=localhost;dbname=mercadin;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

if (isset($_SESSION['usuario'])) {
    $stmt = $pdo->prepare("UPDATE usuario SET token_login = NULL WHERE nomeusuario = ?");
    $stmt->execute([$_SESSION['usuario']]);
}

session_destroy();
setcookie("login_token", "", time() - 3600, "/");

header("Location: login.html");
exit;
?>