<?php
require 'vendor/autoload.php';




\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();


// Dependency Injection Containers
// -----------------------------------------------------------------------------
// In our unit tests, we'll mock these so that we can control our application
// state.

require_once __DIR__ . '/app/app.php';

$app->run();
