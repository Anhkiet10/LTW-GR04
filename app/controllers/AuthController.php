<?php
require_once __DIR__ . '/../../core/Controller.php';

class AuthController extends Controller {
    public function loginPage() {
        $this->render('auth/login');
    }
}
?>
