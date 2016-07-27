<?php
/**
 * Routes campagne manipulation - 'campagne_client' table concerned
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
 * Affect campagne to client
 * url - /campagnes_clients
 * method - POST
 * @params id_campagne, id_clients
 */
$app->post('/campagne_clients/:id_campagne', 'authenticate', function($id_campagne) use ($app, $db, $logManager) {
    verifyRequiredParams(array('clients_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_client = FALSE;

    foreach ($request_params["clients_id"] as $client)
    {
        $data = array(
            "campagne_id" => $id_campagne,
            "client_id" => $client["id"]
        );

        $insert_campagne_client = $db->entityManager->campagne_client()->insert($data);

        if($insert_campagne_client != FALSE || is_array($insert_campagne_client))
        {
            $inserted_client = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("campagne_client", $data), false); //campagne_client insérée
        }
        else
        if($insert_campagne_client == FALSE)
        {
            $inserted_client = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("campagne_client", $data), true); //campagne_client non insérée
        }
    }

    if($inserted_client == TRUE)
        echoResponse(201, true, "clients ajoutes", NULL);
    else if($inserted_client == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});