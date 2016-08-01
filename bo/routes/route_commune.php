<?php
/**
 * Routes commune manipulation - 'commune' table concerned
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
 * Get all commune
 * url - /communes
 * method - GET
 */
$app->get('/communes', 'authenticate', function() use ($app, $db, $logManager) {
    $communes = $db->entityManager->commune();
    $communes_array = JSON::parseNotormObjectToArray($communes);
    global $user_connected;

    if(count($communes_array) > 0)
    {
        $data_communes = array();
        foreach ($communes as $commune) array_push($data_communes, $commune);

        $logManager->setLog($user_connected, (string)$communes, false);
        echoResponse(200, true, "Tous les communes retournés", $data_communes);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$communes, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one commune by id
* url - /communes/:id
* method - GET
*/
$app->get('/communes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $commune = $db->entityManager->commune[$id];
    global $user_connected;

    if(count($commune) > 0)
    {
        $logManager->setLog($user_connected, (string)$commune, false);
        echoResponse(200, true, "commune retourné(e)", $commune);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$commune, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new commune
* url - /communes/
* method - POST
* @params name
*/
$app->post('/communes', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_commune = $request_params["name"];

    $data = array(
        "name" => $name_commune
    );

    $insert_commune = $db->entityManager->commune()->insert($data);

    if($insert_commune == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("commune", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du commune", NULL);
    }
    else
    if($insert_commune != FALSE || is_array($insert_commune))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("commune", $data), false);
        echoResponse(201, true, "commune ajouté(e) avec succès", $insert_commune);
    }
});

/**
* Update one commune
* url - /communes/:id
* method - PUT
* @params name
*/
$app->put('/communes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_commune = $request_params["name"];

    $commune = $db->entityManager->commune[$id];
    if($commune)
    {
        $testSameData = isSameData($commune, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$commune, false);
            echoResponse(200, true, "commune mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_commune = $commune->update(array("name" => $name_commune));

            if($update_commune == FALSE)
            {
                $logManager->setLog($user_connected, (string)$commune, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du commune", NULL);
            }
            else
            if($update_commune != FALSE || is_array($update_commune))
            {
                $logManager->setLog($user_connected, (string)$commune, false);
                echoResponse(201, true, "commune mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$commune, true);
        echoResponse(400, false, "commune inexistant !!", NULL);
    }

});

/**
* Delete one commune
* url - /communes/:id
* method - DELETE
* @params name
*/
$app->delete('/communes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $commune = $db->entityManager->commune[$id];
    global $user_connected;

    if($db->entityManager->application_commune("commune_id", $id)->delete())
    {
        if($commune && $commune->delete())
        {
            $logManager->setLog($user_connected, (string)$commune, false);
            echoResponse(200, true, "commune id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$commune, true);
            echoResponse(200, false, "commune id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$commune, true);
        echoResponse(400, false, "Erreur lors de la suppression de la commune ayant l'id $id : commune inexistant !!", NULL);
    }
});