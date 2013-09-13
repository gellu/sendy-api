<?php
/**
 * Created by: gellu
 * Date: 13.09.2013 13:52
 */

$app->group('/lists', function() use ($app, $db) {

	$app->get('/get', function() use ($app, $db) {

		if(!$app->request()->get('name'))
		{
			echo json_encode(array('status' => 'error', 'result' => 'Parameter [name] is missing'));
			$app->stop();
		}

		$sth = $db->prepare('SELECT * FROM lists WHERE name LIKE :name');
		$sth->execute(array('name' => '%' . $app->request()->get('name') .'%'));
		$res = $sth->fetchAll(PDO::FETCH_ASSOC);

		echo json_encode(array('status' => 'ok', 'result' => $res));

	});

});