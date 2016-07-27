<?php
/**
 * Routes raison manipulation - 'raison_signalement' table concerned
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
 * Affect raison to signalement
 * url - /raisons_signalements
 * method - POST
 * @params id_raison, id_signalements
 */
$app->post('/raison_signalements/:id_raison', 'authenticate', function($id_raison) use ($app, $db, $logManager) {
    verifyRequiredParams(array('signalements_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_signalement = FALSE;

    foreach ($request_params["signalements_id"] as $signalement)
    {
        $data = array(
            "raison_id" => $id_raison,
            "signalement_id" => $signalement["id"]
        );

        $insert_raison_signalement = $db->entityManager->raison_signalement()->insert($data);

        if($insert_raison_signalement != FALSE || is_array($insert_raison_signalement))
        {
            $inserted_signalement = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("raison_signalement", $data), false); //raison_signalement insérée
        }
        else
        if($insert_raison_signalement == FALSE)
        {
            $inserted_signalement = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("raison_signalement", $data), true); //raison_signalement non insérée
        }
    }

    if($inserted_signalement == TRUE)
        echoResponse(201, true, "signalements ajoutes", NULL);
    else if($inserted_signalement == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});