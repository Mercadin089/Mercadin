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

$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - MERCADIN</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
</head>
<body>
    $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
?>

<header class="header">
    <section>
        <a href="index.php" class="logo">
            <img src="img/logo.jpeg" alt="logo" height="50px" width="50px">
        </a>

        <nav class="navbar">
            <a href="index.php">Início</a>
            <a href="index.php#sobre">Sobre</a>
            <a href="index.php#prom">Promoções</a>
            <a href="index.php#ender">Endereço</a>
            <a href="produtos.php">Produtos</a>
        </nav>

        <div class="icons">

            <a href="carrinho.php" class="cart-link" title="Carrinho">
                <img width="30" height="30" src="https://img.icons8.com/material-outlined/30/ffffff/shopping-cart--v1.png" alt="shopping-cart--v1"/>
                <?php if ((int)$cartCount > 0): ?>
                    <span id="cart-count" aria-label="Itens no carrinho"><?php echo (int)$cartCount; ?></span>
                <?php endif; ?>
            </a>

            <a href="<?php echo isset($_SESSION['usuario']) ? 'usuario.php' : 'login.html'; ?>" class="user-link" title="Minha conta">
                <img width="30" height="30" src="https://img.icons8.com/material-sharp/30/ffffff/user-male-circle.png" alt="user-male-circle">
                <?php if (isset($_SESSION['usuario'])): ?>
                    <span class="username"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                <?php endif; ?>
            </a>
        </div>
    </section>
</header>

    <section class="sobre" style="margin-top: 12rem;">
        <h2 class="title">Minha <span>CONTA</span></h2>
        <div class="row">
            <div class="content" style="width: 100%;">
                <h3 style="color: var(--main-color); margin-bottom: 2rem;">Informações do Usuário</h3>
                <div style="background: rgba(255,255,255,0.05); padding: 2rem; border-radius: 1rem; border: var(--border);">
                    <p style="font-size: 1.8rem; margin-bottom: 1rem; color: #fff;">
                        <strong style="color: var(--main-color);">Nome completo:</strong> 
                        <?php echo htmlspecialchars($dados['nomecompleto']); ?>
                    </p>
                    <p style="font-size: 1.8rem; margin-bottom: 1rem; color: #fff;">
                        <strong style="color: var(--main-color);">Nome de usuário:</strong> 
                        <?php echo htmlspecialchars($dados['nomeusuario']); ?>
                    </p>
                    <p style="font-size: 1.8rem; margin-bottom: 1rem; color: #fff;">
                        <strong style="color: var(--main-color);">Email:</strong> 
                        <?php echo htmlspecialchars($dados['email']); ?>
                    </p>
                    <p style="font-size: 1.8rem; margin-bottom: 2rem; color: #fff;">
                        <strong style="color: var(--main-color);">Tipo de conta:</strong> 
                        <?php echo $dados['tipo'] === 'vendedor' ? 'vendedor' : 'Cliente'; ?>
                    </p>
                    <a href="logout.php" class="btn" style="background: #ff4444;">Sair da Conta</a>
                    <?php if ($dados['tipo'] === 'vendedor'): ?>
                        <a href="vendedor.php" class="btn" style="background: #27ae60; margin-left: 1rem;">Painel do Vendedor</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="footer">
        <div class="share">
            <a href="https://www.instagram.com/eteciguape/" target="_blank">
                <img width="30" height="30" src="https://img.icons8.com/ios/30/ffffff/instagram-new--v1.png" alt="instagram-new--v1"/>
            </a>
            <a href="https://www.facebook.com/eteciguape/?locale=pt_BR" target="_blank">
                <img width="30" height="30" src="https://img.icons8.com/ios/30/ffffff/facebook--v1.png" alt="facebook--v1"/>
            </a>
            <a href="https://www.youtube.com/channel/UC5DBOb6OWFPNaArYx8xxQiA/videos" target="_blank">
                <img width="30" height="30" src="https://img.icons8.com/ios/30/ffffff/youtube-play--v1.png" alt="youtube-play--v1"/>
            </a>
        </div>
    </section>
</body>
</html>