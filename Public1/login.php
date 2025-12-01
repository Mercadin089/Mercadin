<?php
declare(strict_types=1);

$DB_HOST = 'localhost';
$DB_NAME = 'mercadin'; // o nome que você criou no phpMyAdmin
$DB_USER = 'root';
$DB_PASS = ''; // vazioadrão

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
    $usuario = trim($_POST['nomeusuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        die("Preencha todos os campos.");
    }

    // Busca o usuário no banco
    $stmt = $pdo->prepare("SELECT senha_hash, tipo FROM usuario WHERE nomeusuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($senha, $user['senha_hash'])) {
        session_start();
        $_SESSION['usuario'] = $usuario;
        $_SESSION['tipo'] = $user['tipo'];

        // Redireciona para a página inicial
        header("Location: index.php");
        exit;
    } else {
        echo "Usuário ou senha incorretos.";
    }
}
?>
