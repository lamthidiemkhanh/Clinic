<?php
// MVC proxy for backward compatibility
require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/controllers/Api/AnimalSpeciesController.php';
(new Api_AnimalSpeciesController())->handle();

