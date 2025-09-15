<?php
class AdminController {
    public function index(): void {
        view('admin/index', [ 'title' => 'Trang chủ Admin', 'pageId' => 'admin-page' ]);
    }
}
