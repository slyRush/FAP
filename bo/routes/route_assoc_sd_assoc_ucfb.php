<?php
/**
 * Routes assoc manipulation - 'assoc_sd_assoc_ucfb' table concerned
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
 * Affect assoc to sd
 * url - /assocs_sds
 * method - POST
 * @params id_assoc, id_sds
 */
$app->post('/assoc_sd_assoc_ucfbs/:id_assoc', 'authenticate', function($id_assoc) use ($app, $db, $logManager) {
    verifyRequiredParams(array('sds_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_sd = FALSE;

    foreach ($request_params["sds_id"] as $sd)
    {
        $data = array(
            "assoc_id" => $id_assoc,
            "sd_id" => $sd["id"]
        );

        $insert_assoc_sd_assoc_ucfb = $db->entityManager->assoc_sd_assoc_ucfb()->insert($data);

        if($insert_assoc_sd_assoc_ucfb != FALSE || is_array($insert_assoc_sd_assoc_ucfb))
        {
            $inserted_sd = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("assoc_sd_assoc_ucfb", $data), false); //assoc_sd_assoc_ucfb insérée
        }
        else
        if($insert_assoc_sd_assoc_ucfb == FALSE)
        {
            $inserted_sd = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("assoc_sd_assoc_ucfb", $data), true); //assoc_sd_assoc_ucfb non insérée
        }
    }

    if($inserted_sd == TRUE)
        echoResponse(201, true, "sds ajoutes", NULL);
    else if($inserted_sd == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});