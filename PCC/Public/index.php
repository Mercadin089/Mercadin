<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MERCADIN</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Josefin+Sans:ital,wght@0,100..700;1,100..700&display=swap"
        rel="stylesheet">

<?php
// -------------------- Sessão e autenticação --------------------
session_start();

$logado = isset($_SESSION['usuario']);
if (!$logado) {
    header("Location: login.html");
    exit;
}

// -------------------- Conexão com MySQL --------------------
require __DIR__ . '/db.php'; // ajuste o caminho se seu db.php estiver em outro local

// -------------------- Dados do carrinho e produtos --------------------
$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));

$stmt = $pdo->query('SELECT id, nome AS name, preco AS price, estoque AS stock, imagem AS image FROM produtos ORDER BY id');
$products = $stmt->fetchAll();

echo "Bem-vindo, " . htmlspecialchars($_SESSION['usuario']) . "!";
?>
</head>

<body>
    <header class="header">
        <section>
            <a href="#" class="logo">
                <!--colocar imagem da logo-->
                <img src="" alt="logo">
            </a>

            <nav class="navbar">
                <a href="#inicio">Início</a>
                <a href="#sobre">Sobre</a>
                <a href="#prom">Promoções</a>
                <a href="#ender">Endereço</a>
                <a href="produtos.html">Produtos</a>
            </nav>

            <div class="icons">
                <a href="<?php echo $logado ? 'usuario.php' : 'login.html'; ?>">
                    <img width="30" height="30" src="https://img.icons8.com/windows/30/ffffff/search--v1.png" alt="search--v1" />
                    <?php if ($logado): ?>
                        <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                    <?php endif; ?>
                </a>

                <!-- Ícone do carrinho com contador -->
                <a href="carrinho.php" class="cart-link" style="position:relative; display:inline-flex; align-items:center; gap:6px;">
                    <img width="30" height="30" src="https://img.icons8.com/material-outlined/30/ffffff/shopping-cart--v1.png" alt="shopping-cart--v1" />
                    <span id="cart-count" style="font-size:14px;"><?php echo (int)$cartCount; ?></span>
                </a>

                <a href="login.html" target="_blank">
                    <img width="30" height="30" src="https://img.icons8.com/material-sharp/30/ffffff/user-male-circle.png" alt="user-male-circle">
                </a>
            </div>
        </section>
    </header>

    <!-- Shop.js: funções de API (ajuste o caminho conforme sua estrutura de pastas) -->
    <script src="js/shop.js"></script>

    <script>
    // Atualiza contador do carrinho ao carregar
    (async function initCartCount() {
        try {
            const cart = await window.Shop.getCart();
            const count = (cart.items || []).reduce((a, i) => a + (i.qty || 0), 0);
            document.getElementById('cart-count').textContent = String(count);
        } catch (e) {
            // silencioso
        }
    })();

    // Liga os botões "Adicione ao Carrinho" às chamadas da API
    document.querySelectorAll('.box[data-id]').forEach(box => {
        const id = parseInt(box.dataset.id, 10);
        const btn = box.querySelector('.add-btn');
        const qtyInput = box.querySelector('.qty');
        const stockEl = box.querySelector('.stock');

        btn?.addEventListener('click', async () => {
            const qty = parseInt(qtyInput?.value || '1', 10);
            if (!qty || qty < 1) return;

            btn.disabled = true;
            try {
                const res = await window.Shop.addToCart(id, qty);
                // atualiza estoque no card
                stockEl.textContent = String(res.newStock);
                // atualiza contador do carrinho no header
                document.getElementById('cart-count').textContent = String(res.cartCount);

                if (res.newStock <= 0) {
                    qtyInput.disabled = true;
                    btn.textContent = 'Esgotado';
                    btn.disabled = true;
                } else {
                    btn.disabled = false;
                }
            } catch (e) {
                alert(e.message || 'Erro ao adicionar');
                btn.disabled = false;
            }
        });
    });
    </script>

        <?php



session_start();


$logado = isset($_SESSION['usuario']);



