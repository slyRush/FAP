<?php
/**
 * Routes role manipulation - 'role' table concerned
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
 * Get all role
 * url - /roles
 * method - GET
 */
$app->get('/roles', 'authenticate', function() use ($app, $db, $logManager) {
    $roles = $db->entityManager->role();
    $roles_array = JSON::parseNotormObjectToArray($roles);
    global $user_connected;

    if(count($roles_array) > 0)
    {
        $data_roles = array();
        foreach ($roles as $role) array_push($data_roles, $role);

        $logManager->setLog($user_connected, (string)$roles, false);
        echoResponse(200, true, "Tous les roles retournés", $data_roles);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$roles, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one role by id
* url - /roles/:id
* method - GET
*/
$app->get('/roles/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $role = $db->entityManager->role[$id];
    global $user_connected;

    if(count($role) > 0)
    {
        $logManager->setLog($user_connected, (string)$role, false);
        echoResponse(200, true, "role retourné(e)", $role);
    }
    else
    {
        $logManager->setLog($user_connected, (string)$role, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new role
* url - /roles/
* method - POST
* @params name
*/
$app->post('/roles', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('titre','niveau')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_role = $request_params["name"];

    $data = array(
        "name" => $name_role
    );

    $insert_role = $db->entityManager->role()->insert($data);

    if($insert_role == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("role", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du role", NULL);
    }
    else
    if($insert_role != FALSE || is_array($insert_role))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("role", $data), false);
        echoResponse(201, true, "role ajouté(e) avec succès", $insert_role);
    }
});

/**
* Update one role
* url - /roles/:id
* method - PUT
* @params name
*/
$app->put('/roles/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('titre','niveau')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_role = $request_params["name"];

    $role = $db->entityManager->role[$id];
    if($role)
    {
        $testSameData = isSameData($role, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)$role, false);
            echoResponse(200, true, "role mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_role = $role->update(array("name" => $name_role));

            if($update_role == FALSE)
            {
                $logManager->setLog($user_connected, (string)$role, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du role", NULL);
            }
            else
            if($update_role != FALSE || is_array($update_role))
            {
                $logManager->setLog($user_connected, (string)$role, false);
                echoResponse(201, true, "role mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$role, true);
        echoResponse(400, false, "role inexistant !!", NULL);
    }

});

/**
* Delete one role
* url - /roles/:id
* method - DELETE
* @params name
*/
$app->delete('/roles/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    $role = $db->entityManager->role[$id];
    global $user_connected;

    if($db->entityManager->application_role("role_id", $id)->delete())
    {
        if($role && $role->delete())
        {
            $logManager->setLog($user_connected, (string)$role, false);
            echoResponse(200, true, "role id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)$role, true);
            echoResponse(200, false, "role id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)$role, true);
        echoResponse(400, false, "Erreur lors de la suppression de la role ayant l'id $id : role inexistant !!", NULL);
    }
});