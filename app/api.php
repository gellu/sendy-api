<?php
/**
 * Created by: gellu
 * Date: 12.09.2013 15:55
 */

require '../vendor/autoload.php';
$config = require 'config.php';

$app = new \Slim\Slim($config['slim']);

require '../src/Middleware.php';
$app->add(new \APIResponseMiddleware());

try {
	$db = new PDO('mysql:dbname='. $config['pdo']['name'] .';host='. $config['pdo']['host'], $config['pdo']['user'], $config['pdo']['password']);
} catch (PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
}

require '../src/Subscribers.php';

$app->run();

