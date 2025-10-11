<?php
require __DIR__ . '/../vendor/autoload.php';
use SoftanConnect\Services;

$payload = ['otp_code' => 123456, 'user_id' => 12345];
$res = Services::validateOtp($payload);
echo json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),"\n";