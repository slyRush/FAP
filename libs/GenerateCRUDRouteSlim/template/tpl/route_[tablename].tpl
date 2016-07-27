<?php
/**
 * Routes {$table_name} manipulation - '{$table_name}' table concerned
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
 * Get all {$table_name}
 * url - /{$table_name}s
 * method - GET
 */
$app->get('/{$table_name}s', 'authenticate', function() use ($app, $db, $logManager) {
    ${$table_name}s = $db->entityManager->{$table_name}();
    ${$table_name}s_array = JSON::parseNotormObjectToArray(${$table_name}s);
    global $user_connected;

    if(count(${$table_name}s_array) > 0)
    {
        $data_{$table_name}s = array();
        foreach (${$table_name}s as ${$table_name}) array_push($data_{$table_name}s, ${$table_name});

        $logManager->setLog($user_connected, (string)${$table_name}s, false);
        echoResponse(200, true, "Tous les {$table_name}s retournés", $data_{$table_name}s);
    }
    else
    {
        $logManager->setLog($user_connected, (string)${$table_name}s, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Get one {$table_name} by id
* url - /{$table_name}s/:id
* method - GET
*/
$app->get('/{$table_name}s/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    ${$table_name} = $db->entityManager->{$table_name}[$id];
    global $user_connected;

    if(count(${$table_name}) > 0)
    {
        $logManager->setLog($user_connected, (string)${$table_name}, false);
        echoResponse(200, true, "{$table_name} retourné(e)", ${$table_name});
    }
    else
    {
        $logManager->setLog($user_connected, (string)${$table_name}, true);
        echoResponse(400, false, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new {$table_name}
* url - /{$table_name}s/
* method - POST
* @params name
*/
$app->post('/{$table_name}s', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('{$required_params}')); // vérifier les paramédtres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_{$table_name} = $request_params["name"];

    $data = array(
        "name" => $name_{$table_name}
    );

    $insert_{$table_name} = $db->entityManager->{$table_name}()->insert($data);

    if($insert_{$table_name} == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("{$table_name}", $data), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du {$table_name}", NULL);
    }
    else
    if($insert_{$table_name} != FALSE || is_array($insert_{$table_name}))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("{$table_name}", $data), false);
        echoResponse(201, true, "{$table_name} ajouté(e) avec succès", $insert_{$table_name});
    }
});

/**
* Update one {$table_name}
* url - /{$table_name}s/:id
* method - PUT
* @params name
*/
$app->put('/{$table_name}s/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('{$required_params}')); // vérifier les paramètres requises
    global $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $name_{$table_name} = $request_params["name"];

    ${$table_name} = $db->entityManager->{$table_name}[$id];
    if(${$table_name})
    {
        $testSameData = isSameData(${$table_name}, $request_params);

        if(!in_array("FALSE", $testSameData)) //c'est la même data, pas de changement
        {
            $logManager->setLog($user_connected, (string)${$table_name}, false);
            echoResponse(200, true, "{$table_name} mis à jour avec succès. Id : $id", NULL);
        }
        else
        {
            $update_{$table_name} = ${$table_name}->update(array("name" => $name_{$table_name}));

            if($update_{$table_name} == FALSE)
            {
                $logManager->setLog($user_connected, (string)${$table_name}, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du {$table_name}", NULL);
            }
            else
            if($update_{$table_name} != FALSE || is_array($update_{$table_name}))
            {
                $logManager->setLog($user_connected, (string)${$table_name}, false);
                echoResponse(201, true, "{$table_name} mis à jour avec succès", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)${$table_name}, true);
        echoResponse(400, false, "{$table_name} inexistant !!", NULL);
    }

});

/**
* Delete one {$table_name}
* url - /{$table_name}s/:id
* method - DELETE
* @params name
*/
$app->delete('/{$table_name}s/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    ${$table_name} = $db->entityManager->{$table_name}[$id];
    global $user_connected;

    if($db->entityManager->application_{$table_name}("{$table_name}_id", $id)->delete())
    {
        if(${$table_name} && ${$table_name}->delete())
        {
            $logManager->setLog($user_connected, (string)${$table_name}, false);
            echoResponse(200, true, "{$table_name} id : $id supprimé avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)${$table_name}, true);
            echoResponse(200, false, "{$table_name} id : $id pas supprimé. Erreur !!", NULL);
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)${$table_name}, true);
        echoResponse(400, false, "Erreur lors de la suppression de la {$table_name} ayant l'id $id : {$table_name} inexistant !!", NULL);
    }
});