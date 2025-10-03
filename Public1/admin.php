<?php
session_start();

// Verificar se o usuário é admin
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: login.html");
    exit;
}

require __DIR__ . '/db.php';

// Processar ações do admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_product':
                $nome = trim($_POST['nome']);
                $preco = (float)$_POST['preco'];
                $estoque = (int)$_POST['estoque'];
                $imagem = trim($_POST['imagem']);
                
                $stmt = $pdo->prepare('INSERT INTO produtos (nome, preco, estoque, imagem, criado_a) VALUES (?, ?, ?, ?, NOW())');
                $stmt->execute([$nome, $preco, $estoque, $imagem]);
                $message = "Produto adicionado com sucesso!";
                break;
                
            case 'update_product':
                $id = (int)$_POST['id'];
                $nome = trim($_POST['nome']);
                $preco = (float)$_POST['preco'];
                $estoque = (int)$_POST['estoque'];
                $imagem = trim($_POST['imagem']);
                
                $stmt = $pdo->prepare('UPDATE produtos SET nome = ?, preco = ?, estoque = ?, imagem = ? WHERE id = ?');
                $stmt->execute([$nome, $preco, $estoque, $imagem, $id]);
                $message = "Produto atualizado com sucesso!";
                break;
                
            case 'delete_product':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = ?');
                $stmt->execute([$id]);
                $message = "Produto excluído com sucesso!";
                break;
        }
    } catch (Exception $e) {
        $error = "Erro: " . $e->getMessage();
    }
}

// Buscar produtos
$stmt = $pdo->query('SELECT * FROM produtos ORDER BY criado_a DESC');
$products = $stmt->fetchAll();

$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - MERCADIN</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <style>
        .admin-container {
            margin-top: 10rem;
            padding: 2rem;
        }
        .admin-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .form-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .form-content {
            background: var(--bg);
            padding: 2rem;
            border-radius: 10px;
            border: var(--border);
            width: 90%;
            max-width: 500px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            color: #fff;
            margin-bottom: 0.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: var(--border);
            border-radius: 5px;
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
            margin-top: 2rem;
        }
        .product-table th,
        .product-table td {
            padding: 1rem;
            border: var(--border);
            text-align: left;
        }
        .product-table th {
            background: rgba(211, 173, 127, 0.2);
        }
        .btn-danger {
            background: #ff4444;
        }
        .btn-danger:hover {
            background: #cc0000;
        }
    </style>
</head>
<body>
    <header class="header">
        <section>
            <a href="index.php" class="logo">
                <img src="" alt="logo">
            </a>
            <nav class="navbar">
                <a href="index.php">Início</a>
                <a href="index.php#sobre">Sobre</a>
                <a href="index.php#prom">Promoções</a>
                <a href="index.php#ender">Endereço</a>
                <a href="produtos.php">Produtos</a>
                <a href="admin.php">Admin</a>
            </nav>
            <div class="icons">
                <a href="carrinho.php" class="cart-link" style="position:relative; display:inline-flex; align-items:center; gap:6px;" title="Carrinho">
                    <img width="30" height="30" src="https://img.icons8.com/material-outlined/30/ffffff/shopping-cart--v1.png" alt="shopping-cart--v1"/>
                    <?php if ((int)$cartCount > 0): ?>
                        <span id="cart-count" aria-label="Itens no carrinho"><?php echo (int)$cartCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="usuario.php" class="user-link" title="Minha conta">
                    <img width="30" height="30" src="https://img.icons8.com/material-sharp/30/ffffff/user-male-circle.png" alt="user-male-circle">
                    <span class="username"><?php echo htmlspecialchars($_SESSION['usuario']); ?> (Admin)</span>
                </a>
            </div>
        </section>
    </header>

    <div class="admin-container">
        <h2 class="title">Painel <span>Administrativo</span></h2>

        <?php if (isset($message)): ?>
            <div style="color: #27ae60; text-align: center; margin: 1rem 0; font-size: 1.6rem;"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div style="color: #ff4444; text-align: center; margin: 1rem 0; font-size: 1.6rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="admin-actions">
            <button class="btn" onclick="openModal('add')">Adicionar Produto</button>
        </div>

        <table class="product-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagem</th>
                    <th>Nome</th>
                    <th>Preço</th>
                    <th>Estoque</th>
                    <th>Data de Criação</th>
                    <th>Última Atualização</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td>
                        <img src="<?php echo htmlspecialchars($product['imagem']); ?>" alt="<?php echo htmlspecialchars($product['nome']); ?>" style="width: 50px; height: 50px; object-fit: cover;">
                    </td>
                    <td><?php echo htmlspecialchars($product['nome']); ?></td>
                    <td>R$ <?php echo number_format($product['preco'], 2, ',', '.'); ?></td>
                    <td><?php echo $product['estoque']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($product['criado_a'])); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($product['enviado_a'])); ?></td>
                    <td>
                        <button class="btn" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">Editar</button>
                        <button class="btn btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">Excluir</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para adicionar/editar produto -->
    <div id="productModal" class="form-modal">
        <div class="form-content">
            <h3 id="modalTitle" style="color: var(--main-color); margin-bottom: 1rem;">Adicionar Produto</h3>
            <form id="productForm" method="POST">
                <input type="hidden" id="productId" name="id">
                <input type="hidden" name="action" id="formAction" value="add_product">
                
                <div class="form-group">
                    <label for="nome">Nome do Produto:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label for="preco">Preço (R$):</label>
                    <input type="number" id="preco" name="preco" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="estoque">Estoque:</label>
                    <input type="number" id="estoque" name="estoque" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="imagem">URL da Imagem:</label>
                    <input type="url" id="imagem" name="imagem" required>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn">Salvar</button>
                </div>
            </form>
        </div>
    </div>

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

    <script>
        function openModal(action) {
            const modal = document.getElementById('productModal');
            const title = document.getElementById('modalTitle');
            const form = document.getElementById('productForm');
            const actionInput = document.getElementById('formAction');
            
            if (action === 'add') {
                title.textContent = 'Adicionar Produto';
                actionInput.value = 'add_product';
                form.reset();
                document.getElementById('productId').value = '';
            }
            
            modal.style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }
        
        function editProduct(product) {
            openModal('edit');
            document.getElementById('modalTitle').textContent = 'Editar Produto';
            document.getElementById('formAction').value = 'update_product';
            document.getElementById('productId').value = product.id;
            document.getElementById('nome').value = product.nome;
            document.getElementById('preco').value = product.preco;
            document.getElementById('estoque').value = product.estoque;
            document.getElementById('imagem').value = product.imagem;
        }
        
        function deleteProduct(id) {
            if (confirm('Tem certeza que deseja excluir este produto?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.name = 'action';
                actionInput.value = 'delete_product';
                
                const idInput = document.createElement('input');
                idInput.name = 'id';
                idInput.value = id;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Fechar modal clicando fora
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>