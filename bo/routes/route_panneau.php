<?php
/**
 * Routes panneau manipulation - 'panneau' table concerned
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
 * Get all panneau
 * url - /panneaus
 * method - GET
 */
$app->get('/panneaus', 'authenticate', function() use ($app, $db, $logManager) {
    $panneaus = $db->entityManager->panneau();
    $panneaus_array = JSON::parseNotormObjectToArray($panneaus);
    global $user_connected;

    if(count($panneaus_array) > 0)
    {
        $data_panneaus = array();
        foreach ($panneaus as $panneau) array_push($data_panneaus, $panneau);

        $logManager->setLog($user_connected, (string)$panneaus, false);
        echoResponse(200, true, "Tous les panneaus retournés", $data_panneaus);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$panneaus, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one panneau by id
* url - /panneaus/:id
* method - GET
*/
$app->get('/panneaus/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $panneau = $db->entityManager->panneau[$id];
    global $user_connected;

    if(count($panneau) > 0)
    {
        $logManager->setLog($user_connected, (string)$panneau, false);
        echoResponse(200, true, "panneau retourné(e)", $panneau);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$panneau, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new panneau
* url - /panneaus/
* method - POST
* @params name
*/
$app->post('/panneaus', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('latitude','longitude','prix','commentaire','num_emplacement','numero','code_postal','adresse_id')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_panneau = $request_params["name"];

    $data = array(
        "name" => $name_panneau
    );

    $insert_panneau = $db->entityManager->panneau()->insert($data);

    if($insert_panneau == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("panneau", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du panneau", NULL);
    }
    else
    if($insert_panneau != FALSE || is_array($insert_panneau))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("panneau", $data), false);
        echoResponse(201, true, "panneau ajouté(e) avec succès", $insert_panneau);
    }
});

/**
* Update one panneau
* url - /panneaus/:id
* method - PUT
* @params name
*/
$app->put('/panneaus/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('latitude','longitude','prix','commentaire','num_emplacement','numero','code_postal','adresse_id')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_panneau = $request_params["name"];

    $panneau = $db->entityManager->panneau[$id];
    if($panneau)
    {
        $testSameData = isSameData($panneau, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$panneau, false);
            echoResponse(200, true, "panneau mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_panneau = $panneau->update(array("name" => $name_panneau));

            if($update_panneau == FALSE)
            {
                $logManager->setLog($user_connected, (string)$panneau, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du panneau", NULL);
            }
            else
            if($update_panneau != FALSE || is_array($update_panneau))
            {
                $logManager->setLog($user_connected, (string)$panneau, false);
                echoResponse(201, true, "panneau mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$panneau, true);
        echoResponse(400, false, "panneau inexistant !!", NULL);
    }

});

/**
* Delete one panneau
* url - /panneaus/:id
* method - DELETE
* @params name
*/
$app->delete('/panneaus/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $panneau = $db->entityManager->panneau[$id];
    global $user_connected;

    if($db->entityManager->application_panneau("panneau_id", $id)->delete())
    {
        if($panneau && $panneau->delete())
        {
            $logManager->setLog($user_connected, (string)$panneau, false);
            echoResponse(200, true, "panneau id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$panneau, true);
            echoResponse(200, false, "panneau id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$panneau, true);
        echoResponse(400, false, "Erreur lors de la suppression de la panneau ayant l'id $id : panneau inexistant !!", NULL);
    }
});