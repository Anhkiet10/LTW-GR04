<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['product_id']) || !isset($input['variant_id']) || !isset($input['quantity'])) {
        throw new Exception('Missing required fields');
    }

    $productId = (int)$input['product_id'];
    $variantId = (int)$input['variant_id'];
    $quantity = max(1, (int)$input['quantity']);

    $db = Database::getConnection();
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    $variantStmt = $db->prepare("SELECT pv.price, pv.stock_quantity FROM product_variants pv WHERE pv.variant_id = ?");
    if (!$variantStmt) {
        throw new Exception('Prepare failed');
    }

    $variantStmt->bind_param('i', $variantId);
    $variantStmt->execute();
    $variantResult = $variantStmt->get_result();
    $variant = $variantResult->fetch_assoc();
    $variantStmt->close();

    if (!$variant) {
        throw new Exception('Product variant not found');
    }

    if ($variant['stock_quantity'] < $quantity) {
        throw new Exception('Insufficient stock');
    }

    // For now, use a guest/default user ID. In production, use actual user session
    $userId = isset($input['user_id']) ? (int)$input['user_id'] : 2;

    $cartStmt = $db->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
    if (!$cartStmt) {
        throw new Exception('Prepare failed');
    }

    $cartStmt->bind_param('i', $userId);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();
    $cartRow = $cartResult->fetch_assoc();
    $cartStmt->close();

    if (!$cartRow) {
        $insertCartStmt = $db->prepare("INSERT INTO cart (user_id) VALUES (?)");
        if (!$insertCartStmt) {
            throw new Exception('Prepare failed');
        }
        $insertCartStmt->bind_param('i', $userId);
        if (!$insertCartStmt->execute()) {
            throw new Exception('Failed to create cart');
        }
        $cartId = $insertCartStmt->insert_id;
        $insertCartStmt->close();
    } else {
        $cartId = (int)$cartRow['cart_id'];
    }

    $checkStmt = $db->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = ? AND variant_id = ?");
    if (!$checkStmt) {
        throw new Exception('Prepare failed');
    }

    $checkStmt->bind_param('ii', $cartId, $variantId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $existingItem = $checkResult->fetch_assoc();
    $checkStmt->close();

    if ($existingItem) {
        $newQuantity = $existingItem['quantity'] + $quantity;
        $updateStmt = $db->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
        if (!$updateStmt) {
            throw new Exception('Prepare failed');
        }
        $updateStmt->bind_param('ii', $newQuantity, $existingItem['cart_item_id']);
        if (!$updateStmt->execute()) {
            throw new Exception('Failed to update cart item');
        }
        $updateStmt->close();
    } else {
        $insertStmt = $db->prepare("INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, price_snapshot) VALUES (?, ?, ?, ?, ?)");
        if (!$insertStmt) {
            throw new Exception('Prepare failed');
        }
        $insertStmt->bind_param('iiiii', $cartId, $productId, $variantId, $quantity, $variant['price']);
        if (!$insertStmt->execute()) {
            throw new Exception('Failed to add item to cart');
        }
        $insertStmt->close();
    }

    echo json_encode(['success' => true, 'message' => 'Product added to cart']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;
