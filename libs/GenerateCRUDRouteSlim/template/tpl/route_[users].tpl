<?php
/**
 * Routes {$table_name} manipulation - '{$table_name}' table concerned
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
 * Get all {$table_name}
 * url - /{$table_name}s
 * method - GET
 */
$app->get('/{$table_name}s', 'authenticate', function() use ($app, $db, $logManager) {
    ${$table_name}s = $db->entityManager->{$table_name}();
    ${$table_name}s_array = JSON::parseNotormObjectToArray(${$table_name}s);

    global $user_connected;

    if(count(${$table_name}s_array) > 0)
    {
        $data_{$table_name}s = array();

        foreach (${$table_name}s as ${$table_name}) array_push($data_{$table_name}s, JSON::removeNode(${$table_name}, "password_hash"));

        $logManager->setLog($user_connected, (string)${$table_name}s, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_{$table_name}s);
    }
    else
    {
        $logManager->setLog($user_connected, (string)${$table_name}s, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one {$table_name} by id
* url - /{$table_name}s/:id
* method - GET
*/
$app->get('/{$table_name}s/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    ${$table_name}s = $db->entityManager->{$table_name}[$id];
    global $user_connected;

    if(count(${$table_name}s) > 0)
    {
        $logManager->setLog($user_connected, (string)${$table_name}s, false);
        echoResponse(200, true, "{$table_name} est retourné", ${$table_name}s);
    }
    else
    {
        $logManager->setLog($user_connected, (string)${$table_name}s, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});