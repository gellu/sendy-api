<?php
/**
 * Created by: gellu
 * Date: 12.09.2013 15:58
 */

$app->group('/subscribers', function() use ($app, $db){

	$app->post('/add/user', function() use ($app, $db) {

		$post = $app->request()->post();

		if(!$post['email'] || !$post['list'])
		{
			echo json_encode(array('status' => 'error', 'result' => 'Some parameters are missing'));
			$app->stop();
		}

		$list = $db->query('SELECT * FROM lists WHERE id = '. (int) $post['list'])->fetch(PDO::FETCH_ASSOC);
		if(!$list)
		{
			echo json_encode(array('status' => 'error', 'result' => 'List not found'));
			$app->stop();
		}

		$sth = $db->prepare('SELECT * FROM subscribers WHERE list = :list AND email = :email');
		$sth->execute(array('list' => $post['list'], 'email' => $post['email']));

		if(count($sth->fetchAll(PDO::FETCH_ASSOC)) > 0)
		{
			echo json_encode(array('status' => 'error', 'result' => 'User already on the list'));
			$app->stop();
		}

		$sth = $db->prepare('INSERT INTO subscribers SET email = :email, list = :list, `timestamp` = :timestamp');
		$sth->execute(array('email' => $post['email'], 'list' => $post['list'], 'timestamp' => time()));

		echo json_encode(array('status' => 'ok', 'result' => $sth->rowCount()));

	});

	$app->group('/get', function() use ($app, $db)  {

		$app->get('/list', function() use ($app, $db) {

			if(!$app->request()->get('list'))
			{
				echo json_encode(array('status' => 'error', 'result' => 'Parameter [list] is missing'));
				$app->stop();
			}

			$sth = $db->prepare('SELECT * FROM subscribers WHERE list = :list');
			$sth->execute(array('list' => $app->request()->get('list')));
			$res = $sth->fetchAll(PDO::FETCH_ASSOC);

			echo json_encode(array('status' => 'ok', 'result' => $res));
		});

		$app->get('/user', function() use ($app, $db) {

			if(!$app->request()->get('email'))
			{
				echo json_encode(array('status' => 'error', 'result' => 'Parameter [email] is missing'));
				$app->stop();
			}

			$sth = $db->prepare('SELECT * FROM subscribers WHERE email = :email');
			$sth->execute(array('email' => $app->request()->get('email')));
			$res = $sth->fetchAll(PDO::FETCH_ASSOC);

			echo json_encode(array('status' => 'ok', 'result' => $res));
		});


	});

	$app->get('/truncate/list', function() use ($app, $db) {

		if(!$app->request()->get('list'))
		{
			echo json_encode(array('status' => 'error', 'result' => 'Parameter [list] is missing'));
			$app->stop();
		}

		$sth = $db->prepare('DELETE FROM subscribers WHERE list = :list');
		$sth->execute(array('list' => $app->request()->get('list')));

		echo json_encode(array('status' => 'ok', 'result' => $sth->rowCount()));

	});

	$app->get('/delete/user', function() use ($app, $db) {

		if(!$app->request()->get('email'))
		{
			echo json_encode(array('status' => 'error', 'result' => 'Parameter [email] is missing'));
			$app->stop();
		}

		$sth = $db->prepare('DELETE FROM subscribers WHERE email = :email');
		$sth->execute(array('email' => $app->request()->get('email')));

		echo json_encode(array('status' => 'ok', 'result' => $sth->rowCount()));

	});

});

