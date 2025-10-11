<?php
require __DIR__ . '/../vendor/autoload.php';
use SoftanConnect\Services;

$res = Services::validateCredentials();
echo json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\n";