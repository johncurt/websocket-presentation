<?php
// Make sure composer dependencies have been installed
require __DIR__ . '/vendor/autoload.php';

$app = new Ratchet\App('localhost', 8100);
$app->route('/', new \realTimeComm\Router());
$app->run();
