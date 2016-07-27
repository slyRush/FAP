<?php
/**
 * Routes departement manipulation - 'departement' table concerned
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
 * Get all departement
 * url - /departements
 * method - GET
 */
$app->get('/departements', 'authenticate', function() use ($app, $db, $logManager) {
    $departements = $db->entityManager->departement();
    $departements_array = JSON::parseNotormObjectToArray($departements);
    global $user_connected;

    if(count($departements_array) > 0)
    {
        $data_departements = array();
        foreach ($departements as $departement) array_push($data_departements, $departement);

        $logManager->setLog($user_connected, (string)$departements, false);
        echoResponse(200, true, "Tous les departements retourn�s", $data_departements);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$departements, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one departement by id
* url - /departements/:id
* method - GET
*/
$app->get('/departements/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $departement = $db->entityManager->departement[$id];
    global $user_connected;

    if(count($departement) > 0)
    {
        $logManager->setLog($user_connected, (string)$departement, false);
        echoResponse(200, true, "departement retourn�(e)", $departement);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$departement, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new departement
* url - /departements/
* method - POST
* @params name
*/
$app->post('/departements', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('code','nom')); // v�rifier les param�dtres requises
    global $user_connected;

    //recup�rer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_departement = $request_params["name"];

    $data = array(
        "name" => $name_departement
    );

    $insert_departement = $db->entityManager->departement()->insert($data);

    if($insert_departement == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("departement", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du departement", NULL);
    }
    else
    if($insert_departement != FALSE || is_array($insert_departement))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("departement", $data), false);
        echoResponse(201, true, "departement ajout�(e) avec succ�s", $insert_departement);
    }
});

/**
* Update one departement
* url - /departements/:id
* method - PUT
* @params name
*/
$app->put('/departements/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('code','nom')); // v�rifier les param�tres requises
    global $user_connected;

    //recup�rer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_departement = $request_params["name"];

    $departement = $db->entityManager->departement[$id];
    if($departement)
    {
        $testSameData = isSameData($departement, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la m�me data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$departement, false);
            echoResponse(200, true, "departement mis � jour avec succ�s. Id : $id", NULL);
        }
        else
        {
            $update_departement = $departement->update(array("name" => $name_departement));

            if($update_departement == FALSE)
            {
                $logManager->setLog($user_connected, (string)$departement, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise � jour du departement", NULL);
            }
            else
            if($update_departement != FALSE || is_array($update_departement))
            {
                $logManager->setLog($user_connected, (string)$departement, false);
                echoResponse(201, true, "departement mis � jour avec succ�s", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$departement, true);
        echoResponse(400, false, "departement inexistant !!", NULL);
    }

});

/**
* Delete one departement
* url - /departements/:id
* method - DELETE
* @params name
*/
$app->delete('/departements/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $departement = $db->entityManager->departement[$id];
    global $user_connected;

    if($db->entityManager->application_departement("departement_id", $id)->delete())
    {
        if($departement && $departement->delete())
        {
            $logManager->setLog($user_connected, (string)$departement, false);
            echoResponse(200, true, "departement id : $id supprim� avec succ�s", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$departement, true);
            echoResponse(200, false, "departement id : $id pas supprim�. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$departement, true);
        echoResponse(400, false, "Erreur lors de la suppression de la departement ayant l'id $id : departement inexistant !!", NULL);
    }
});