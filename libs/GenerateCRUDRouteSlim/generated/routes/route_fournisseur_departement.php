<?php
/**
 * Routes fournisseur manipulation - 'fournisseur_departement' table concerned
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
 * Affect fournisseur to departement
 * url - /fournisseurs_departements
 * method - POST
 * @params id_fournisseur, id_departements
 */
$app->post('/fournisseur_departements/:id_fournisseur', 'authenticate', function($id_fournisseur) use ($app, $db, $logManager) {
    verifyRequiredParams(array('departements_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_departement = FALSE;

    foreach ($request_params["departements_id"] as $departement)
    {
        $data = array(
            "fournisseur_id" => $id_fournisseur,
            "departement_id" => $departement["id"]
        );

        $insert_fournisseur_departement = $db->entityManager->fournisseur_departement()->insert($data);

        if($insert_fournisseur_departement != FALSE || is_array($insert_fournisseur_departement))
        {
            $inserted_departement = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("fournisseur_departement", $data), false); //fournisseur_departement insérée
        }
        else
        if($insert_fournisseur_departement == FALSE)
        {
            $inserted_departement = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("fournisseur_departement", $data), true); //fournisseur_departement non insérée
        }
    }

    if($inserted_departement == TRUE)
        echoResponse(201, true, "departements ajoutes", NULL);
    else if($inserted_departement == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});