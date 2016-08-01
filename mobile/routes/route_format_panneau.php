<?php
/**
 * Routes format manipulation - 'format_panneau' table concerned
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
 * Affect format to panneau
 * url - /formats_panneaus
 * method - POST
 * @params id_format, id_panneaus
 */
$app->post('/format_panneaus/:id_format', 'authenticate', function($id_format) use ($app, $db, $logManager) {
    verifyRequiredParams(array('panneaus_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_panneau = FALSE;

    foreach ($request_params["panneaus_id"] as $panneau)
    {
        $data = array(
            "format_id" => $id_format,
            "panneau_id" => $panneau["id"]
        );

        $insert_format_panneau = $db->entityManager->format_panneau()->insert($data);

        if($insert_format_panneau != FALSE || is_array($insert_format_panneau))
        {
            $inserted_panneau = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("format_panneau", $data), false); //format_panneau insérée
        }
        else
        if($insert_format_panneau == FALSE)
        {
            $inserted_panneau = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("format_panneau", $data), true); //format_panneau non insérée
        }
    }

    if($inserted_panneau == TRUE)
        echoResponse(201, true, "panneaus ajoutes", NULL);
    else if($inserted_panneau == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});