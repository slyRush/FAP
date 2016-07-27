<?php
/**
 * Routes superadmin manipulation - 'superadmin' table concerned
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
 * Get all superadmin
 * url - /superadmins
 * method - GET
 */
$app->get('/superadmins', 'authenticate', function() use ($app, $db, $logManager) {
    $superadmins = $db->entityManager->superadmin();
    $superadmins_array = JSON::parseNotormObjectToArray($superadmins);

    global $user_connected;

    if(count($superadmins_array) > 0)
    {
        $data_superadmins = array();

        foreach ($superadmins as $superadmin) array_push($data_superadmins, JSON::removeNode($superadmin, "password_hash"));

        $logManager->setLog($user_connected, (string)$superadmins, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_superadmins);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$superadmins, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one superadmin by id
* url - /superadmins/:id
* method - GET
*/
$app->get('/superadmins/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $superadmins = $db->entityManager->superadmin[$id];
    global $user_connected;

    if(count($superadmins) > 0)
    {
        $logManager->setLog($user_connected, (string)$superadmins, false);
        echoResponse(200, true, "superadmin est retourné", $superadmins);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$superadmins, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});