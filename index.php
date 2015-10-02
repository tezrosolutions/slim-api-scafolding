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

$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "path" => "/hello",
    "realm" => "Protected",
    "relaxed" => array("dev.1800approved.com.au", "localhost"),
    "users" => [
        "root" => "r0Ot_C0n643",
        "genius" => "gEn1u5_C0n",
        "hubspot" => "hUb5p0t_C0n",
        "contactspace" => "c0nTa9t5Pac3"
    ]
]));


// Dependency Injection Containers
// -----------------------------------------------------------------------------
// In our unit tests, we'll mock these so that we can control our application
// state.

require_once __DIR__ . '/app/app.php';

$app->run();
