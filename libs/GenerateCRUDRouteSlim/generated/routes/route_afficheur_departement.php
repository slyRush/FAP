<?php
/**
 * Routes afficheur manipulation - 'afficheur_departement' table concerned
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
 * Affect afficheur to departement
 * url - /afficheurs_departements
 * method - POST
 * @params id_afficheur, id_departements
 */
$app->post('/afficheur_departements/:id_afficheur', 'authenticate', function($id_afficheur) use ($app, $db, $logManager) {
    verifyRequiredParams(array('departements_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_departement = FALSE;

    foreach ($request_params["departements_id"] as $departement)
    {
        $data = array(
            "afficheur_id" => $id_afficheur,
            "departement_id" => $departement["id"]
        );

        $insert_afficheur_departement = $db->entityManager->afficheur_departement()->insert($data);

        if($insert_afficheur_departement != FALSE || is_array($insert_afficheur_departement))
        {
            $inserted_departement = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("afficheur_departement", $data), false); //afficheur_departement insérée
        }
        else
        if($insert_afficheur_departement == FALSE)
        {
            $inserted_departement = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("afficheur_departement", $data), true); //afficheur_departement non insérée
        }
    }

    if($inserted_departement == TRUE)
        echoResponse(201, true, "departements ajoutes", NULL);
    else if($inserted_departement == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});