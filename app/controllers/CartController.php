<?php
require_once __DIR__ . '/../../core/Controller.php';

class CartController extends Controller {
    public function index() {
        // Shopping cart placeholder
        $this->render('cart/index');
    }
}
?>
