

<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../models/CartModel.php';


class CartController extends Controller{
    public function index()
    {
        $cartModel = new CartModel();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            
        }
          if (!isset($_SESSION['user_id'])) {
        header("Location: /WEB_GR4/login");
        exit;
        }

    $userId = $_SESSION['user_id'];

    $cart = $cartModel->getCartByUser($userId);

    $items = $cartModel->getCartItems($cart['cart_id']);

    require __DIR__ . '/../views/cart/index.php';
    }


    public function add()
    {

    if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                "success" => false,
                "login" => true
            ]);
            exit;
        }
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents("php://input"), true);

        $productId = $data['product_id'];
        $variantId = $data['variant_id'];

        $cartModel = new CartModel();

        $userId = $_SESSION['user_id'];
        $cart = $cartModel->getCartByUser($userId);

        $cartModel->addToCart(
            $cart['cart_id'],
            $productId,
            $variantId
        );

        echo json_encode([
            "success" => true
        ]);
    }
    
    public function update()
    {
         if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

   
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $cartItemId = $data["cart_item_id"];
        $quantity = $data["quantity"];

        $cartModel = new CartModel();


        $userId = $_SESSION['user_id'];

        $cart = $cartModel->getCartByUser($userId);

        $cartModel->updateQuantity(
            $cartItemId,
            $quantity,
            $cart['cart_id']
        );

        echo json_encode([
            "success" => true
        ]);
    }   

    public function delete()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $cartItemId = $data["cart_item_id"];

        $cartModel = new CartModel();

        
        $userId = $_SESSION['user_id'];

        $cart = $cartModel->getCartByUser($userId);

        $cartModel->deleteItem(
            $cartItemId,
            $cart['cart_id']
        );

        echo json_encode([
            "success" => true
        ]);
    }


    public function checkInfo(){
        if(session_status()===PHP_SESSION_NONE){
            session_start();
        }

        $userId=$_SESSION['user_id'];
        $cartModel=new CartModel();

        $user=$cartModel->getUserInfo($userId);
        $address=$cartModel->getDefaultAddress($userId);

        header("Content-Type:application/json");

        echo json_encode([
            "complete"=> !empty($user['phone'])&&$address
        ]);
    }

    public function saveAddress(){
        if(session_status()===PHP_SESSION_NONE){
            session_start();
        }

         $userId=$_SESSION['user_id'];
         $phone=$_POST['phone'];
         $city=$_POST['city'];
         $fullAddress=$_POST['full_address'];
        
         $cartModel=new CartModel();

         $cartModel->saveUserPhone($userId, $phone);

         $cartModel->saveAddress($userId, $fullAddress, $city);
         header("Content-Type: application/json");

         echo json_encode([
            "success"=> true
         ]);
    }

public function placeOrder(){
    if(session_status() === PHP_SESSION_NONE){
        session_start();
    }

    header("Content-Type: application/json");

    $userId = $_SESSION['user_id'];

    $data = json_decode(file_get_contents("php://input"), true);
    $selectedIds = isset($data['selected_ids']) ? $data['selected_ids'] : [];

    if(empty($selectedIds)){
        echo json_encode(["success" => false, "message" => "Vui lòng chọn ít nhất một sản phẩm"]);
        exit;
    }

    $selectedIds = array_map('intval', $selectedIds);

    $cartModel = new CartModel();
    $cart = $cartModel->getCartByUser($userId);
    $allItems = $cartModel->getCartItems($cart['cart_id']);

    $items = array_filter($allItems, function($item) use ($selectedIds){
        return in_array((int)$item['cart_item_id'], $selectedIds);
    });

    if(empty($items)){
        echo json_encode(["success" => false, "message" => "Không tìm thấy sản phẩm đã chọn"]);
        exit;
    }

    // KIỂM TRA TỒN KHO trước khi đặt
    foreach($items as $item){
        $stock = $cartModel->getStockByVariant($item['variant_id']);

        if($item['quantity'] > $stock){
            echo json_encode([
                "success"  => false,
                "message"  => "Sản phẩm \"{$item['product_name']}\" chỉ còn $stock trong kho, bạn đang chọn {$item['quantity']}"
            ]);
            exit;
        }
    }

    $address = $cartModel->getDefaultAddress($userId);

    $total = 0;
    foreach($items as $item){
        $total += $item['price_snapshot'] * $item['quantity'];
    }

    require_once __DIR__ . '/../models/OrderModel.php';
    $orderModel = new OrderModel();

    $orderId = $orderModel->createOrder($userId, $address['address_id'], $total);

    foreach($items as $item){
        $orderModel->addOrderItem(
            $orderId,
            $item['product_id'],
            $item['variant_id'],
            $item['quantity'],
            $item['price_snapshot']
        );

        // TRỪ TỒN KHO sau khi thêm vào đơn hàng
        $cartModel->reduceStock($item['variant_id'], $item['quantity']);
    }

    foreach($selectedIds as $itemId){
        $cartModel->deleteItem($itemId, $cart['cart_id']);
    }

    echo json_encode(["success" => true, "order_id" => $orderId]);
}
}   

?>