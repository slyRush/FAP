<?php
/**
 * Routes assoc_ucfb manipulation - 'assoc_ucfb' table concerned
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
 * Get all assoc_ucfb
 * url - /assoc_ucfbs
 * method - GET
 */
$app->get('/assoc_ucfbs', 'authenticate', function() use ($app, $db, $logManager) {
    $assoc_ucfbs = $db->entityManager->assoc_ucfb();
    $assoc_ucfbs_array = JSON::parseNotormObjectToArray($assoc_ucfbs);

    global $user_connected;

    if(count($assoc_ucfbs_array) > 0)
    {
        $data_assoc_ucfbs = array();

        foreach ($assoc_ucfbs as $assoc_ucfb) array_push($data_assoc_ucfbs, JSON::removeNode($assoc_ucfb, "password_hash"));

        $logManager->setLog($user_connected, (string)$assoc_ucfbs, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_assoc_ucfbs);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$assoc_ucfbs, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one assoc_ucfb by id
* url - /assoc_ucfbs/:id
* method - GET
*/
$app->get('/assoc_ucfbs/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $assoc_ucfbs = $db->entityManager->assoc_ucfb[$id];
    global $user_connected;

    if(count($assoc_ucfbs) > 0)
    {
        $logManager->setLog($user_connected, (string)$assoc_ucfbs, false);
        echoResponse(200, true, "assoc_ucfb est retourné", $assoc_ucfbs);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$assoc_ucfbs, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});