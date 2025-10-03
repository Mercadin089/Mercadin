<?php
session_start();
require __DIR__ . '/db.php';

// Buscar produtos do banco
$stmt = $pdo->query('SELECT id, nome, preco, estoque, imagem, criado_a FROM produtos ORDER BY criado_a DESC');
$products = $stmt->fetchAll();

$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - MERCADIN</title>
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
            <img src="" alt="logo">
        </a>

        <nav class="navbar">
            <a href="index.php">Início</a>
            <a href="index.php#sobre">Sobre</a>
            <a href="index.php#prom">Promoções</a>
            <a href="index.php#ender">Endereço</a>
            <a href="produtos.php">Produtos</a>
        </nav>

        <div class="icons">
            <a href="#" class="search-link" title="Pesquisar">
                <img width="30" height="30" src="https://img.icons8.com/windows/30/ffffff/search--v1.png" alt="search--v1"/>
            </a>

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

    <section class="prom" style="margin-top: 10rem;">
        <h2 class="title">Nossos <span>PRODUTOS</span></h2>

        <div class="box-container">
            <?php foreach ($products as $p): 
                $id = (int)$p['id'];
                $nome = $p['nome'];
                $preco = (float)$p['preco'];
                $estoque = (int)$p['estoque'];
                $img = $p['imagem'] ?: '';
                $criado_a = new DateTime($p['criado_a']);
                $esgotado = $estoque <= 0;
            ?>
            <div class="box" data-id="<?php echo $id; ?>">
                <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($nome); ?>">
                <h3><?php echo htmlspecialchars($nome); ?></h3>
                <div class="price">R$ <?php echo number_format($preco, 2, ',', '.'); ?></div>
                <div class="stock-line" style="margin:6px 0;">
                    Estoque: <span class="stock"><?php echo $estoque; ?></span>
                </div>
                <div class="product-info" style="font-size: 1.2rem; color: #ccc; margin: 8px 0;">
                    Adicionado em: <?php echo $criado_a->format('d/m/Y H:i'); ?>
                </div>
                <div class="actions" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <input type="number" class="qty" min="1" max="<?php echo $estoque; ?>" value="1" style="width:72px; padding:6px;" <?php echo $esgotado ? 'disabled' : ''; ?>>
                    <button class="btn add-btn" data-id="<?php echo $id; ?>" <?php echo $esgotado ? 'disabled' : ''; ?>>
                        <?php echo $esgotado ? 'Esgotado' : 'Adicionar ao Carrinho'; ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
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

    <script src="js/shop.js"></script>
    <script>
        // Atualiza contador do carrinho ao carregar
        (async function initCartCount() {
            try {
                const cart = await window.Shop.getCart();
                const count = (cart.items || []).reduce((a, i) => a + (i.qty || 0), 0);
                updateCartCount(count);
            } catch (e) {
                console.error('Erro ao carregar carrinho:', e);
            }
        })();

        // Liga os botões "Adicionar ao Carrinho"
        document.querySelectorAll('.add-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = parseInt(this.dataset.id, 10);
                const box = this.closest('.box');
                const qtyInput = box.querySelector('.qty');
                const stockEl = box.querySelector('.stock');
                const qty = parseInt(qtyInput.value || '1', 10);
                
                if (!qty || qty < 1) {
                    alert('Quantidade inválida');
                    return;
                }

                this.disabled = true;
                this.textContent = 'Adicionando...';
                
                try {
                    const res = await window.Shop.addToCart(id, qty);
                    
                    // Atualiza estoque no card
                    stockEl.textContent = String(res.newStock);
                    
                    // Atualiza contador do carrinho no header
                    updateCartCount(res.cartCount);

                    if (res.newStock <= 0) {
                        qtyInput.disabled = true;
                        this.textContent = 'Esgotado';
                        this.disabled = true;
                    } else {
                        qtyInput.max = res.newStock;
                        this.textContent = 'Adicionar ao Carrinho';
                        this.disabled = false;
                    }

                    // Feedback visual
                    this.style.background = '#27ae60';
                    setTimeout(() => {
                        this.style.background = '';
                    }, 1000);

                } catch (error) {
                    alert(error.message || 'Erro ao adicionar ao carrinho');
                    this.textContent = 'Adicionar ao Carrinho';
                    this.disabled = false;
                }
            });
        });

        function updateCartCount(count) {
            const cartCountElement = document.getElementById('cart-count');
            if (count > 0) {
                if (cartCountElement) {
                    cartCountElement.textContent = count;
                } else {
                    const cartLink = document.querySelector('.cart-link');
                    cartLink.innerHTML += `<span id="cart-count" aria-label="Itens no carrinho">${count}</span>`;
                }
            } else {
                if (cartCountElement) {
                    cartCountElement.remove();
                }
            }
        }
    </script>
</body>
</html>