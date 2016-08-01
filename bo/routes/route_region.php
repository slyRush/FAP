<?php
/**
 * Routes region manipulation - 'region' table concerned
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
 * Get all region
 * url - /regions
 * method - GET
 */
$app->get('/regions', 'authenticate', function() use ($app, $db, $logManager) {
    $regions = $db->entityManager->region();
    $regions_array = JSON::parseNotormObjectToArray($regions);
    global $user_connected;

    if(count($regions_array) > 0)
    {
        $data_regions = array();
        foreach ($regions as $region) array_push($data_regions, $region);

        $logManager->setLog($user_connected, (string)$regions, false);
        echoResponse(200, true, "Tous les regions retournés", $data_regions);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$regions, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one region by id
* url - /regions/:id
* method - GET
*/
$app->get('/regions/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $region = $db->entityManager->region[$id];
    global $user_connected;

    if(count($region) > 0)
    {
        $logManager->setLog($user_connected, (string)$region, false);
        echoResponse(200, true, "region retourné(e)", $region);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$region, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new region
* url - /regions/
* method - POST
* @params name
*/
$app->post('/regions', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_region = $request_params["name"];

    $data = array(
        "name" => $name_region
    );

    $insert_region = $db->entityManager->region()->insert($data);

    if($insert_region == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("region", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du region", NULL);
    }
    else
    if($insert_region != FALSE || is_array($insert_region))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("region", $data), false);
        echoResponse(201, true, "region ajouté(e) avec succès", $insert_region);
    }
});

/**
* Update one region
* url - /regions/:id
* method - PUT
* @params name
*/
$app->put('/regions/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_region = $request_params["name"];

    $region = $db->entityManager->region[$id];
    if($region)
    {
        $testSameData = isSameData($region, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$region, false);
            echoResponse(200, true, "region mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_region = $region->update(array("name" => $name_region));

            if($update_region == FALSE)
            {
                $logManager->setLog($user_connected, (string)$region, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du region", NULL);
            }
            else
            if($update_region != FALSE || is_array($update_region))
            {
                $logManager->setLog($user_connected, (string)$region, false);
                echoResponse(201, true, "region mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$region, true);
        echoResponse(400, false, "region inexistant !!", NULL);
    }

});

/**
* Delete one region
* url - /regions/:id
* method - DELETE
* @params name
*/
$app->delete('/regions/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $region = $db->entityManager->region[$id];
    global $user_connected;

    if($db->entityManager->application_region("region_id", $id)->delete())
    {
        if($region && $region->delete())
        {
            $logManager->setLog($user_connected, (string)$region, false);
            echoResponse(200, true, "region id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$region, true);
            echoResponse(200, false, "region id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$region, true);
        echoResponse(400, false, "Erreur lors de la suppression de la region ayant l'id $id : region inexistant !!", NULL);
    }
});