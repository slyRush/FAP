<?php
/**
 * Routes etat manipulation - 'etat_panneau' table concerned
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
 * Affect etat to panneau
 * url - /etats_panneaus
 * method - POST
 * @params id_etat, id_panneaus
 */
$app->post('/etat_panneaus/:id_etat', 'authenticate', function($id_etat) use ($app, $db, $logManager) {
    verifyRequiredParams(array('panneaus_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_panneau = FALSE;

    foreach ($request_params["panneaus_id"] as $panneau)
    {
        $data = array(
            "etat_id" => $id_etat,
            "panneau_id" => $panneau["id"]
        );

        $insert_etat_panneau = $db->entityManager->etat_panneau()->insert($data);

        if($insert_etat_panneau != FALSE || is_array($insert_etat_panneau))
        {
            $inserted_panneau = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("etat_panneau", $data), false); //etat_panneau insérée
        }
        else
        if($insert_etat_panneau == FALSE)
        {
            $inserted_panneau = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("etat_panneau", $data), true); //etat_panneau non insérée
        }
    }

    if($inserted_panneau == TRUE)
        echoResponse(201, true, "panneaus ajoutes", NULL);
    else if($inserted_panneau == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});