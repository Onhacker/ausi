<?php
@ini_set('display_errors', 1);
@error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

echo "OK\n";
echo "PHP_VERSION=" . PHP_VERSION . "\n";
echo "json_ext=" . (function_exists('json_encode') ? 'ON' : 'OFF') . "\n";
