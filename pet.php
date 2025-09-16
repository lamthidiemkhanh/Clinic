<?php
// MVC proxy for backward compatibility
require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/controllers/Api/PetController.php';
(new Api_PetController())->handle();

