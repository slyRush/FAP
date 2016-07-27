<?php
/**
 * Routes role manipulation - 'role_utilisateur' table concerned
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
 * Affect role to utilisateur
 * url - /roles_utilisateurs
 * method - POST
 * @params id_role, id_utilisateurs
 */
$app->post('/role_utilisateurs/:id_role', 'authenticate', function($id_role) use ($app, $db, $logManager) {
    verifyRequiredParams(array('utilisateurs_id')); // vérifier les paramétres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);

    $inserted_utilisateur = FALSE;

    foreach ($request_params["utilisateurs_id"] as $utilisateur)
    {
        $data = array(
            "role_id" => $id_role,
            "utilisateur_id" => $utilisateur["id"]
        );

        $insert_role_utilisateur = $db->entityManager->role_utilisateur()->insert($data);

        if($insert_role_utilisateur != FALSE || is_array($insert_role_utilisateur))
        {
            $inserted_utilisateur = TRUE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("role_utilisateur", $data), false); //role_utilisateur insérée
        }
        else
        if($insert_role_utilisateur == FALSE)
        {
            $inserted_utilisateur = FALSE;
            $logManager->setLog($user_connected, buildSqlQueryInsert("role_utilisateur", $data), true); //role_utilisateur non insérée
        }
    }

    if($inserted_utilisateur == TRUE)
        echoResponse(201, true, "utilisateurs ajoutes", NULL);
    else if($inserted_utilisateur == FALSE)
        echoResponse(400, false, "Erreur ajout", NULL);
});