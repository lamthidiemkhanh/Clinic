<?php
// Compatibility proxy: delegate to MVC API controller
require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/controllers/Api/ClinicController.php';
(new Api_ClinicController())->handle();

