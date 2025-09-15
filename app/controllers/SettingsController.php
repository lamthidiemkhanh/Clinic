<?php
class SettingsController {
    public function index(): void {
        view('settings/index', [ 'title' => 'Cài đặt', 'pageId' => 'settings-page' ]);
    }
}
