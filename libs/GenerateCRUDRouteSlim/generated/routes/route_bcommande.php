<?php
/**
 * Routes bcommande manipulation - 'bcommande' table concerned
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
 * Get all bcommande
 * url - /bcommandes
 * method - GET
 */
$app->get('/bcommandes', 'authenticate', function() use ($app, $db, $logManager) {
    $bcommandes = $db->entityManager->bcommande();
    $bcommandes_array = JSON::parseNotormObjectToArray($bcommandes);
    global $user_connected;

    if(count($bcommandes_array) > 0)
    {
        $data_bcommandes = array();
        foreach ($bcommandes as $bcommande) array_push($data_bcommandes, $bcommande);

        $logManager->setLog($user_connected, (string)$bcommandes, false);
        echoResponse(200, true, "Tous les bcommandes retournés", $data_bcommandes);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$bcommandes, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one bcommande by id
* url - /bcommandes/:id
* method - GET
*/
$app->get('/bcommandes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $bcommande = $db->entityManager->bcommande[$id];
    global $user_connected;

    if(count($bcommande) > 0)
    {
        $logManager->setLog($user_connected, (string)$bcommande, false);
        echoResponse(200, true, "bcommande retourné(e)", $bcommande);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$bcommande, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new bcommande
* url - /bcommandes/
* method - POST
* @params name
*/
$app->post('/bcommandes', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('reference','description','commentaire')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_bcommande = $request_params["name"];

    $data = array(
        "name" => $name_bcommande
    );

    $insert_bcommande = $db->entityManager->bcommande()->insert($data);

    if($insert_bcommande == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("bcommande", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du bcommande", NULL);
    }
    else
    if($insert_bcommande != FALSE || is_array($insert_bcommande))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("bcommande", $data), false);
        echoResponse(201, true, "bcommande ajouté(e) avec succès", $insert_bcommande);
    }
});

/**
* Update one bcommande
* url - /bcommandes/:id
* method - PUT
* @params name
*/
$app->put('/bcommandes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('reference','description','commentaire')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_bcommande = $request_params["name"];

    $bcommande = $db->entityManager->bcommande[$id];
    if($bcommande)
    {
        $testSameData = isSameData($bcommande, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$bcommande, false);
            echoResponse(200, true, "bcommande mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_bcommande = $bcommande->update(array("name" => $name_bcommande));

            if($update_bcommande == FALSE)
            {
                $logManager->setLog($user_connected, (string)$bcommande, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du bcommande", NULL);
            }
            else
            if($update_bcommande != FALSE || is_array($update_bcommande))
            {
                $logManager->setLog($user_connected, (string)$bcommande, false);
                echoResponse(201, true, "bcommande mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$bcommande, true);
        echoResponse(400, false, "bcommande inexistant !!", NULL);
    }

});

/**
* Delete one bcommande
* url - /bcommandes/:id
* method - DELETE
* @params name
*/
$app->delete('/bcommandes/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $bcommande = $db->entityManager->bcommande[$id];
    global $user_connected;

    if($db->entityManager->application_bcommande("bcommande_id", $id)->delete())
    {
        if($bcommande && $bcommande->delete())
        {
            $logManager->setLog($user_connected, (string)$bcommande, false);
            echoResponse(200, true, "bcommande id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$bcommande, true);
            echoResponse(200, false, "bcommande id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$bcommande, true);
        echoResponse(400, false, "Erreur lors de la suppression de la bcommande ayant l'id $id : bcommande inexistant !!", NULL);
    }
});