<?php
// MVC proxy for backward compatibility
require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/controllers/Api/AnimalBreedController.php';
(new Api_AnimalBreedController())->handle();

