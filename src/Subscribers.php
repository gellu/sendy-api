<?php
/**
 * Created by: gellu    
 * Date: 12.09.2013 15:58
 * Modified: 29.09.2014 16:53 AEST By: Synergi
 */

$app->group('/subscribers', function() use ($app, $db){

    $app->post('/user/add', function() use ($app, $db) {

        $post = $app->request->post();

        if(!$post['email'] || !$post['list'] )
        {
            echo json_encode(array('status' => 'error', 'result' => 'Some parameters are missing'));
            $app->stop();
        }

        if(!isset($post['name']))
        {
            $post['name'] = '';
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

        //check if user exists in db
        $sth = $db->prepare('SELECT * FROM subscribers WHERE email = :email');
        $sth->execute(array('email' => $post['email']));
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);

        //If exists use current userID else create one on count+1
        $userID = (count($res) >= 1)? $res[0]['userID']: count($res)+1;

        $sth = $db->prepare('INSERT INTO subscribers SET userID = :userID, name = :name, email = :email, list = :list, unsubscribed = :unsubscribed, `timestamp` = :timestamp');
        $sth->execute(array('userID'		=> $userID,
                            'name'			=> $post['name'],
                            'email' 		=> $post['email'],
                            'list' 			=> $post['list'],
                            'unsubscribed' 	=> (isset($post['unsubscribed']) && $post['unsubscribed']) == 1 ? 1 : 0,
                            'timestamp' 	=> time()));

        echo json_encode(array('status' => 'ok', 'result' => $sth->rowCount()));

    });

    $app->post('/user/subscribe', function() use ($app, $db) {

        $post = $app->request->post();

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

        if(count($sth->fetchAll(PDO::FETCH_ASSOC)) <= 0)
        {
            echo json_encode(array('status' => 'error', 'result' => 'User not on the list'));
            $app->stop();
        }

        //Don't worry about current state, just update
        $sth = $db->prepare('UPDATE subscribers SET `unsubscribed` = "0", `timestamp` = :timestamp WHERE list = :list AND email = :email');
        $sth->execute(array('list' => $post['list'], 'email' => $post['email'], 'timestamp' 	=> time()));

        echo json_encode(array('status' => 'ok', 'result' => $sth->rowCount()));

    });

    $app->post('/user/unsubscribe', function() use ($app, $db) {

        $post = $app->request->post();
        $count = 0;

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
        
        // Unsubscribe from all lists
        if($list['unsubscribe_all_list'] === '1')
        {
            //check if user exists in db
            $sth = $db->prepare('SELECT * FROM subscribers WHERE email = :email');
            $sth->execute(array('email' => $post['email']));
            $subscribers = $sth->fetchAll(PDO::FETCH_ASSOC);
            foreach ($subscribers as $subscriber) 
            {
                $sth = $db->prepare('UPDATE subscribers SET `unsubscribed` = "1", `timestamp` = :timestamp WHERE id = :id');
                $sth->execute(array('id' => $subscriber['id'], 'timestamp' => time()));
            }
            $count = count($subscribers);
        }
        else
        {
            $sth = $db->prepare('SELECT * FROM subscribers WHERE list = :list AND email = :email');
            $sth->execute(array('list' => $post['list'], 'email' => $post['email']));

            if(count($sth->fetchAll(PDO::FETCH_ASSOC)) > 0)
            {
                //Don't worry about current state, just update
                $sth = $db->prepare('UPDATE subscribers SET `unsubscribed` = "1", `timestamp` = :timestamp WHERE list = :list AND email = :email');
                $sth->execute(array('list' => $post['list'], 'email' => $post['email'], 'timestamp' => time()));
                $count = $sth->rowCount();
            }
        }
        echo json_encode(array('status' => 'ok', 'result' => $count));
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

        $app->get('/unsubscribed', function() use ($app, $db) {

            $sth = $db->prepare('SELECT email FROM subscribers WHERE unsubscribed = "1"');
            $sth->execute();
            $res = $sth->fetchAll(PDO::FETCH_COLUMN, 0);

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

        $app->get('/user/status', function() use ($app, $db) {

            if(!$app->request()->get('email'))
            {
                echo json_encode(array('status' => 'error', 'result' => 'Parameter [email] is missing'));
                $app->stop();
            }

            if(!$app->request()->get('list'))
            {
                echo json_encode(array('status' => 'error', 'result' => 'Parameter [list] is missing'));
                $app->stop();
            }

            $sth = $db->prepare('SELECT * FROM subscribers WHERE list = :list AND email = :email');
            $sth->execute(array('email' => $app->request()->get('email'), 'list' => $app->request()->get('list')));
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);

            if($res[0])
            {
                $status = $res[0]['unsubscribed']; // 1 = unsubscribed , 0 = subscribed
                echo json_encode(array('status' => 'ok', 'result' => $status));
            } else
            {
                //Not in list
                echo json_encode(array('status' => 'ok', 'result' => "2"));
            }

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
