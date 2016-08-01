<?php
/**
 * Routes assoc manipulation - 'assoc_ppr_assoc_ucfb' table concerned
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
 * Affect assoc to ppr
 * url - /assocs_pprs
 * method - POST
 * @params id_assoc, id_pprs
 */
$app->post('/assoc_ppr_assoc_ucfbs/:id_assoc', 'authenticate', function($id_assoc) use ($app, $db, $logManager) {
    verifyRequiredParams(array('pprs_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_ppr = FALSE;

    foreach ($request_params["pprs_id"] as $ppr)
    {
        $data = array(
            "assoc_id" => $id_assoc,
            "ppr_id" => $ppr["id"]
        );

        $insert_assoc_ppr_assoc_ucfb = $db->entityManager->assoc_ppr_assoc_ucfb()->insert($data);

        if($insert_assoc_ppr_assoc_ucfb != FALSE || is_array($insert_assoc_ppr_assoc_ucfb))
        {
            $inserted_ppr = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("assoc_ppr_assoc_ucfb", $data), false); //assoc_ppr_assoc_ucfb insérée
        }
        else
        if($insert_assoc_ppr_assoc_ucfb == FALSE)
        {
            $inserted_ppr = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("assoc_ppr_assoc_ucfb", $data), true); //assoc_ppr_assoc_ucfb non insérée
        }
    }

    if($inserted_ppr == TRUE)
        echoResponse(201, true, "pprs ajoutes", NULL);
    else if($inserted_ppr == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});