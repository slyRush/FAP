<?php
/**
 * Routes client manipulation - 'client' table concerned
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
 * Get all client
 * url - /clients
 * method - GET
 */
$app->get('/clients', 'authenticate', function() use ($app, $db, $logManager) {
    $clients = $db->entityManager->client();
    $clients_array = JSON::parseNotormObjectToArray($clients);

    global $user_connected;

    if(count($clients_array) > 0)
    {
        $data_clients = array();

        foreach ($clients as $client) array_push($data_clients, JSON::removeNode($client, "password_hash"));

        $logManager->setLog($user_connected, (string)$clients, false);
        echoResponse(200, true, "Tous les auteurs retournés", $data_clients);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$clients, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }

});

/**
* Get one client by id
* url - /clients/:id
* method - GET
*/
$app->get('/clients/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $clients = $db->entityManager->client[$id];
    global $user_connected;

    if(count($clients) > 0)
    {
        $logManager->setLog($user_connected, (string)$clients, false);
        echoResponse(200, true, "client est retourné", $clients);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$clients, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});