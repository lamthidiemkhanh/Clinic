<?php
function view(string $path, array $vars = []): void {
    $full = __DIR__ . '/../views/' . $path . '.php';
    if (!is_file($full)) { echo 'View not found'; return; }
    extract($vars, EXTR_SKIP);
    ob_start();
    include $full;
    $content = ob_get_clean();
    include __DIR__ . '/../views/layout.php';
}

function first_char(string $s): string {
    if ($s === '') return '';
    if (function_exists('mb_substr')) return mb_substr($s, 0, 1, 'UTF-8');
    return substr($s, 0, 1);
}
