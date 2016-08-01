<?php
/**
 * Routes photo manipulation - 'photo_panneau' table concerned
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
 * Affect photo to panneau
 * url - /photos_panneaus
 * method - POST
 * @params id_photo, id_panneaus
 */
$app->post('/photo_panneaus/:id_photo', 'authenticate', function($id_photo) use ($app, $db, $logManager) {
    verifyRequiredParams(array('panneaus_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_panneau = FALSE;

    foreach ($request_params["panneaus_id"] as $panneau)
    {
        $data = array(
            "photo_id" => $id_photo,
            "panneau_id" => $panneau["id"]
        );

        $insert_photo_panneau = $db->entityManager->photo_panneau()->insert($data);

        if($insert_photo_panneau != FALSE || is_array($insert_photo_panneau))
        {
            $inserted_panneau = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("photo_panneau", $data), false); //photo_panneau insérée
        }
        else
        if($insert_photo_panneau == FALSE)
        {
            $inserted_panneau = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("photo_panneau", $data), true); //photo_panneau non insérée
        }
    }

    if($inserted_panneau == TRUE)
        echoResponse(201, true, "panneaus ajoutes", NULL);
    else if($inserted_panneau == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});