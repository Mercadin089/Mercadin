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
            header("Location: login.html");}
?>
