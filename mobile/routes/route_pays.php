<?php
/**
 * Routes pays manipulation - 'pays' table concerned
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
 * Get all pays
 * url - /payss
 * method - GET
 */
$app->get('/payss', 'authenticate', function() use ($app, $db, $logManager) {
    $payss = $db->entityManager->pays();
    $payss_array = JSON::parseNotormObjectToArray($payss);
    global $user_connected;

    if(count($payss_array) > 0)
    {
        $data_payss = array();
        foreach ($payss as $pays) array_push($data_payss, $pays);

        $logManager->setLog($user_connected, (string)$payss, false);
        echoResponse(200, true, "Tous les payss retournés", $data_payss);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$payss, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one pays by id
* url - /payss/:id
* method - GET
*/
$app->get('/payss/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $pays = $db->entityManager->pays[$id];
    global $user_connected;

    if(count($pays) > 0)
    {
        $logManager->setLog($user_connected, (string)$pays, false);
        echoResponse(200, true, "pays retourné(e)", $pays);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$pays, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new pays
* url - /payss/
* method - POST
* @params name
*/
$app->post('/payss', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_pays = $request_params["name"];

    $data = array(
        "name" => $name_pays
    );

    $insert_pays = $db->entityManager->pays()->insert($data);

    if($insert_pays == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("pays", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du pays", NULL);
    }
    else
    if($insert_pays != FALSE || is_array($insert_pays))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("pays", $data), false);
        echoResponse(201, true, "pays ajouté(e) avec succès", $insert_pays);
    }
});

/**
* Update one pays
* url - /payss/:id
* method - PUT
* @params name
*/
$app->put('/payss/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_pays = $request_params["name"];

    $pays = $db->entityManager->pays[$id];
    if($pays)
    {
        $testSameData = isSameData($pays, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$pays, false);
            echoResponse(200, true, "pays mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_pays = $pays->update(array("name" => $name_pays));

            if($update_pays == FALSE)
            {
                $logManager->setLog($user_connected, (string)$pays, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du pays", NULL);
            }
            else
            if($update_pays != FALSE || is_array($update_pays))
            {
                $logManager->setLog($user_connected, (string)$pays, false);
                echoResponse(201, true, "pays mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$pays, true);
        echoResponse(400, false, "pays inexistant !!", NULL);
    }

});

/**
* Delete one pays
* url - /payss/:id
* method - DELETE
* @params name
*/
$app->delete('/payss/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $pays = $db->entityManager->pays[$id];
    global $user_connected;

    if($db->entityManager->application_pays("pays_id", $id)->delete())
    {
        if($pays && $pays->delete())
        {
            $logManager->setLog($user_connected, (string)$pays, false);
            echoResponse(200, true, "pays id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$pays, true);
            echoResponse(200, false, "pays id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$pays, true);
        echoResponse(400, false, "Erreur lors de la suppression de la pays ayant l'id $id : pays inexistant !!", NULL);
    }
});