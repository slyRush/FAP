<?php
/**
 * Routes afficheur manipulation - 'afficheur_zone_affichage' table concerned
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
 * Affect afficheur to zone
 * url - /afficheurs_zones
 * method - POST
 * @params id_afficheur, id_zones
 */
$app->post('/afficheur_zone_affichages/:id_afficheur', 'authenticate', function($id_afficheur) use ($app, $db, $logManager) {
    verifyRequiredParams(array('zones_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_zone = FALSE;

    foreach ($request_params["zones_id"] as $zone)
    {
        $data = array(
            "afficheur_id" => $id_afficheur,
            "zone_id" => $zone["id"]
        );

        $insert_afficheur_zone_affichage = $db->entityManager->afficheur_zone_affichage()->insert($data);

        if($insert_afficheur_zone_affichage != FALSE || is_array($insert_afficheur_zone_affichage))
        {
            $inserted_zone = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("afficheur_zone_affichage", $data), false); //afficheur_zone_affichage insérée
        }
        else
        if($insert_afficheur_zone_affichage == FALSE)
        {
            $inserted_zone = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("afficheur_zone_affichage", $data), true); //afficheur_zone_affichage non insérée
        }
    }

    if($inserted_zone == TRUE)
        echoResponse(201, true, "zones ajoutes", NULL);
    else if($inserted_zone == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});