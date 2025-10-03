<?php
// public/api.php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db.php';

function respond($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function ensureCart() {
  if (!isset($_SESSION['cart'])) $_SESSION['cart'] = []; // [id => ['qty'=>int]]
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($method === 'GET' && $action === 'products') {
    $stmt = $pdo->query('SELECT id, nome, preco, estoque, imagem, criado_a FROM produtos ORDER BY id');
    $products = $stmt->fetchAll();
    respond(['products' => $products]);
}

if ($method === 'GET' && $action === 'cart') {
    ensureCart();
    $cart = $_SESSION['cart'];
    $ids = array_map('intval', array_keys($cart));

    $items = [];
    $total = 0.0;

    if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, nome, preco, imagem FROM produtos WHERE id IN ($in)");
    $stmt->execute($ids);
    $map = [];
    foreach ($stmt->fetchAll() as $p) {
        $map[(int)$p['id']] = $p;
    }

    foreach ($cart as $id => $row) {
        $id = (int)$id;
        if (!isset($map[$id])) continue;
        $p = $map[$id];
        $qty = (int)$row['qty'];
      $line = $qty * (float)$p['preco'];
        $total += $line;
        $items[] = [
        'id' => $id,
        'name' => $p['nome'],
        'price' => (float)$p['preco'],
        'qty' => $qty,
        'image' => $p['imagem'],
        'subtotal' => $line
        ];
    }
    }

    respond(['items' => $items, 'total' => $total]);
}

if ($method === 'POST' && $action === 'addToCart') {
    ensureCart();
    $id = (int)($_POST['id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 1);
    if ($id <= 0 || $qty <= 0) respond(['error' => 'Parâmetros inválidos'], 400);

    try {
    $pdo->beginTransaction();

    // Bloqueia a linha do produto até o commit/rollback
    $stmt = $pdo->prepare('SELECT id, nome, preco, estoque FROM produtos WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if (!$p) {
        $pdo->rollBack();
        respond(['error' => 'Produto não encontrado'], 404);
    }

    $stock = (int)$p['estoque'];
    if ($stock < $qty) {
        $pdo->rollBack();
        respond(['error' => 'Estoque insuficiente', 'stock' => $stock], 409);
    }

    $newStock = $stock - $qty;
    $upd = $pdo->prepare('UPDATE produtos SET estoque = ? WHERE id = ?');
    $upd->execute([$newStock, $id]);

    $pdo->commit();

    // Atualiza sessão
    $_SESSION['cart'][$id]['qty'] = ($_SESSION['cart'][$id]['qty'] ?? 0) + $qty;
    $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));

    respond([
        'message' => 'Adicionado ao carrinho',
        'productId' => $id,
        'newStock' => $newStock,
        'cartCount' => $cartCount
    ]);
    } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    respond(['error' => 'Erro ao adicionar'], 500);
    }
}

if ($method === 'POST' && $action === 'removeFromCart') {
    ensureCart();
    $id = (int)($_POST['id'] ?? 0);
    $qty = (int)($_POST['qty'] ?? 1);
    if ($id <= 0 || $qty <= 0) respond(['error' => 'Parâmetros inválidos'], 400);

    $current = (int)($_SESSION['cart'][$id]['qty'] ?? 0);
    if ($current <= 0) respond(['error' => 'Produto não está no carrinho'], 400);

    $toRemove = min($qty, $current);

    try {
    $pdo->beginTransaction();

    // Garante que o produto existe e bloqueia a linha
    $stmt = $pdo->prepare('SELECT id FROM produtos WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if (!$p) {
        $pdo->rollBack();
        respond(['error' => 'Produto não encontrado'], 404);
    }

    $upd = $pdo->prepare('UPDATE produtos SET estoque = estoque + ? WHERE id = ?');
    $upd->execute([$toRemove, $id]);

    $pdo->commit();

    $remaining = $current - $toRemove;
    if ($remaining > 0) {
        $_SESSION['cart'][$id]['qty'] = $remaining;
    } else {
        unset($_SESSION['cart'][$id]);
    }

    $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
    respond([
        'message' => 'Removido do carrinho',
        'productId' => $id,
        'removed' => $toRemove,
        'cartCount' => $cartCount
    ]);
    } catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    respond(['error' => 'Erro ao remover'], 500);
    }
}

respond(['error' => 'Rota não encontrada'], 404);