<?php
/**
 * Routes afficheur manipulation - 'afficheur' table concerned
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
 * Get all afficheur
 * url - /afficheurs
 * method - GET
 */
$app->get('/afficheurs', 'authenticate', function() use ($app, $db, $logManager) {
    $afficheurs = $db->entityManager->afficheur();
    $afficheurs_array = JSON::parseNotormObjectToArray($afficheurs);

    global $user_connected;

    if(count($afficheurs_array) > 0)
    {
        $data_afficheurs = array();

        foreach ($afficheurs as $afficheur) array_push($data_afficheurs, JSON::removeNode($afficheur, "password_hash"));

        $logManager->setLog($user_connected, (string)$afficheurs, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_afficheurs);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$afficheurs, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one afficheur by id
* url - /afficheurs/:id
* method - GET
*/
$app->get('/afficheurs/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $afficheurs = $db->entityManager->afficheur[$id];
    global $user_connected;

    if(count($afficheurs) > 0)
    {
        $logManager->setLog($user_connected, (string)$afficheurs, false);
        echoResponse(200, true, "afficheur est retourné", $afficheurs);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$afficheurs, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});