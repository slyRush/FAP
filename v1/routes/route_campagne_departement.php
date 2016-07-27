<?php
/**
 * Routes campagne manipulation - 'campagne_departement' table concerned
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
 * Affect campagne to departement
 * url - /campagnes_departements
 * method - POST
 * @params id_campagne, id_departements
 */
$app->post('/campagne_departements/:id_campagne', 'authenticate', function($id_campagne) use ($app, $db, $logManager) {
    verifyRequiredParams(array('departements_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_departement = FALSE;

    foreach ($request_params["departements_id"] as $departement)
    {
        $data = array(
            "campagne_id" => $id_campagne,
            "departement_id" => $departement["id"]
        );

        $insert_campagne_departement = $db->entityManager->campagne_departement()->insert($data);

        if($insert_campagne_departement != FALSE || is_array($insert_campagne_departement))
        {
            $inserted_departement = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("campagne_departement", $data), false); //campagne_departement insérée
        }
        else
        if($insert_campagne_departement == FALSE)
        {
            $inserted_departement = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("campagne_departement", $data), true); //campagne_departement non insérée
        }
    }

    if($inserted_departement == TRUE)
        echoResponse(201, true, "departements ajoutes", NULL);
    else if($inserted_departement == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});