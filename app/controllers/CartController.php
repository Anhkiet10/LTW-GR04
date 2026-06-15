

<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/CartModel.php';

class CartController extends Controller{
    public function index(){
       $cartModel= new CartModel();

       $items= $cartModel->getCartItems();

       $this->render('cart/index',[
            'items'=>$items
       ]);

    }
    public function add($productId, $variantId ){
       $cartModel= new CartModel();

       $cartModel->addToCart($productId, $variantId);
        }
    
        public function addAjax(){
            $data = json_decode(file_get_contents("php://input"), true);

            $cartModel= new CartModel();
            $cartModel->addToCart($data['product_id'],$data['variant_id']);

            echo json_encode([
                "success" =>true
            ]);
        }
    
        public function update()
        {
            $data= json_decode(file_get_contents("php://input"), true);


            $cartModel= new CartModel();

            $cartModel->updateQuantity(
                $data['cart_item_id'],
                $data['quantity']
            );

            echo json_encode([
                "success"=>true
            ]);
        }

        public function delete() {
            $data = json_decode(file_get_contents("php://input"), true);

            $cartItemId = (int)$data['cart_item_id'];

            $cartModel = new CartModel();
            $result = $cartModel->deleteItem($cartItemId);

            echo json_encode([
                "success" => $result
        ]);
}
}   

    

?>