<?php
/**
 * Routes fournisseur manipulation - 'fournisseur' table concerned
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
 * Get all fournisseur
 * url - /fournisseurs
 * method - GET
 */
$app->get('/fournisseurs', 'authenticate', function() use ($app, $db, $logManager) {
    $fournisseurs = $db->entityManager->fournisseur();
    $fournisseurs_array = JSON::parseNotormObjectToArray($fournisseurs);

    global $user_connected;

    if(count($fournisseurs_array) > 0)
    {
        $data_fournisseurs = array();

        foreach ($fournisseurs as $fournisseur) array_push($data_fournisseurs, JSON::removeNode($fournisseur, "password_hash"));

        $logManager->setLog($user_connected, (string)$fournisseurs, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_fournisseurs);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$fournisseurs, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one fournisseur by id
* url - /fournisseurs/:id
* method - GET
*/
$app->get('/fournisseurs/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $fournisseurs = $db->entityManager->fournisseur[$id];
    global $user_connected;

    if(count($fournisseurs) > 0)
    {
        $logManager->setLog($user_connected, (string)$fournisseurs, false);
        echoResponse(200, true, "fournisseur est retourné", $fournisseurs);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$fournisseurs, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});