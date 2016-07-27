<?php
/**
 * Routes circonscription manipulation - 'circonscription' table concerned
 * ----------- METHOD with authentification ----------
 */

include_once dirname(__DIR__)  . '/includes/functions/set_headers.php';

require_once dirname(__DIR__)  . '/includes/functions/utils.php';
require_once dirname(__DIR__)  . '/includes/functions/json.php';
require_once dirname(__DIR__)  . '/includes/functions/security_api.php';
require_once dirname(__DIR__)  . '/includes/db_manager/dbManager.php';
require_once dirname(__DIR__)  . '/includes/pass_hash.php';
require_once dirname(__DIR__)  . '/includes/Log.class.php';

global $app;
$db = new DBManager();
$logManager = new Log();

/**
 * Get all circonscription
 * url - /circonscriptions
 * method - GET
 */
$app->get('/circonscriptions', 'authenticate', function() use ($app, $db, $logManager) {
    $circonscriptions = $db->entityManager->circonscription();
    $circonscriptions_array = JSON::parseNotormObjectToArray($circonscriptions);
    global $user_connected;

    if(count($circonscriptions_array) > 0)
    {
        $data_circonscriptions = array();
        foreach ($circonscriptions as $circonscription) array_push($data_circonscriptions, $circonscription);

        $logManager->setLog($user_connected, (string)$circonscriptions, false);
        echoResponse(200, true, "Tous les circonscriptions retournés", $data_circonscriptions);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$circonscriptions, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one circonscription by id
* url - /circonscriptions/:id
* method - GET
*/
$app->get('/circonscriptions/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $circonscription = $db->entityManager->circonscription[$id];
    global $user_connected;

    if(count($circonscription) > 0)
    {
        $logManager->setLog($user_connected, (string)$circonscription, false);
        echoResponse(200, true, "circonscription retourné(e)", $circonscription);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$circonscription, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new circonscription
* url - /circonscriptions/
* method - POST
* @params name
*/
$app->post('/circonscriptions', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_circonscription = $request_params["name"];

    $data = array(
        "name" => $name_circonscription
    );

    $insert_circonscription = $db->entityManager->circonscription()->insert($data);

    if($insert_circonscription == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("circonscription", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du circonscription", NULL);
    }
    else
    if($insert_circonscription != FALSE || is_array($insert_circonscription))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("circonscription", $data), false);
        echoResponse(201, true, "circonscription ajouté(e) avec succès", $insert_circonscription);
    }
});

/**
* Update one circonscription
* url - /circonscriptions/:id
* method - PUT
* @params name
*/
$app->put('/circonscriptions/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_circonscription = $request_params["name"];

    $circonscription = $db->entityManager->circonscription[$id];
    if($circonscription)
    {
        $testSameData = isSameData($circonscription, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$circonscription, false);
            echoResponse(200, true, "circonscription mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_circonscription = $circonscription->update(array("name" => $name_circonscription));

            if($update_circonscription == FALSE)
            {
                $logManager->setLog($user_connected, (string)$circonscription, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du circonscription", NULL);
            }
            else
            if($update_circonscription != FALSE || is_array($update_circonscription))
            {
                $logManager->setLog($user_connected, (string)$circonscription, false);
                echoResponse(201, true, "circonscription mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$circonscription, true);
        echoResponse(400, false, "circonscription inexistant !!", NULL);
    }

});

/**
* Delete one circonscription
* url - /circonscriptions/:id
* method - DELETE
* @params name
*/
$app->delete('/circonscriptions/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $circonscription = $db->entityManager->circonscription[$id];
    global $user_connected;

    if($db->entityManager->application_circonscription("circonscription_id", $id)->delete())
    {
        if($circonscription && $circonscription->delete())
        {
            $logManager->setLog($user_connected, (string)$circonscription, false);
            echoResponse(200, true, "circonscription id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$circonscription, true);
            echoResponse(200, false, "circonscription id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$circonscription, true);
        echoResponse(400, false, "Erreur lors de la suppression de la circonscription ayant l'id $id : circonscription inexistant !!", NULL);
    }
});