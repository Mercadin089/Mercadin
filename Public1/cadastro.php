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


// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nomecompleto']);
    $usuario = trim($_POST['nomeusuario']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmarSenha = $_POST['confirmarsenha'];

    // Valida os campos
    if (empty($nome) || empty($usuario) || empty($email) || empty($senha) || empty($confirmarSenha)) {
        die("Preencha todos os campos.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("E-mail inválido.");
    }

    if ($senha !== $confirmarSenha) {
        die("As senhas não coincidem.");
    }

    // Verifica se o nome de usuário ou e-mail já existem
    $stmt = $pdo->prepare("SELECT idusuario FROM usuario WHERE nomeusuario = ? OR email = ?");
    $stmt->execute([$usuario, $email]);

    if ($stmt->fetch()) {
        die("Usuário ou e-mail já cadastrado.");
    }

    // Criptografa a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $tipo = $_POST['tipo'];

$stmt = $pdo->prepare("INSERT INTO usuario (nomecompleto, nomeusuario, email, senha_hash, tipo) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$nome, $usuario, $email, $senhaHash, $tipo]);

session_start();
$_SESSION['usuario'] = $usuario;
$_SESSION['tipo'] = $tipo;
header("Location: index.php");
exit;
}
?>
