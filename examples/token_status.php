<?php
require __DIR__ . '/../vendor/autoload.php';
use SoftanConnect\Services;

$payload = ['token' => '<TOKEN>', 'user_id' => 12345];
$res = Services::tokenStatus($payload);
echo json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\n";