<?php
/**
 * Routes adresse manipulation - 'adresse' table concerned
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
 * Get all adresse
 * url - /adresses
 * method - GET
 */
$app->get('/adresses', 'authenticate', function() use ($app, $db, $logManager) {
    $adresses = $db->entityManager->adresse();
    $adresses_array = JSON::parseNotormObjectToArray($adresses);

    global $user_connected;

    if(count($adresses_array) > 0)
    {
        $data_adresses = array();

        foreach ($adresses as $adresse) array_push($data_adresses, JSON::removeNode($adresse, "password_hash"));

        $logManager->setLog($user_connected, (string)$adresses, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_adresses);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$adresses, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one adresse by id
* url - /adresses/:id
* method - GET
*/
$app->get('/adresses/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $adresses = $db->entityManager->adresse[$id];
    global $user_connected;

    if(count($adresses) > 0)
    {
        $logManager->setLog($user_connected, (string)$adresses, false);
        echoResponse(200, true, "adresse est retourné", $adresses);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$adresses, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});