<?php

require_once __DIR__ . '/vendor/autoload.php';

session_start();


use App\Controllers\ApiController;

$api = new ApiController();

$api->run();
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['data']) && !empty($data['data'])) {
    $api->execPOST($data['data']);
}