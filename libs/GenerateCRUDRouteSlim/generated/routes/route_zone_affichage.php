<?php
/**
 * Routes zone manipulation - 'zone_affichage' table concerned
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
 * Affect zone to affichage
 * url - /zones_affichages
 * method - POST
 * @params id_zone, id_affichages
 */
$app->post('/zone_affichages/:id_zone', 'authenticate', function($id_zone) use ($app, $db, $logManager) {
    verifyRequiredParams(array('affichages_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_affichage = FALSE;

    foreach ($request_params["affichages_id"] as $affichage)
    {
        $data = array(
            "zone_id" => $id_zone,
            "affichage_id" => $affichage["id"]
        );

        $insert_zone_affichage = $db->entityManager->zone_affichage()->insert($data);

        if($insert_zone_affichage != FALSE || is_array($insert_zone_affichage))
        {
            $inserted_affichage = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("zone_affichage", $data), false); //zone_affichage insérée
        }
        else
        if($insert_zone_affichage == FALSE)
        {
            $inserted_affichage = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("zone_affichage", $data), true); //zone_affichage non insérée
        }
    }

    if($inserted_affichage == TRUE)
        echoResponse(201, true, "affichages ajoutes", NULL);
    else if($inserted_affichage == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});