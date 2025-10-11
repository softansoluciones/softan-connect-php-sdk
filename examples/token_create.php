<?php
require __DIR__ . '/../vendor/autoload.php';
use SoftanConnect\Services;

$payload = ['user_id' => 12345];
$res = Services::requestToken($payload);
echo json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\n";