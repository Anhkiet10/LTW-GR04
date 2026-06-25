<?php

require_once __DIR__ . '/../../core/Model.php';

class CartModel extends Model{

    public function getCartByUser($userId)
    {
        $cart = $this->fetchOne("
            SELECT *
            FROM cart
            WHERE user_id = $userId
        ");

        if (!$cart) {

            $this->query("
                INSERT INTO cart(user_id)
                VALUES($userId)
            ");

            $cart = $this->fetchOne("
                SELECT *
                FROM cart
                WHERE user_id = $userId
            ");
        }

        return $cart;
    }
    public function addToCart($cartId,$productId,$variantId)
    {
        $exist = $this->fetchOne("
            SELECT cart_item_id, quantity
            FROM cart_items
            WHERE cart_id = $cartId
            AND variant_id = $variantId
        ");

        // Nếu đã có trong giỏ
        if($exist){

            $newQty = $exist['quantity'] + 1;

            return $this->query("
                UPDATE cart_items
                SET quantity = $newQty
                WHERE cart_item_id = {$exist['cart_item_id']}
            ");
        }

        // Nếu chưa có thì INSERT
        $variant = $this->fetchOne("
            SELECT price
            FROM product_variants
            WHERE variant_id = $variantId
        ");

        $price = $variant['price'];

        return $this->query("
            INSERT INTO cart_items
            (
                cart_id,
                product_id,
                variant_id,
                quantity,
                price_snapshot
            )
            VALUES
            (
                $cartId,
                $productId,
                $variantId,
                1,
                $price
            )
        ");
    }

    public function getCartItems($cartId){
        $sql=("
            SELECT 
                ci.cart_item_id,
                ci.product_id,
                ci.variant_id,
                ci.quantity,
                ci.price_snapshot,
                p.product_name,
                p.description,
                pv.variant_key
            FROM cart_items ci
            INNER JOIN products p ON ci.product_id = p.product_id
            LEFT JOIN product_variants pv ON ci.variant_id = pv.variant_id
            WHERE ci.cart_id=$cartId
            ORDER BY ci.cart_item_id DESC
        ");

        $items = $this->fetchAll($sql);

        foreach($items as &$item){
            $variantKey = $item['variant_key'] ?? '';

            if(empty($variantKey) || $variantKey === 'default'){
                $item['variant_key'] = '';
                continue;
            }

            $ids = str_replace("_", ",", $variantKey);

            if(!preg_match('/^[\d,]+$/', $ids)){
                $item['variant_key'] = '';
                continue;
            }

            $result = $this->fetchAll("
                SELECT value_name
                FROM attribute_values
                WHERE value_id IN($ids)
            ");

            $item['variant_key'] = implode("-", array_column($result, "value_name"));
        }

        return $items; 
    }                   

    public function updateQuantity($cartItemId,$quantity,$cartId)
    {
    $sql="
        UPDATE cart_items
        SET quantity=$quantity
        WHERE cart_item_id=$cartItemId
        AND cart_id=$cartId
    ";

    return $this->query($sql);
    }
   public function deleteItem($cartItemId,$cartId)
    {
        $sql="
            DELETE FROM cart_items
            WHERE cart_item_id=$cartItemId
            AND cart_id=$cartId
        ";

        return $this->query($sql);
    }


    //đặt hàng
    public function getUserInfo($userId)
    {
        return $this->fetchOne("
            SELECT *
            FROM users
            WHERE user_id = $userId
        ");
    }

    public function getDefaultAddress($userId)
    {
        return $this->fetchOne("
            SELECT *
            FROM addresses
            WHERE user_id = $userId
            AND is_default = 1
            LIMIT 1
        ");
    }
    public function saveUserPhone($userId,$phone)
    {
        return $this->query("
            UPDATE users
            SET phone = '$phone'
            WHERE user_id = $userId
        ");
    }
    public function saveAddress(
        $userId,
        $fullAddress,
        $city
    )
    {
        return $this->query("
            INSERT INTO addresses
            (
                user_id,
                label,
                full_address,
                city,
                is_default
            )
            VALUES
            (
                $userId,
                'Nhà',
                '$fullAddress',
                '$city',
                1
            )
        ");
    }
    public function clearCart($cartId)
    {
        return $this->query("
            DELETE FROM cart_items
            WHERE cart_id = $cartId
        ");
    }

    public function getStockByVariant($variantId)
    {
        $result = $this->fetchOne("
            SELECT stock_quantity
            FROM product_variants
            WHERE variant_id = $variantId
        ");
        return $result ? (int)$result['stock_quantity'] : 0;
    }

    public function reduceStock($variantId, $quantity)
    {
        return $this->query("
            UPDATE product_variants
            SET stock_quantity = stock_quantity - $quantity
            WHERE variant_id = $variantId
            AND stock_quantity >= $quantity
        ");
    }

}  