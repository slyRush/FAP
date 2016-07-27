<?php
/**
 * Routes utilisateur manipulation - 'utilisateur' table concerned
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
 * Get all utilisateur
 * url - /utilisateurs
 * method - GET
 */
$app->get('/utilisateurs', 'authenticate', function() use ($app, $db, $logManager) {
    $utilisateurs = $db->entityManager->utilisateur();
    $utilisateurs_array = JSON::parseNotormObjectToArray($utilisateurs);

    global $user_connected;

    if(count($utilisateurs_array) > 0)
    {
        $data_utilisateurs = array();

        foreach ($utilisateurs as $utilisateur) array_push($data_utilisateurs, JSON::removeNode($utilisateur, "password_hash"));

        $logManager->setLog($user_connected, (string)$utilisateurs, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_utilisateurs);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$utilisateurs, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one utilisateur by id
* url - /utilisateurs/:id
* method - GET
*/
$app->get('/utilisateurs/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $utilisateurs = $db->entityManager->utilisateur[$id];
    global $user_connected;

    if(count($utilisateurs) > 0)
    {
        $logManager->setLog($user_connected, (string)$utilisateurs, false);
        echoResponse(200, true, "utilisateur est retourné", $utilisateurs);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$utilisateurs, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});