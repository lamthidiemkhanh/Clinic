<?php
require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/controllers/Api/ServiceController.php';
(new Api_ServiceController())->handle();

