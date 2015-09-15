<?php
require 'vendor/autoload.php';




\Slim\Slim::registerAutoloader();

$logWriter = new \Slim\LogWriter(fopen(__DIR__ . '/logs/log-'.date('Y-m-d', time()), 'a'));
$app = new \Slim\Slim(array('log.writer' => $logWriter));


// Dependency Injection Containers
// -----------------------------------------------------------------------------
// In our unit tests, we'll mock these so that we can control our application
// state.

require_once __DIR__ . '/app/app.php';

$app->run();
