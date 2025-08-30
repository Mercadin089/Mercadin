<?php
// Conexão com o banco de dados
$host = 'localhost';
$db = 'mercadin';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
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
