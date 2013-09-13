<?php
/**
 * Created by: gellu
 * Date: 12.09.2013 15:55
 */

require '../vendor/autoload.php';
$config = require 'config.php';

$app = new \Slim\Slim($config['slim']);

try {
	$db = new PDO('mysql:dbname='. $config['pdo']['name'] .';host='. $config['pdo']['host'], $config['pdo']['user'], $config['pdo']['password']);
} catch (PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
}

require '../src/Middleware.php';

// Authorize API call with app_key
$app->add(new \APIAuthMiddleware($db));
// Send proper headers for response
$app->add(new \APIResponseMiddleware());

require '../src/Subscribers.php';
require '../src/Lists.php';

$app->run();

