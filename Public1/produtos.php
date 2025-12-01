<?php
session_start();
require __DIR__ . '/db.php';

// Buscar produtos do banco
$stmt = $pdo->query('SELECT id, nome, preco, estoque, imagem, criado_a, prazo_reserva_horas FROM produtos ORDER BY criado_a DESC');
$products = $stmt->fetchAll();

// Processar reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fazer_reserva'])) {
    $id_produto = (int)$_POST['id_produto'];
    $quantidade = (int)$_POST['quantidade'];
    
    // Buscar ID do usuário
    $user_stmt = $pdo->prepare("SELECT idusuario FROM usuario WHERE nomeusuario = ?");
    $user_stmt->execute([$_SESSION['usuario']]);
    $user = $user_stmt->fetch();
    $user_id = $user['idusuario'];
    
    // Buscar informações do produto
    $produto_stmt = $pdo->prepare("SELECT estoque, prazo_reserva_horas FROM produtos WHERE id = ?");
    $produto_stmt->execute([$id_produto]);
    $produto = $produto_stmt->fetch();
    
    if ($produto && $quantidade <= $produto['estoque']) {
        // Calcular datas
        $data_reserva = date('Y-m-d H:i:s');
        $data_limite = date('Y-m-d H:i:s', strtotime("+{$produto['prazo_reserva_horas']} hours"));
        
        // Inserir reserva
        $reserva_stmt = $pdo->prepare("INSERT INTO reservas (id_produto, id_usuario, quantidade, data_reserva, data_limite, status) VALUES (?, ?, ?, ?, ?, 'pendente')");
        $reserva_stmt->execute([$id_produto, $user_id, $quantidade, $data_reserva, $data_limite]);
        
        // Atualizar estoque
        $novo_estoque = $produto['estoque'] - $quantidade;
        $update_stmt = $pdo->prepare("UPDATE produtos SET estoque = ? WHERE id = ?");
        $update_stmt->execute([$novo_estoque, $id_produto]);
        
        $message = "Reserva realizada com sucesso! Você tem até " . date('d/m/Y H:i', strtotime($data_limite)) . " para retirar o produto.";
    } else {
        $error = "Estoque insuficiente para realizar a reserva.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - MERCADIN</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <section>
            <a href="index.php" class="logo"><img src="img/logo.jpeg" alt="logo" width="50px" ></a>
            <nav class="navbar">
                <a href="index.php">Início</a>
                <a href="index.php#sobre">Sobre</a>
                <a href="index.php#prom">Promoções</a>
                <a href="index.php#ender">Endereço</a>
                <a href="produtos.php">Produtos</a>
                <a href="vendedor.php">Vendedor</a>
            </nav>
            <div class="icons">
                <a href="reservas.php" class="cart-link" title="Reservas">
                    <img width="30" height="30" src="https://img.icons8.com/material-outlined/30/ffffff/shopping-cart--v1.png" alt="reservas"/>
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

    <section class="prom" style="margin-top: 10rem;">
        <h2 class="title">Nossos <span>PRODUTOS</span></h2>

        <?php if (isset($message)): ?>
            <div style="color: #27ae60; text-align: center; margin: 1rem 0; font-size: 1.6rem;"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div style="color: #ff4444; text-align: center; margin: 1rem 0; font-size: 1.6rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="box-container">
            <?php foreach ($products as $p): 
                $id = (int)$p['id'];
                $nome = $p['nome'];
                $preco = (float)$p['preco'];
                $estoque = (int)$p['estoque'];
                $img = $p['imagem'] ?: '';
                $prazo_reserva = $p['prazo_reserva_horas'];
                $esgotado = $estoque <= 0;
            ?>
            <div class="box">
                <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($nome); ?>">
                <h3><?php echo htmlspecialchars($nome); ?></h3>
                <div class="price">R$ <?php echo number_format($preco, 2, ',', '.'); ?></div>
                <div class="stock-line" style="margin:6px 0;">
                    Estoque: <span class="stock"><?php echo $estoque; ?></span>
                </div>
                <div class="product-info" style="font-size: 1.2rem; color: #ccc; margin: 8px 0;">
                    Prazo de retirada: <?php echo $prazo_reserva; ?> horas
                </div>
                <?php if (isset($_SESSION['usuario'])): ?>
                <form method="POST" class="actions" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <input type="hidden" name="id_produto" value="<?php echo $id; ?>">
                    <input type="number" name="quantidade" min="1" max="<?php echo $estoque; ?>" value="1" style="width:72px; padding:6px;" <?php echo $esgotado ? 'disabled' : ''; ?>>
                    <button type="submit" name="fazer_reserva" class="btn" <?php echo $esgotado ? 'disabled' : ''; ?>>
                        <?php echo $esgotado ? 'Esgotado' : 'Reservar'; ?>
                    </button>
                </form>
                <?php else: ?>
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="login.html" class="btn">Faça login para reservar</a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
</body>
</html>