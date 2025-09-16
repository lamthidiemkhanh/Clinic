<?php
require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/models/Model.php';
require __DIR__ . '/app/models/Clinic.php';
$c = new Clinic();
$rows = $c->all(10);
echo json_encode($rows, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
