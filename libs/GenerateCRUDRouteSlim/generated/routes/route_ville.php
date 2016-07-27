<?php
/**
 * Routes ville manipulation - 'ville' table concerned
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
 * Get all ville
 * url - /villes
 * method - GET
 */
$app->get('/villes', 'authenticate', function() use ($app, $db, $logManager) {
    $villes = $db->entityManager->ville();
    $villes_array = JSON::parseNotormObjectToArray($villes);
    global $user_connected;

    if(count($villes_array) > 0)
    {
        $data_villes = array();
        foreach ($villes as $ville) array_push($data_villes, $ville);

        $logManager->setLog($user_connected, (string)$villes, false);
        echoResponse(200, true, "Tous les villes retourn�s", $data_villes);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$villes, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one ville by id
* url - /villes/:id
* method - GET
*/
$app->get('/villes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $ville = $db->entityManager->ville[$id];
    global $user_connected;

    if(count($ville) > 0)
    {
        $logManager->setLog($user_connected, (string)$ville, false);
        echoResponse(200, true, "ville retourn�(e)", $ville);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$ville, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new ville
* url - /villes/
* method - POST
* @params name
*/
$app->post('/villes', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // v�rifier les param�dtres requises
    global $user_connected;

    //recup�rer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_ville = $request_params["name"];

    $data = array(
        "name" => $name_ville
    );

    $insert_ville = $db->entityManager->ville()->insert($data);

    if($insert_ville == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("ville", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du ville", NULL);
    }
    else
    if($insert_ville != FALSE || is_array($insert_ville))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("ville", $data), false);
        echoResponse(201, true, "ville ajout�(e) avec succ�s", $insert_ville);
    }
});

/**
* Update one ville
* url - /villes/:id
* method - PUT
* @params name
*/
$app->put('/villes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // v�rifier les param�tres requises
    global $user_connected;

    //recup�rer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_ville = $request_params["name"];

    $ville = $db->entityManager->ville[$id];
    if($ville)
    {
        $testSameData = isSameData($ville, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la m�me data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$ville, false);
            echoResponse(200, true, "ville mis � jour avec succ�s. Id : $id", NULL);
        }
        else
        {
            $update_ville = $ville->update(array("name" => $name_ville));

            if($update_ville == FALSE)
            {
                $logManager->setLog($user_connected, (string)$ville, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise � jour du ville", NULL);
            }
            else
            if($update_ville != FALSE || is_array($update_ville))
            {
                $logManager->setLog($user_connected, (string)$ville, false);
                echoResponse(201, true, "ville mis � jour avec succ�s", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$ville, true);
        echoResponse(400, false, "ville inexistant !!", NULL);
    }

});

/**
* Delete one ville
* url - /villes/:id
* method - DELETE
* @params name
*/
$app->delete('/villes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $ville = $db->entityManager->ville[$id];
    global $user_connected;

    if($db->entityManager->application_ville("ville_id", $id)->delete())
    {
        if($ville && $ville->delete())
        {
            $logManager->setLog($user_connected, (string)$ville, false);
            echoResponse(200, true, "ville id : $id supprim� avec succ�s", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$ville, true);
            echoResponse(200, false, "ville id : $id pas supprim�. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$ville, true);
        echoResponse(400, false, "Erreur lors de la suppression de la ville ayant l'id $id : ville inexistant !!", NULL);
    }
});