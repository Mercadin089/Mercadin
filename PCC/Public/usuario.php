<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit;
}

// Conexão com o banco
$pdo = new PDO("mysql:host=localhost;dbname=mercadin;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Buscar dados do usuário
$stmt = $pdo->prepare("SELECT nomecompleto, nomeusuario, email, tipo FROM usuario WHERE nomeusuario = ?");
$stmt->execute([$_SESSION['usuario']]);
$dados = $stmt->fetch();

if (!$dados) {
    echo "Usuário não encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MERCADIN</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap"
        rel="stylesheet">
</head>
<body>
    <header class="header">
        <section>
            <a href="#" class="logo">
                <img src="" alt="logo">
            </a>

            <nav class="navbar">
                <a href="index.php">Início</a>
                <a href="#sobre">Sobre</a>
                <a href="#prom">Promoções</a>
                <a href="#ender">Endereço</a>
                <a href="produtos.html">Produtos</a>
            </nav>

            <div class="icons">
                <a href="usuario.php">
                    <img width="30" height="30" src="https://img.icons8.com/windows/30/ffffff/search--v1.png" alt="search--v1" />
                    <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                </a>
                <img width="30" height="30" src="https://img.icons8.com/material-outlined/30/ffffff/shopping-cart--v1.png" alt="shopping-cart--v1" />
                <a href="login.html"><img width="30" height="30" src="https://img.icons8.com/material-sharp/30/ffffff/user-male-circle.png" alt="user-male-circle"></a>
            </div>
        </section>
    </header>

    <section class="sobre" style="margin-top: 10rem;">
        <h2 class="title">Minha <span>CONTA</span></h2>
        <div class="row">
            <div class="content">
                <h3>Informações do Usuário</h3>
                <p>Nome completo: <strong><?php echo htmlspecialchars($dados['nomecompleto']); ?></strong></p>
                <p>Nome de usuário: <strong><?php echo htmlspecialchars($dados['nomeusuario']); ?></strong></p>
                <p>Email: <strong><?php echo htmlspecialchars($dados['email']); ?></strong></p>
                <p>Tipo de conta: <strong><?php echo $dados['tipo'] === 'admin' ? 'Administrador' : 'Cliente'; ?></strong></p>
                <a href="logout.php" class="btn">Sair da Conta</a>
            </div>
        </div>
    </section>

    <section class="footer">
        <div class="share">
            <a href="https://www.instagram.com/eteciguape/" target="_blank">
                <img width="30" height="30" src="https://img.icons8.com/ios/30/ffffff/instagram-new--v1.png" alt="instagram-new--v1" />
            </a>
            <a href="https://www.facebook.com/eteciguape/?locale=pt_BR" target="_blank">
                <img width="30" height="30" src="https://img.icons8.com/ios/30/ffffff/facebook--v1.png" alt="facebook--v1" />
            </a>
            <a href="https://www.youtube.com/channel/UC5DBOb6OWFPNaArYx8xxQiA/videos" target="_blank">
                <img width="30" height="30" src="https://img.icons8.com/ios/30/ffffff/youtube-play--v1.png" alt="youtube-play--v1" />
            </a>
        </div>
    </section>
</body>
</html>
