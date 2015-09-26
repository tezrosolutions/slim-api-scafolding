<?php
//turn all reporting on
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';
require 'config/hubspot.php';
require 'config/genius.php';




\Slim\Slim::registerAutoloader();

if($_SERVER['HTTP_HOST'] == "api-1800approved.rhcloud.com") {
	$logWriter = new \Slim\LogWriter(fopen(__DIR__ . '/logs-os', 'a'));
} else {
	$logWriter = new \Slim\LogWriter(fopen(__DIR__ . '/logs/log-'.date('Y-m-d', time()), 'a'));
}
$customConfig = array();

$customConfig['hubspot'] = array();
$customConfig['hubspot']['config'] = $hubspotConfig;

$customConfig['genius'] = array();
$customConfig['genius']['coplCodes'] = $coplCodes;
$customConfig['genius']['config'] = $geniusConfig;


$app = new \Slim\Slim(array('log.writer' => $logWriter, 'custom' => $customConfig ));



// Dependency Injection Containers
// -----------------------------------------------------------------------------
// In our unit tests, we'll mock these so that we can control our application
// state.

require_once __DIR__ . '/app/app.php';

$app->run();
