<?php
/**
 * Routes campagne manipulation - 'campagne' table concerned
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
 * Get all campagne
 * url - /campagnes
 * method - GET
 */
$app->get('/campagnes', 'authenticate', function() use ($app, $db, $logManager) {
    $campagnes = $db->entityManager->campagne();
    $campagnes_array = JSON::parseNotormObjectToArray($campagnes);
    global $user_connected;

    if(count($campagnes_array) > 0)
    {
        $data_campagnes = array();
        foreach ($campagnes as $campagne) array_push($data_campagnes, $campagne);

        $logManager->setLog($user_connected, (string)$campagnes, false);
        echoResponse(200, true, "Tous les campagnes retournés", $data_campagnes);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$campagnes, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one campagne by id
* url - /campagnes/:id
* method - GET
*/
$app->get('/campagnes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $campagne = $db->entityManager->campagne[$id];
    global $user_connected;

    if(count($campagne) > 0)
    {
        $logManager->setLog($user_connected, (string)$campagne, false);
        echoResponse(200, true, "campagne retourné(e)", $campagne);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$campagne, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new campagne
* url - /campagnes/
* method - POST
* @params name
*/
$app->post('/campagnes', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('titre','date_debut','date_fin','nb_tour','tour_actuel')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_campagne = $request_params["name"];

    $data = array(
        "name" => $name_campagne
    );

    $insert_campagne = $db->entityManager->campagne()->insert($data);

    if($insert_campagne == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("campagne", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du campagne", NULL);
    }
    else
    if($insert_campagne != FALSE || is_array($insert_campagne))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("campagne", $data), false);
        echoResponse(201, true, "campagne ajouté(e) avec succès", $insert_campagne);
    }
});

/**
* Update one campagne
* url - /campagnes/:id
* method - PUT
* @params name
*/
$app->put('/campagnes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('titre','date_debut','date_fin','nb_tour','tour_actuel')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_campagne = $request_params["name"];

    $campagne = $db->entityManager->campagne[$id];
    if($campagne)
    {
        $testSameData = isSameData($campagne, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$campagne, false);
            echoResponse(200, true, "campagne mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_campagne = $campagne->update(array("name" => $name_campagne));

            if($update_campagne == FALSE)
            {
                $logManager->setLog($user_connected, (string)$campagne, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du campagne", NULL);
            }
            else
            if($update_campagne != FALSE || is_array($update_campagne))
            {
                $logManager->setLog($user_connected, (string)$campagne, false);
                echoResponse(201, true, "campagne mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$campagne, true);
        echoResponse(400, false, "campagne inexistant !!", NULL);
    }

});

/**
* Delete one campagne
* url - /campagnes/:id
* method - DELETE
* @params name
*/
$app->delete('/campagnes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $campagne = $db->entityManager->campagne[$id];
    global $user_connected;

    if($db->entityManager->application_campagne("campagne_id", $id)->delete())
    {
        if($campagne && $campagne->delete())
        {
            $logManager->setLog($user_connected, (string)$campagne, false);
            echoResponse(200, true, "campagne id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$campagne, true);
            echoResponse(200, false, "campagne id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$campagne, true);
        echoResponse(400, false, "Erreur lors de la suppression de la campagne ayant l'id $id : campagne inexistant !!", NULL);
    }
});