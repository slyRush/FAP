<?php
/**
 * Routes campagne manipulation - 'campagne_pays' table concerned
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
 * Affect campagne to pays
 * url - /campagnes_payss
 * method - POST
 * @params id_campagne, id_payss
 */
$app->post('/campagne_payss/:id_campagne', 'authenticate', function($id_campagne) use ($app, $db, $logManager) {
    verifyRequiredParams(array('payss_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_pays = FALSE;

    foreach ($request_params["payss_id"] as $pays)
    {
        $data = array(
            "campagne_id" => $id_campagne,
            "pays_id" => $pays["id"]
        );

        $insert_campagne_pays = $db->entityManager->campagne_pays()->insert($data);

        if($insert_campagne_pays != FALSE || is_array($insert_campagne_pays))
        {
            $inserted_pays = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("campagne_pays", $data), false); //campagne_pays insérée
        }
        else
        if($insert_campagne_pays == FALSE)
        {
            $inserted_pays = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("campagne_pays", $data), true); //campagne_pays non insérée
        }
    }

    if($inserted_pays == TRUE)
        echoResponse(201, true, "payss ajoutes", NULL);
    else if($inserted_pays == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});