if (!$logado) {
    header("Location: login.html");
    exit;
}

echo "Bem-vindo, " . htmlspecialchars($_SESSION['usuario']) . "!";
?>

</head>

    <div class="home-container">
        <section id="inicio">
            <div class="content">
                <h3>Os melhores produtos da região</h3>
                <p>Cultivamos qualidade, sustentabilidade e sabor. Você leva para casa os melhores produtos da região –
                    frescos, nutritivos e cultivados com dedicação. Das frutas suculentas às verduras crocantes, cada
                    alimento é colhido no ponto certo para garantir o máximo de sabor e nutrientes.</p>
                <a href="produtos.html" class="btn">Pegue o seu agora</a>
            </div>

        </section>

    </div>

    <section class="sobre" id="sobre">
        <h2 class="title">Sobre <span>NÓS</span></h2>
        <div class="row">
            <div class="container-image">
                <img src="img/ricardo gomez.jpg" alt="sobre-nos">
            </div>
            <div class="content">
                <h3>O que torna nosso produtos especiais</h3>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Dolor natus, quod ratione explicabo commodi
                    ad assumenda repudiandae incidunt recusandae deserunt similique magnam doloremque beatae illum
                    voluptatibus distinctio consequuntur suscipit animi!</p>
                <a href="#" class="btn">Saiba mais</a>
    </section>
            </div>

                <!-- PROMOÇÕES: renderizando do banco com estoque e botão de adicionar -->
    <section class="prom" id="prom">
        <h2 class="title">Nossas <span>PROMOÇÕES</span></h2>

        <div class="box-container">
            <?php foreach ($products as $p): 
                $id = (int)$p['id'];
                $nome = $p['name'];
                $preco = (float)$p['price'];
                $estoque = (int)$p['stock'];
                $img = $p['image'] ?: '';
                $esgotado = $estoque <= 0;
            ?>
            <div class="box" data-id="<?php echo $id; ?>">
                <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($nome); ?>">
                <h3><?php echo htmlspecialchars($nome); ?></h3>
                <div class="price">R$ <?php echo number_format($preco, 2, ',', '.'); ?></div>
                <div class="stock-line" style="margin:6px 0;">Estoque: <span class="stock"><?php echo $estoque; ?></span></div>
                <div class="actions" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <input type="number" class="qty" min="1" value="1" style="width:72px; padding:6px;" <?php echo $esgotado ? 'disabled' : ''; ?>>
                    <button class="btn add-btn" <?php echo $esgotado ? 'disabled' : ''; ?>>
                        <?php echo $esgotado ? 'Esgotado' : 'Adicione ao Carrinho'; ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

            <div>

    <section class="ender" id="ender">
        <h2 class="title">Nosso <span>ENDEREÇO</span></h2>

        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14502.485217074212!2d-47.556571345129775!3d-24.671160148850365!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94daa1a0cc4d5d3f%3A0xb6abdf69b4e73124!2sEtec%20Engenheiro%20Agr%C3%B4nomo%20Narciso%20de%20Medeiros!5e0!3m2!1spt-BR!2sbr!4v1748518787702!5m2!1spt-BR!2sbr"
            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>

    </section>

    <section class="footer">
        <div class="share">
            <a href="https://www.instagram.com/eteciguape/" target="_blank">
                <img width="30" height="30" src="https://img.icons8.com/ios/30/ffffff/instagram-new--v1.png"
                    alt="instagram-new--v1" />
            </a>

            <a href="https://www.facebook.com/eteciguape/?locale=pt_BR" target="_blank">
                <img width="30" height="30" src="https://img.icons8.com/ios/30/ffffff/facebook--v1.png"
                    alt="facebook--v1" />
            </a>
            <a href="https://www.youtube.com/channel/UC5DBOb6OWFPNaArYx8xxQiA/videos" target="_blank">
                <img width="30" height="30" src="https://img.icons8.com/ios/30/ffffff/youtube-play--v1.png"
                    alt="youtube-play--v1" />
            </a>

        </div>

    </section>
</body>

</html>