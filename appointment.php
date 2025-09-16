<?php
// MVC proxy for backward compatibility (same behavior as Api_AppointmentsController)
require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/controllers/Api/AppointmentsController.php';
(new Api_AppointmentsController())->handle();

