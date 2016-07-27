<?php
/**
 * Routes arrondissement manipulation - 'arrondissement' table concerned
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
 * Get all arrondissement
 * url - /arrondissements
 * method - GET
 */
$app->get('/arrondissements', 'authenticate', function() use ($app, $db, $logManager) {
    $arrondissements = $db->entityManager->arrondissement();
    $arrondissements_array = JSON::parseNotormObjectToArray($arrondissements);
    global $user_connected;

    if(count($arrondissements_array) > 0)
    {
        $data_arrondissements = array();
        foreach ($arrondissements as $arrondissement) array_push($data_arrondissements, $arrondissement);

        $logManager->setLog($user_connected, (string)$arrondissements, false);
        echoResponse(200, true, "Tous les arrondissements retournés", $data_arrondissements);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$arrondissements, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one arrondissement by id
* url - /arrondissements/:id
* method - GET
*/
$app->get('/arrondissements/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $arrondissement = $db->entityManager->arrondissement[$id];
    global $user_connected;

    if(count($arrondissement) > 0)
    {
        $logManager->setLog($user_connected, (string)$arrondissement, false);
        echoResponse(200, true, "arrondissement retourné(e)", $arrondissement);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$arrondissement, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new arrondissement
* url - /arrondissements/
* method - POST
* @params name
*/
$app->post('/arrondissements', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_arrondissement = $request_params["name"];

    $data = array(
        "name" => $name_arrondissement
    );

    $insert_arrondissement = $db->entityManager->arrondissement()->insert($data);

    if($insert_arrondissement == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("arrondissement", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du arrondissement", NULL);
    }
    else
    if($insert_arrondissement != FALSE || is_array($insert_arrondissement))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("arrondissement", $data), false);
        echoResponse(201, true, "arrondissement ajouté(e) avec succès", $insert_arrondissement);
    }
});

/**
* Update one arrondissement
* url - /arrondissements/:id
* method - PUT
* @params name
*/
$app->put('/arrondissements/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_arrondissement = $request_params["name"];

    $arrondissement = $db->entityManager->arrondissement[$id];
    if($arrondissement)
    {
        $testSameData = isSameData($arrondissement, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$arrondissement, false);
            echoResponse(200, true, "arrondissement mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_arrondissement = $arrondissement->update(array("name" => $name_arrondissement));

            if($update_arrondissement == FALSE)
            {
                $logManager->setLog($user_connected, (string)$arrondissement, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du arrondissement", NULL);
            }
            else
            if($update_arrondissement != FALSE || is_array($update_arrondissement))
            {
                $logManager->setLog($user_connected, (string)$arrondissement, false);
                echoResponse(201, true, "arrondissement mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$arrondissement, true);
        echoResponse(400, false, "arrondissement inexistant !!", NULL);
    }

});

/**
* Delete one arrondissement
* url - /arrondissements/:id
* method - DELETE
* @params name
*/
$app->delete('/arrondissements/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $arrondissement = $db->entityManager->arrondissement[$id];
    global $user_connected;

    if($db->entityManager->application_arrondissement("arrondissement_id", $id)->delete())
    {
        if($arrondissement && $arrondissement->delete())
        {
            $logManager->setLog($user_connected, (string)$arrondissement, false);
            echoResponse(200, true, "arrondissement id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$arrondissement, true);
            echoResponse(200, false, "arrondissement id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$arrondissement, true);
        echoResponse(400, false, "Erreur lors de la suppression de la arrondissement ayant l'id $id : arrondissement inexistant !!", NULL);
    }
});