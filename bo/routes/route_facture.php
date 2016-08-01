<?php
/**
 * Routes facture manipulation - 'facture' table concerned
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
 * Get all facture
 * url - /factures
 * method - GET
 */
$app->get('/factures', 'authenticate', function() use ($app, $db, $logManager) {
    $factures = $db->entityManager->facture();
    $factures_array = JSON::parseNotormObjectToArray($factures);
    global $user_connected;

    if(count($factures_array) > 0)
    {
        $data_factures = array();
        foreach ($factures as $facture) array_push($data_factures, $facture);

        $logManager->setLog($user_connected, (string)$factures, false);
        echoResponse(200, true, "Tous les factures retournés", $data_factures);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$factures, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one facture by id
* url - /factures/:id
* method - GET
*/
$app->get('/factures/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $facture = $db->entityManager->facture[$id];
    global $user_connected;

    if(count($facture) > 0)
    {
        $logManager->setLog($user_connected, (string)$facture, false);
        echoResponse(200, true, "facture retourné(e)", $facture);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$facture, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new facture
* url - /factures/
* method - POST
* @params name
*/
$app->post('/factures', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('reference','description','commentaire')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_facture = $request_params["name"];

    $data = array(
        "name" => $name_facture
    );

    $insert_facture = $db->entityManager->facture()->insert($data);

    if($insert_facture == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("facture", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du facture", NULL);
    }
    else
    if($insert_facture != FALSE || is_array($insert_facture))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("facture", $data), false);
        echoResponse(201, true, "facture ajouté(e) avec succès", $insert_facture);
    }
});

/**
* Update one facture
* url - /factures/:id
* method - PUT
* @params name
*/
$app->put('/factures/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('reference','description','commentaire')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_facture = $request_params["name"];

    $facture = $db->entityManager->facture[$id];
    if($facture)
    {
        $testSameData = isSameData($facture, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$facture, false);
            echoResponse(200, true, "facture mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_facture = $facture->update(array("name" => $name_facture));

            if($update_facture == FALSE)
            {
                $logManager->setLog($user_connected, (string)$facture, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du facture", NULL);
            }
            else
            if($update_facture != FALSE || is_array($update_facture))
            {
                $logManager->setLog($user_connected, (string)$facture, false);
                echoResponse(201, true, "facture mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$facture, true);
        echoResponse(400, false, "facture inexistant !!", NULL);
    }

});

/**
* Delete one facture
* url - /factures/:id
* method - DELETE
* @params name
*/
$app->delete('/factures/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $facture = $db->entityManager->facture[$id];
    global $user_connected;

    if($db->entityManager->application_facture("facture_id", $id)->delete())
    {
        if($facture && $facture->delete())
        {
            $logManager->setLog($user_connected, (string)$facture, false);
            echoResponse(200, true, "facture id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$facture, true);
            echoResponse(200, false, "facture id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$facture, true);
        echoResponse(400, false, "Erreur lors de la suppression de la facture ayant l'id $id : facture inexistant !!", NULL);
    }
});