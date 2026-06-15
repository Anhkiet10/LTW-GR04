<?php

require_once __DIR__ . '/../../core/Model.php';

class CartModel extends Model{
    public function addToCart($productId, $variantId){
        $productId= (int)$productId;
        $variantId= (int)$variantId;
        
        //tạm dùng user_id=2
        $cart= $this->fetchOne("
        SELECT cart_id
        FROM cart
        WHERE user_id=2
        ");
        $cartId = $cart['cart_id'];

        $variant = $this->fetchOne("
        SELECT price
        FROM product_variants
        WHERE variant_id= $variantId
        ");
        $price =$variant['price'];
        
        // Kiểm tra sản phẩm đã có trong giỏ chưa
        $exists=$this->fetchOne("
            SELECT cart_item_id, quantity
            FROM cart_items
            WHERE cart_id= $cartId
            AND variant_id=$variantId
        ");

        if($exists){
            //Nếu đã có thì thêm vào
            $this->query("
            UPDATE cart_items
            SET quantity=quantity +1
            WHERE cart_item_id={$exists['cart_item_id']}
            ");
        }else{
            //Nếu chưa có thì thêm mới
        $this->query("
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
    }
    public function getCartItems(){
        return $this->fetchAll("
        SELECT 
            ci.cart_item_id,
            ci.quantity,
            ci.price_snapshot,

            p.product_name,
            p.description,

            pv.variant_id,
            pv.sku
        FROM cart_items ci

        JOIN products p
            ON ci.product_id = p.product_id

        LEFT JOIN product_variants pv
            ON ci.variant_id = pv.variant_id
        
        WHERE ci.cart_id=1
    ");
    }

    public function updateQuantity($cartItemId,$quantity)
    {
        $cartItemId=(int) $cartItemId;
        $quantity = (int) $quantity;

        $this->query(
            "UPDATE cart_items
            SET quantity = $quantity
            WHERE cart_item_id = $cartItemId"
        );
    }
    public function deleteItem($cartItemId) {
        return $this->query("
            DELETE FROM cart_items
            WHERE cart_item_id = $cartItemId
    ");
    }
}