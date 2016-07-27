<?php
/**
 * Routes admin manipulation - 'admin' table concerned
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
 * Get all admin
 * url - /admins
 * method - GET
 */
$app->get('/admins', 'authenticate', function() use ($app, $db, $logManager) {
    $admins = $db->entityManager->admin();
    $admins_array = JSON::parseNotormObjectToArray($admins);

    global $user_connected;

    if(count($admins_array) > 0)
    {
        $data_admins = array();

        foreach ($admins as $admin) array_push($data_admins, JSON::removeNode($admin, "password_hash"));

        $logManager->setLog($user_connected, (string)$admins, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_admins);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$admins, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one admin by id
* url - /admins/:id
* method - GET
*/
$app->get('/admins/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $admins = $db->entityManager->admin[$id];
    global $user_connected;

    if(count($admins) > 0)
    {
        $logManager->setLog($user_connected, (string)$admins, false);
        echoResponse(200, true, "admin est retourné", $admins);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$admins, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});