<?php
/**
 * Created by: Synergi
 * Date: 25.09.2014 15:26 AEST
 * 
 */

$app->group('/campaigns', function() use ($app, $db) {

    $app->get('/get', function() use ($app, $db) {

        if(!$app->request()->get('id'))
        {
            echo json_encode(array('status' => 'error', 'result' => 'Parameter [id] is missing'));
            $app->stop();
        }

        $sth = $db->prepare('SELECT * FROM campaigns WHERE id = :id');
        $sth->execute(array('id' => $app->request()->get('id')));
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(array('status' => 'ok', 'result' => $res));

    });

    $app->get('/show', function() use ($app, $db) {

        if(!$app->request()->get('app_key'))
        {
            echo json_encode(array('status' => 'error', 'result' => 'Parameter [app_key] is missing'));
            $app->stop();
        }

        //get app_key
        $sth = $db->prepare('SELECT id FROM apps WHERE app_key = :app_key');
        $sth->execute(array('app_key' => $app->request()->get('app_key')));
        $app = $sth->fetchAll(PDO::FETCH_ASSOC);

        //Get Campaigns
        $sth = $db->prepare('SELECT * FROM campaigns WHERE app = :id');
        $sth->execute(array('id' => $app[0]['id']));
        $res = $sth->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(array('status' => 'ok', 'result' => $res));

    });

});