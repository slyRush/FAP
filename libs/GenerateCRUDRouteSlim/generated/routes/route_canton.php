<?php
/**
 * Routes canton manipulation - 'canton' table concerned
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
 * Get all canton
 * url - /cantons
 * method - GET
 */
$app->get('/cantons', 'authenticate', function() use ($app, $db, $logManager) {
    $cantons = $db->entityManager->canton();
    $cantons_array = JSON::parseNotormObjectToArray($cantons);
    global $user_connected;

    if(count($cantons_array) > 0)
    {
        $data_cantons = array();
        foreach ($cantons as $canton) array_push($data_cantons, $canton);

        $logManager->setLog($user_connected, (string)$cantons, false);
        echoResponse(200, true, "Tous les cantons retourn�s", $data_cantons);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$cantons, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one canton by id
* url - /cantons/:id
* method - GET
*/
$app->get('/cantons/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $canton = $db->entityManager->canton[$id];
    global $user_connected;

    if(count($canton) > 0)
    {
        $logManager->setLog($user_connected, (string)$canton, false);
        echoResponse(200, true, "canton retourn�(e)", $canton);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$canton, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new canton
* url - /cantons/
* method - POST
* @params name
*/
$app->post('/cantons', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // v�rifier les param�dtres requises
    global $user_connected;

    //recup�rer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_canton = $request_params["name"];

    $data = array(
        "name" => $name_canton
    );

    $insert_canton = $db->entityManager->canton()->insert($data);

    if($insert_canton == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("canton", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du canton", NULL);
    }
    else
    if($insert_canton != FALSE || is_array($insert_canton))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("canton", $data), false);
        echoResponse(201, true, "canton ajout�(e) avec succ�s", $insert_canton);
    }
});

/**
* Update one canton
* url - /cantons/:id
* method - PUT
* @params name
*/
$app->put('/cantons/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // v�rifier les param�tres requises
    global $user_connected;

    //recup�rer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_canton = $request_params["name"];

    $canton = $db->entityManager->canton[$id];
    if($canton)
    {
        $testSameData = isSameData($canton, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la m�me data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$canton, false);
            echoResponse(200, true, "canton mis � jour avec succ�s. Id : $id", NULL);
        }
        else
        {
            $update_canton = $canton->update(array("name" => $name_canton));

            if($update_canton == FALSE)
            {
                $logManager->setLog($user_connected, (string)$canton, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise � jour du canton", NULL);
            }
            else
            if($update_canton != FALSE || is_array($update_canton))
            {
                $logManager->setLog($user_connected, (string)$canton, false);
                echoResponse(201, true, "canton mis � jour avec succ�s", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$canton, true);
        echoResponse(400, false, "canton inexistant !!", NULL);
    }

});

/**
* Delete one canton
* url - /cantons/:id
* method - DELETE
* @params name
*/
$app->delete('/cantons/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $canton = $db->entityManager->canton[$id];
    global $user_connected;

    if($db->entityManager->application_canton("canton_id", $id)->delete())
    {
        if($canton && $canton->delete())
        {
            $logManager->setLog($user_connected, (string)$canton, false);
            echoResponse(200, true, "canton id : $id supprim� avec succ�s", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$canton, true);
            echoResponse(200, false, "canton id : $id pas supprim�. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$canton, true);
        echoResponse(400, false, "Erreur lors de la suppression de la canton ayant l'id $id : canton inexistant !!", NULL);
    }
});