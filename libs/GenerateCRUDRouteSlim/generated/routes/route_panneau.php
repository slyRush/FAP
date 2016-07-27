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

        foreach ($panneaus as $panneau) array_push($data_panneaus, JSON::removeNode($panneau, "password_hash"));

        $logManager->setLog($user_connected, (string)$panneaus, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_panneaus);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$panneaus, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one panneau by id
* url - /panneaus/:id
* method - GET
*/
$app->get('/panneaus/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $panneaus = $db->entityManager->panneau[$id];
    global $user_connected;

    if(count($panneaus) > 0)
    {
        $logManager->setLog($user_connected, (string)$panneaus, false);
        echoResponse(200, true, "panneau est retourné", $panneaus);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$panneaus, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});