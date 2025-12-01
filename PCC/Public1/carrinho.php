<?php
session_start();
require __DIR__ . '/db.php';

$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));

// Se o JavaScript falhar, vamos tentar carregar o carrinho via PHP
$cartItems = [];
$cartTotal = 0.0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, nome, preco, imagem FROM produtos WHERE id IN ($in)");
        $stmt->execute($ids);
        $productsMap = [];
        foreach ($stmt->fetchAll() as $p) {
            $productsMap[(int)$p['id']] = $p;
        }

        foreach ($_SESSION['cart'] as $id => $row) {
            $id = (int)$id;
            if (!isset($productsMap[$id])) continue;
            $p = $productsMap[$id];
            $qty = (int)$row['qty'];
            $subtotal = $qty * (float)$p['preco'];
            $cartTotal += $subtotal;
            $cartItems[] = [
                'id' => $id,
                'name' => $p['nome'],
                'price' => (float)$p['preco'],
                'qty' => $qty,
                'image' => $p['imagem'],
                'subtotal' => $subtotal
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - MERCADIN</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <style>
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            color: #fff;
            margin: 2rem 0;
        }
        .cart-table th, .cart-table td {
            padding: 1rem;
            border-bottom: 0.1rem solid rgba(255,255,255,0.3);
            text-align: left;
        }
        .cart-table th {
            background: rgba(211, 173, 127, 0.2);
        }
        .qty-btn {
            background: var(--main-color);
            color: #fff;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: bold;
        }
        .qty-btn:disabled {
            background: #666;
            cursor: not-allowed;
        }
        .qty-btn:hover:not(:disabled) {
            background: #b3956a;
        }
        .loading {
            text-align: center;
            color: #fff;
            font-size: 1.8rem;
            padding: 2rem;
        }
        .error-message {
            text-align: center;
            color: #ff4444;
            font-size: 1.6rem;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <section>
            <a href="index.php" class="logo">
                <img src="img/logo.jpeg" alt="logo" width="50px" height="50px">
            </a>
            <nav class="navbar">
                <a href="index.php">Início</a>
                <a href="index.php#sobre">Sobre</a>
                <a href="index.php#prom">Promoções</a>
                <a href="index.php#ender">Endereço</a>
                <a href="produtos.php">Produtos</a>
                <a href="vendedor.php">Vendedor</a>
            </nav>
            <div class="icons">

                <a href="carrinho.php" class="cart-link" style="position:relative; display:inline-flex; align-items:center; gap:6px;" title="Carrinho">
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

    <section class="carrinho" style="margin-top: 10rem;">
        <h2 class="title">Meu <span>CARRINHO</span></h2>
        
        <div id="cart-content">
            <?php if (empty($cartItems)): ?>
                <div style="text-align: center; color: #fff; font-size: 2rem;">
                    <p>Seu carrinho está vazio</p>
                    <a href="produtos.php" class="btn" style="margin-top: 2rem;">Continuar Comprando</a>
                </div>
            <?php else: ?>
                <!-- Fallback em PHP caso o JavaScript falhe -->
                <div class="cart-items">
                    <div style="overflow-x: auto;">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th style="text-align: center;">Quantidade</th>
                                    <th style="text-align: right;">Subtotal</th>
                                    <th style="text-align: center;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                            <div>
                                                <h3 style="color: var(--main-color); margin: 0;"><?php echo htmlspecialchars($item['name']); ?></h3>
                                                <p style="margin: 0.5rem 0 0 0;">R$ <?php echo number_format($item['price'], 2, ',', '.'); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                            <form method="POST" action="api.php" style="display: inline;">
                                                <input type="hidden" name="action" value="removeFromCart">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="qty" value="1">
                                                <button type="submit" class="qty-btn" <?php echo $item['qty'] <= 1 ? 'disabled' : ''; ?> onclick="return confirm('Remover um item?')">-</button>
                                            </form>
                                            <span style="padding: 0 1rem; min-width: 30px; display: inline-block;"><?php echo $item['qty']; ?></span>
                                            <form method="POST" action="api.php" style="display: inline;">
                                                <input type="hidden" name="action" value="addToCart">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="qty" value="1">
                                                <button type="submit" class="qty-btn">+</button>
                                            </form>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <form method="POST" action="api.php" style="display: inline;">
                                            <input type="hidden" name="action" value="removeFromCart">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="qty" value="<?php echo $item['qty']; ?>">
                                            <button type="submit" class="btn" style="background: #ff4444; padding: 0.5rem 1rem; font-size: 1.4rem;" onclick="return confirm('Remover todos os itens?')">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" style="text-align: right; font-weight: bold; border-top: 0.1rem solid rgba(255,255,255,0.3);">Total:</td>
                                    <td colspan="2" style="text-align: right; font-weight: bold; color: var(--main-color); border-top: 0.1rem solid rgba(255,255,255,0.3);">
                                        R$ <?php echo number_format($cartTotal, 2, ',', '.'); ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="produtos.php" class="btn" style="margin-right: 1rem;">Continuar Comprando</a>
                        <button class="btn" onclick="checkout()" style="background: #27ae60;">Finalizar Compra</button>
                    </div>
                </div>
            <?php endif; ?>
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

    <!-- JavaScript moderno com fallback -->
    <script>
        // Função para carregar script dinamicamente
        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = () => resolve();
                script.onerror = () => reject(new Error(`Falha ao carregar script: ${src}`));
                document.head.appendChild(script);
            });
        }

        // Tenta carregar o shop.js
        loadScript('js/shop.js')
            .then(() => {
                console.log('Shop.js carregado com sucesso');
                // Agora tenta carregar o carrinho via JavaScript
                if (typeof Shop !== 'undefined') {
                    loadCartWithJS();
                } else {
                    console.warn('Shop não definido após carregamento');
                }
            })
            .catch(error => {
                console.error('Erro ao carregar shop.js:', error);
                document.getElementById('cart-content').innerHTML = `
                    <div class="error-message">
                        JavaScript não carregou corretamente, mas você ainda pode usar o carrinho com os controles básicos.
                        <br><br>
                        <a href="javascript:location.reload()" class="btn">Tentar Recarregar</a>
                    </div>
                `;
            });

        // Função para carregar carrinho via JavaScript
        async function loadCartWithJS() {
            try {
                const cart = await Shop.getCart();
                const cartContent = document.getElementById('cart-content');
                
                if (!cart.items || cart.items.length === 0) {
                    cartContent.innerHTML = `
                        <div style="text-align: center; color: #fff; font-size: 2rem;">
                            <p>Seu carrinho está vazio</p>
                            <a href="produtos.php" class="btn" style="margin-top: 2rem;">Continuar Comprando</a>
                        </div>
                    `;
                    return;
                }

                let html = `
                    <div class="cart-items">
                        <div style="overflow-x: auto;">
                            <table class="cart-table">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th style="text-align: center;">Quantidade</th>
                                        <th style="text-align: right;">Subtotal</th>
                                        <th style="text-align: center;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                cart.items.forEach(item => {
                    html += `
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img src="${item.image || ''}" alt="${item.name}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                    <div>
                                        <h3 style="color: var(--main-color); margin: 0;">${item.name}</h3>
                                        <p style="margin: 0.5rem 0 0 0;">R$ ${item.price ? item.price.toFixed(2) : '0.00'}</p>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                    <button class="qty-btn" onclick="updateQuantity(${item.id}, ${item.qty - 1})" ${item.qty <= 1 ? 'disabled' : ''}>-</button>
                                    <span style="padding: 0 1rem; min-width: 30px; display: inline-block;">${item.qty}</span>
                                    <button class="qty-btn" onclick="updateQuantity(${item.id}, ${item.qty + 1})">+</button>
                                </div>
                            </td>
                            <td style="text-align: right;">
                                R$ ${item.subtotal ? item.subtotal.toFixed(2) : '0.00'}
                            </td>
                            <td style="text-align: center;">
                                <button class="btn" onclick="removeItem(${item.id})" style="background: #ff4444; padding: 0.5rem 1rem; font-size: 1.4rem;">Remover</button>
                            </td>
                        </tr>
                    `;
                });

                html += `
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" style="text-align: right; font-weight: bold; border-top: 0.1rem solid rgba(255,255,255,0.3);">Total:</td>
                                        <td colspan="2" style="text-align: right; font-weight: bold; color: var(--main-color); border-top: 0.1rem solid rgba(255,255,255,0.3);">
                                            R$ ${cart.total ? cart.total.toFixed(2) : '0.00'}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div style="text-align: center; margin-top: 2rem;">
                            <a href="produtos.php" class="btn" style="margin-right: 1rem;">Continuar Comprando</a>
                            <button class="btn" onclick="checkout()" style="background: #27ae60;">Finalizar Compra</button>
                        </div>
                    </div>
                `;

                cartContent.innerHTML = html;
                updateCartCount(cart.items.reduce((total, item) => total + (item.qty || 0), 0));

            } catch (error) {
                console.error('Erro ao carregar carrinho via JS:', error);
                // Mantém o fallback em PHP
            }
        }

        async function updateQuantity(productId, newQty) {
            if (newQty < 1) {
                await removeItem(productId);
                return;
            }
            
            try {
                const currentQty = await getCurrentQuantity(productId);
                if (newQty > currentQty) {
                    await Shop.addToCart(productId, newQty - currentQty);
                } else {
                    await Shop.removeFromCart(productId, currentQty - newQty);
                }
                await loadCartWithJS();
            } catch (error) {
                alert('Erro ao atualizar quantidade: ' + (error.message || 'Erro desconhecido'));
            }
        }

        async function getCurrentQuantity(productId) {
            try {
                const cart = await Shop.getCart();
                const item = cart.items.find(item => item.id === productId);
                return item ? item.qty : 0;
            } catch (error) {
                return 0;
            }
        }

        async function removeItem(productId) {
            if (!confirm('Tem certeza que deseja remover este item do carrinho?')) return;
            
            try {
                const currentQty = await getCurrentQuantity(productId);
                await Shop.removeFromCart(productId, currentQty);
                await loadCartWithJS();
            } catch (error) {
                alert('Erro ao remover item: ' + (error.message || 'Erro desconhecido'));
            }
        }

        function updateCartCount(count) {
            const cartCountElement = document.getElementById('cart-count');
            if (count > 0) {
                if (cartCountElement) {
                    cartCountElement.textContent = count;
                } else {
                    const cartLink = document.querySelector('.cart-link');
                    if (cartLink) {
                        const span = document.createElement('span');
                        span.id = 'cart-count';
                        span.setAttribute('aria-label', 'Itens no carrinho');
                        span.textContent = count;
                        span.style.cssText = `
                            position: absolute;
                            top: -6px;
                            right: -8px;
                            min-width: 18px;
                            height: 18px;
                            padding: 0 5px;
                            font-size: 11px;
                            line-height: 18px;
                            text-align: center;
                            border-radius: 999px;
                            background: #d3ad7f;
                            color: #fff;
                            box-shadow: 0 0 0 2px rgba(0,0,0,0.4);
                        `;
                        cartLink.appendChild(span);
                    }
                }
            } else {
                if (cartCountElement) {
                    cartCountElement.remove();
                }
            }
        }

        function checkout() {
            alert('Funcionalidade de checkout em desenvolvimento! Em breve você poderá finalizar sua compra.');
        }
    </script>
</body>
</html>