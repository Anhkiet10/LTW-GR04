<?php
require_once __DIR__ . '/../../core/Controller.php';

class AdminController extends Controller {

    public function home() {
        $this->render('admin/home', [
            'pageTitle' => 'Admin Dashboard',
        ]);
    }
}
?>
