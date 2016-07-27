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
 * Get all campagnes
 * url - /campagnes
 * method - GET
 */
$app->get('/campagnes', 'authenticate', function() use ($app, $db, $logManager) {
    global $user_id, $user_connected;

    $campagnes = $db->entityManager->campagne("author_id", $user_id);
    $campagnes_array = JSON::parseNotormObjectToArray($campagnes);

    if(count($campagnes_array) > 0)
    {
        $data_campagnes = array();
        foreach ($campagnes as $campagne)
        {
            $data_pays = array();
            foreach ($campagne->campagne_pays() as $campagne_pays)
            {
                array_push($data_pays, array("id" => $campagne_pays->pays["id"], "name" => $campagne_pays->pays["name"]));
            }
            $campagne = JSON::parseNotormObjectToArray($campagne); //parse campagne to array
            $campagne["payss"] = $data_pays; //add payss from array

            array_push($data_campagnes, $campagne);
        }
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
    global $user_connected;
    $campagne = $db->entityManager->campagne[$id];

    if(count($campagne) > 0)
    {
        $logManager->setLog($user_connected, (string)$campagne, false);
        echoResponse(200, true, "campagne est retourné", $campagne);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$campagne, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new campagne
* url - /campagnes
* method - POST
* @params title, web, slogan
*/
$app->post('/campagnes', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('titre','date_debut','date_fin','nb_tour','statut')); // vérifier les paramétres requises
    global $user_id, $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $request_params = insertKeyValuePairInArray($request_params, "author_id", $user_id, 0); //add key author_id to array params send to post, value equals to current $user_id
    $request_params = insertKeyValuePairInArray($request_params, "maintainer_id", $user_id, 1); //add key maintainer_id to array params send to post, value equals to current $user_id

    $insert_campagne = $db->entityManager->campagne()->insert($request_params);

    if($insert_campagne == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("campagne", $request_params), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du campagne", NULL);
    }
    else
    if($insert_campagne != FALSE || is_array($insert_campagne))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("campagne", $request_params), false);
        echoResponse(201, true, "campagne ajoutée avec succès", $insert_campagne);
    }
});

/**
* Update one campagne
* url - /campagnes/:id
* method - PUT
* @params title, web, slogan
*/
$app->put('/campagnes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('titre','date_debut','date_fin','nb_tour','statut')); // vérifier les paramétres requises
    global $user_id, $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $request_params = insertKeyValuePairInArray($request_params, "author_id", $user_id, 0); //add key author_id to array params send to post, value equals to current $user_id
    $request_params = insertKeyValuePairInArray($request_params, "maintainer_id", $user_id, 1); //add key maintainer_id to array params send to post, value equals to current $user_id

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
            $update_campagne = $campagne->update($request_params);

            if($update_campagne == FALSE)
            {
                $logManager->setLog($user_connected, (string)$campagne, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du campagne", NULL);
            }
            else
            if($update_campagne != FALSE || is_array($update_campagne))
            {
                $logManager->setLog($user_connected, (string)$campagne, false);
                echoResponse(200, true, "campagne mis à jour avec succès. Id : $id", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$campagne, true);
        echoResponse(400, false, "Tag inexistant !!", NULL);
    }
});

/**
* Delete an campagne, need to delete from association table first
* url - /campagnes/:id
* method - DELETE
* @params name
*/
$app->delete('/campagnes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    global $user_connected;
    $campagne = $db->entityManager->campagne[$id];

    $campagne_pays = $db->entityManager->campagne_pays("campagne_id", $id)->fetch();

    if($campagne_pays != FALSE)
    {
        if($db->entityManager->campagne_pays("campagne_id", $id)->delete())
        {
            if($campagne && $campagne->delete())
            {
                $logManager->setLog($user_connected, (string)$campagne, false);
                echoResponse(200, true, "campagne id : $id supprimée avec succès", NULL);
            }
            else
            {
                $logManager->setLog($user_connected, (string)$campagne, true);
                echoResponse(200, false, "campagne id : $id n'a pa pu être supprimée", NULL);
            }
        }
        else
        {
            $logManager->setLog($user_connected, (string)$campagne, true);
            echoResponse(400, false, "Erreur lors de la suppression de la campagne ayant l'id $id !!", NULL);
        }
    }
    else if($campagne_pays == FALSE)
    {
        if($campagne && $campagne->delete())
        {
            $logManager->setLog($user_connected, (string)$campagne, false);
            echoResponse(200, true, "campagne id : $id supprimée avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$campagne, true);
            echoResponse(200, false, "campagne id : $id n'a pa pu être supprimée", NULL);
        }
    }
});