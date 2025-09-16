<?php
// MVC proxy for backward compatibility
require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/controllers/Api/PetTypeServiceController.php';
(new Api_PetTypeServiceController())->handle();

