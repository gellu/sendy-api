<?php
/**
 * Created by: gellu
 * Date: 12.09.2013 15:58
 */

$app->group('/subscribers', function() use ($app, $db){

	/**
	 * GET subscribers list by email and/or list(id)
	 */
	$app->get('/get', function() use($app, $db) {

		$get = $app->request()->get();

		if(!$get['email'] && !$get['list'])
		{
			echo json_encode(array('status' => 'error', 'result' => 'Expected parameters not found'));
			$app->stop();
		}

		$where  = [];
		$params = [];

		if ($get['email'])
		{
			$where[] = 'email = :email';
			$params['email'] = $get['email'];
		}
		if ($get['list'])
		{
			$where[] = 'list = :list';
			$params['list'] = $get['list'];
		}

		$sth = $db->prepare('SELECT * FROM subscribers WHERE '. implode(' AND ', $where));
		$sth->execute($params);
		$res = $sth->fetchAll(PDO::FETCH_ASSOC);

		echo json_encode(array('status' => 'ok', 'result' => $res));

	});

	$app->get('/delete', function() use ($app, $db) {

		$get = $app->request()->get();

	});

});

