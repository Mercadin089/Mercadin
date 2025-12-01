<?php
session_start();

// Verificar se o usuário é vendedor
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'vendedor') {
    header("Location: login.html");
    exit;
}

require __DIR__ . '/db.php';

// Processar ações do vendedor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_product':
                $nome = trim($_POST['nome']);
                $preco = (float)$_POST['preco'];
                $estoque = (int)$_POST['estoque'];
                $imagem = trim($_POST['imagem']);
                $prazo_reserva = (int)$_POST['prazo_reserva'];
                
                $stmt = $pdo->prepare('INSERT INTO produtos (nome, preco, estoque, imagem, prazo_reserva_horas, criado_a) VALUES (?, ?, ?, ?, ?, NOW())');
                $stmt->execute([$nome, $preco, $estoque, $imagem, $prazo_reserva]);
                $message = "Produto adicionado com sucesso!";
                break;
                
            case 'update_product':
                $id = (int)$_POST['id'];
                $nome = trim($_POST['nome']);
                $preco = (float)$_POST['preco'];
                $estoque = (int)$_POST['estoque'];
                $imagem = trim($_POST['imagem']);
                $prazo_reserva = (int)$_POST['prazo_reserva'];
                
                $stmt = $pdo->prepare('UPDATE produtos SET nome = ?, preco = ?, estoque = ?, imagem = ?, prazo_reserva_horas = ? WHERE id = ?');
                $stmt->execute([$nome, $preco, $estoque, $imagem, $prazo_reserva, $id]);
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

// Buscar reservas dos produtos
$reservas_stmt = $pdo->query('SELECT r.*, p.nome as produto_nome, u.nomeusuario 
                            FROM reservas r 
                            JOIN produtos p ON r.id_produto = p.id 
                            JOIN usuario u ON r.id_usuario = u.idusuario 
                            ORDER BY r.data_reserva DESC');
$reservas = $reservas_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendedor - MERCADIN</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { margin-top: 10rem; padding: 2rem; }
        .admin-actions { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .form-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); justify-content: center; align-items: center; z-index: 1000; }
        .form-content { background: var(--bg); padding: 2rem; border-radius: 10px; border: var(--border); width: 90%; max-width: 500px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; color: #fff; margin-bottom: 0.5rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.8rem; border: var(--border); border-radius: 5px; background: rgba(255,255,255,0.1); color: #fff; }
        .product-table, .reservas-table { width: 100%; border-collapse: collapse; color: #fff; margin-top: 2rem; }
        .product-table th, .product-table td, .reservas-table th, .reservas-table td { padding: 1rem; border: var(--border); text-align: left; }
        .product-table th, .reservas-table th { background: rgba(211, 173, 127, 0.2); }
        .btn-danger { background: #ff4444; }
        .btn-danger:hover { background: #cc0000; }
        .tab-container { margin-top: 2rem; }
        .tab-buttons { display: flex; gap: 1rem; margin-bottom: 1rem; }
        .tab-button { padding: 1rem 2rem; background: var(--main-color); color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        .tab-button.active { background: #b3956a; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>
    <header class="header">
        <section>
            <a href="index.php" class="logo"><img src="img/logo.jpeg" alt="logo" width="50px" height="50px"></a>
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

    <div class="admin-container">
        <h2 class="title">Painel do <span>VENDEDOR</span></h2>

        <?php if (isset($message)): ?>
            <div style="color: #27ae60; text-align: center; margin: 1rem 0; font-size: 1.6rem;"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div style="color: #ff4444; text-align: center; margin: 1rem 0; font-size: 1.6rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="openTab('produtos')">Produtos</button>
                <button class="tab-button" onclick="openTab('reservas')">Reservas</button>
            </div>

            <div id="produtos" class="tab-content active">
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
                            <th>Prazo Reserva (horas)</th>
                            <th>Data de Criação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><img src="<?php echo htmlspecialchars($product['imagem']); ?>" alt="<?php echo htmlspecialchars($product['nome']); ?>" style="width: 50px; height: 50px; object-fit: cover;"></td>
                            <td><?php echo htmlspecialchars($product['nome']); ?></td>
                            <td>R$ <?php echo number_format($product['preco'], 2, ',', '.'); ?></td>
                            <td><?php echo $product['estoque']; ?></td>
                            <td><?php echo $product['prazo_reserva_horas']; ?>h</td>
                            <td><?php echo date('d/m/Y H:i', strtotime($product['criado_a'])); ?></td>
                            <td>
                                <button class="btn" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">Editar</button>
                                <button class="btn btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">Excluir</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="reservas" class="tab-content">
                <table class="reservas-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Produto</th>
                            <th>Cliente</th>
                            <th>Quantidade</th>
                            <th>Data Reserva</th>
                            <th>Data Limite</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td><?php echo $reserva['id']; ?></td>
                            <td><?php echo htmlspecialchars($reserva['produto_nome']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['nomeusuario']); ?></td>
                            <td><?php echo $reserva['quantidade']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($reserva['data_reserva'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($reserva['data_limite'])); ?></td>
                            <td>
                                <span style="color: 
                                    <?php echo $reserva['status'] == 'confirmada' ? '#27ae60' : 
                                        ($reserva['status'] == 'pendente' ? '#f39c12' : 
                                        ($reserva['status'] == 'cancelada' ? '#e74c3c' : '#95a5a6')); ?>">
                                    <?php echo ucfirst($reserva['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
                    <label for="prazo_reserva">Prazo de Reserva (horas):</label>
                    <input type="number" id="prazo_reserva" name="prazo_reserva" min="1" value="24" required>
                </div>
                
                <div class="form-group">
                    <label for="imagem">URL da Imagem:</label>
                    <input type="url" id="imagem" name="imagem" >
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

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
                document.getElementById('prazo_reserva').value = '24';
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
            document.getElementById('prazo_reserva').value = product.prazo_reserva_horas;
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
        
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>