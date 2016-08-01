<?php
/**
 * Routes type manipulation - 'type_adresse' table concerned
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
 * Affect type to adresse
 * url - /types_adresses
 * method - POST
 * @params id_type, id_adresses
 */
$app->post('/type_adresses/:id_type', 'authenticate', function($id_type) use ($app, $db, $logManager) {
    verifyRequiredParams(array('adresses_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_adresse = FALSE;

    foreach ($request_params["adresses_id"] as $adresse)
    {
        $data = array(
            "type_id" => $id_type,
            "adresse_id" => $adresse["id"]
        );

        $insert_type_adresse = $db->entityManager->type_adresse()->insert($data);

        if($insert_type_adresse != FALSE || is_array($insert_type_adresse))
        {
            $inserted_adresse = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("type_adresse", $data), false); //type_adresse insérée
        }
        else
        if($insert_type_adresse == FALSE)
        {
            $inserted_adresse = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("type_adresse", $data), true); //type_adresse non insérée
        }
    }

    if($inserted_adresse == TRUE)
        echoResponse(201, true, "adresses ajoutes", NULL);
    else if($inserted_adresse == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});