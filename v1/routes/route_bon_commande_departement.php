<?php
/**
 * Routes bon manipulation - 'bon_commande_departement' table concerned
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
 * Affect bon to commande
 * url - /bons_commandes
 * method - POST
 * @params id_bon, id_commandes
 */
$app->post('/bon_commande_departements/:id_bon', 'authenticate', function($id_bon) use ($app, $db, $logManager) {
    verifyRequiredParams(array('commandes_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_commande = FALSE;

    foreach ($request_params["commandes_id"] as $commande)
    {
        $data = array(
            "bon_id" => $id_bon,
            "commande_id" => $commande["id"]
        );

        $insert_bon_commande_departement = $db->entityManager->bon_commande_departement()->insert($data);

        if($insert_bon_commande_departement != FALSE || is_array($insert_bon_commande_departement))
        {
            $inserted_commande = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("bon_commande_departement", $data), false); //bon_commande_departement insérée
        }
        else
        if($insert_bon_commande_departement == FALSE)
        {
            $inserted_commande = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("bon_commande_departement", $data), true); //bon_commande_departement non insérée
        }
    }

    if($inserted_commande == TRUE)
        echoResponse(201, true, "commandes ajoutes", NULL);
    else if($inserted_commande == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});