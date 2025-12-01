<?php
session_start();
require __DIR__ . '/db.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit;
}

// Buscar ID do usuário
$user_stmt = $pdo->prepare("SELECT idusuario FROM usuario WHERE nomeusuario = ?");
$user_stmt->execute([$_SESSION['usuario']]);
$user = $user_stmt->fetch();
$user_id = $user['idusuario'];

// Processar cancelamento de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_reserva'])) {
    $reserva_id = (int)$_POST['reserva_id'];
    
    $stmt = $pdo->prepare("UPDATE reservas SET status = 'cancelada' WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$reserva_id, $user_id]);
    $message = "Reserva cancelada com sucesso!";
}

// Buscar reservas do usuário
$stmt = $pdo->prepare("SELECT r.*, p.nome as produto_nome, p.preco, p.imagem 
                       FROM reservas r 
                       JOIN produtos p ON r.id_produto = p.id 
                       WHERE r.id_usuario = ? 
                       ORDER BY r.data_reserva DESC");
$stmt->execute([$user_id]);
$reservas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas - MERCADIN</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .reservas-container { margin-top: 10rem; padding: 2rem; }
        .reservas-table { width: 100%; border-collapse: collapse; color: #fff; margin-top: 2rem; }
        .reservas-table th, .reservas-table td { padding: 1rem; border-bottom: 0.1rem solid rgba(255,255,255,0.3); text-align: left; }
        .reservas-table th { background: rgba(211, 173, 127, 0.2); }
        .status-pendente { color: #f39c12; }
        .status-confirmada { color: #27ae60; }
        .status-cancelada { color: #e74c3c; }
        .status-expirada { color: #95a5a6; }
        .btn-cancelar { background: #ff4444; padding: 0.5rem 1rem; font-size: 1.4rem; }
    </style>
</head>
<body>
    <header class="header">
        <section>
            <a href="index.php" class="logo"><img src="" alt="logo"></a>
            <nav class="navbar">
                <a href="index.php">Início</a>
                <a href="index.php#sobre">Sobre</a>
                <a href="index.php#prom">Promoções</a>
                <a href="index.php#ender">Endereço</a>
                <a href="produtos.php">Produtos</a>
                <a href="vendedor.php">Vendedor</a>
            </nav>
            <div class="icons">
                <a href="reservas.php" class="cart-link" style="position:relative; display:inline-flex; align-items:center; gap:6px;" title="Reservas">
                    <img width="30" height="30" src="https://img.icons8.com/material-outlined/30/ffffff/shopping-cart--v1.png" alt="reservas"/>
                </a>
                <a href="usuario.php" class="user-link" title="Minha conta">
                    <img width="30" height="30" src="https://img.icons8.com/material-sharp/30/ffffff/user-male-circle.png" alt="user-male-circle">
                    <span class="username"><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                </a>
            </div>
        </section>
    </header>

    <section class="reservas-container">
        <h2 class="title">Minhas <span>RESERVAS</span></h2>

        <?php if (isset($message)): ?>
            <div style="color: #27ae60; text-align: center; margin: 1rem 0; font-size: 1.6rem;"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (empty($reservas)): ?>
            <div style="text-align: center; color: #fff; font-size: 2rem;">
                <p>Você não possui reservas</p>
                <a href="produtos.php" class="btn" style="margin-top: 2rem;">Fazer Reserva</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="reservas-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço Unitário</th>
                            <th>Total</th>
                            <th>Data Reserva</th>
                            <th>Data Limite</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="<?php echo htmlspecialchars($reserva['imagem']); ?>" alt="<?php echo htmlspecialchars($reserva['produto_nome']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <div>
                                        <h3 style="color: var(--main-color); margin: 0;"><?php echo htmlspecialchars($reserva['produto_nome']); ?></h3>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $reserva['quantidade']; ?></td>
                            <td>R$ <?php echo number_format($reserva['preco'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($reserva['preco'] * $reserva['quantidade'], 2, ',', '.'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($reserva['data_reserva'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($reserva['data_limite'])); ?></td>
                            <td>
                                <span class="status-<?php echo $reserva['status']; ?>">
                                    <?php echo ucfirst($reserva['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($reserva['status'] == 'pendente'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                        <button type="submit" name="cancelar_reserva" class="btn btn-cancelar" onclick="return confirm('Cancelar esta reserva?')">Cancelar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</body>
</html>