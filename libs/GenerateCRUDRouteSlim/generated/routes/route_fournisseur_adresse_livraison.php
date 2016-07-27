<?php
/**
 * Routes fournisseur manipulation - 'fournisseur_adresse_livraison' table concerned
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
 * Affect fournisseur to adresse
 * url - /fournisseurs_adresses
 * method - POST
 * @params id_fournisseur, id_adresses
 */
$app->post('/fournisseur_adresse_livraisons/:id_fournisseur', 'authenticate', function($id_fournisseur) use ($app, $db, $logManager) {
    verifyRequiredParams(array('adresses_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_adresse = FALSE;

    foreach ($request_params["adresses_id"] as $adresse)
    {
        $data = array(
            "fournisseur_id" => $id_fournisseur,
            "adresse_id" => $adresse["id"]
        );

        $insert_fournisseur_adresse_livraison = $db->entityManager->fournisseur_adresse_livraison()->insert($data);

        if($insert_fournisseur_adresse_livraison != FALSE || is_array($insert_fournisseur_adresse_livraison))
        {
            $inserted_adresse = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("fournisseur_adresse_livraison", $data), false); //fournisseur_adresse_livraison insérée
        }
        else
        if($insert_fournisseur_adresse_livraison == FALSE)
        {
            $inserted_adresse = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("fournisseur_adresse_livraison", $data), true); //fournisseur_adresse_livraison non insérée
        }
    }

    if($inserted_adresse == TRUE)
        echoResponse(201, true, "adresses ajoutes", NULL);
    else if($inserted_adresse == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});