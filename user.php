<?php
// MVC proxy for backward compatibility
require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/controllers/Api/UserController.php';
(new Api_UserController())->handle();

