<?php
/**
 * Routes campagne manipulation - 'campagne_fournisseur' table concerned
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
 * Affect campagne to fournisseur
 * url - /campagnes_fournisseurs
 * method - POST
 * @params id_campagne, id_fournisseurs
 */
$app->post('/campagne_fournisseurs/:id_campagne', 'authenticate', function($id_campagne) use ($app, $db, $logManager) {
    verifyRequiredParams(array('fournisseurs_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_fournisseur = FALSE;

    foreach ($request_params["fournisseurs_id"] as $fournisseur)
    {
        $data = array(
            "campagne_id" => $id_campagne,
            "fournisseur_id" => $fournisseur["id"]
        );

        $insert_campagne_fournisseur = $db->entityManager->campagne_fournisseur()->insert($data);

        if($insert_campagne_fournisseur != FALSE || is_array($insert_campagne_fournisseur))
        {
            $inserted_fournisseur = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("campagne_fournisseur", $data), false); //campagne_fournisseur insérée
        }
        else
        if($insert_campagne_fournisseur == FALSE)
        {
            $inserted_fournisseur = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("campagne_fournisseur", $data), true); //campagne_fournisseur non insérée
        }
    }

    if($inserted_fournisseur == TRUE)
        echoResponse(201, true, "fournisseurs ajoutes", NULL);
    else if($inserted_fournisseur == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});