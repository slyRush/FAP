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
 * Get all {$table_name}s
 * url - /{$table_name}s
 * method - GET
 */
$app->get('/{$table_name}s', 'authenticate', function() use ($app, $db, $logManager) {
    global $user_id, $user_connected;

    ${$table_name}s = $db->entityManager->{$table_name}("author_id", $user_id);
    ${$table_name}s_array = JSON::parseNotormObjectToArray(${$table_name}s);

    if(count(${$table_name}s_array) > 0)
    {
        $data_{$table_name}s = array();
        foreach (${$table_name}s as ${$table_name})
        {
            $data_{$table_name_affected} = array();
            foreach (${$table_name}->{$table_name}_{$table_name_affected}() as ${$table_name}_{$table_name_affected})
            {
                array_push($data_{$table_name_affected}, array("id" => ${$table_name}_{$table_name_affected}->{$table_name_affected}["id"], "name" => ${$table_name}_{$table_name_affected}->{$table_name_affected}["name"]));
            }
            ${$table_name} = JSON::parseNotormObjectToArray(${$table_name}); //parse {$table_name} to array
            ${$table_name}["{$table_name_affected}s"] = $data_{$table_name_affected}; //add {$table_name_affected}s from array

            array_push($data_{$table_name}s, ${$table_name});
        }
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
    global $user_connected;
    ${$table_name} = $db->entityManager->{$table_name}[$id];

    if(count(${$table_name}) > 0)
    {
        $logManager->setLog($user_connected, (string)${$table_name}, false);
        echoResponse(200, true, "{$table_name} est retourné", ${$table_name});
    }
    else
    {
        $logManager->setLog($user_connected, (string)${$table_name}, false);
        echoResponse(400, true, "Une erreur est survenue.", NULL);
    }
});

/**
* Create new {$table_name}
* url - /{$table_name}s
* method - POST
* @params title, web, slogan
*/
$app->post('/{$table_name}s', 'authenticate', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('{$required_params}')); // vérifier les paramétres requises
    global $user_id, $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $request_params = insertKeyValuePairInArray($request_params, "author_id", $user_id, 0); //add key author_id to array params send to post, value equals to current $user_id
    $request_params = insertKeyValuePairInArray($request_params, "maintainer_id", $user_id, 1); //add key maintainer_id to array params send to post, value equals to current $user_id

    $insert_{$table_name} = $db->entityManager->{$table_name}()->insert($request_params);

    if($insert_{$table_name} == FALSE)
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("{$table_name}", $request_params), true);
        echoResponse(400, false, "Oops! Une erreur est survenue lors de l'insertion du {$table_name}", NULL);
    }
    else
    if($insert_{$table_name} != FALSE || is_array($insert_{$table_name}))
    {
        $logManager->setLog($user_connected, buildSqlQueryInsert("{$table_name}", $request_params), false);
        echoResponse(201, true, "{$table_name} ajoutée avec succès", $insert_{$table_name});
    }
});

/**
* Update one {$table_name}
* url - /{$table_name}s/:id
* method - PUT
* @params title, web, slogan
*/
$app->put('/{$table_name}s/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    verifyRequiredParams(array('{$required_params}')); // vérifier les paramétres requises
    global $user_id, $user_connected;

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $request_params = insertKeyValuePairInArray($request_params, "author_id", $user_id, 0); //add key author_id to array params send to post, value equals to current $user_id
    $request_params = insertKeyValuePairInArray($request_params, "maintainer_id", $user_id, 1); //add key maintainer_id to array params send to post, value equals to current $user_id

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
            $update_{$table_name} = ${$table_name}->update($request_params);

            if($update_{$table_name} == FALSE)
            {
                $logManager->setLog($user_connected, (string)${$table_name}, true);
                echoResponse(400, false, "Oops! Une erreur est survenue lors de la mise à jour du {$table_name}", NULL);
            }
            else
            if($update_{$table_name} != FALSE || is_array($update_{$table_name}))
            {
                $logManager->setLog($user_connected, (string)${$table_name}, false);
                echoResponse(200, true, "{$table_name} mis à jour avec succès. Id : $id", NULL);
            }
        }
    }
    else
    {
        $logManager->setLog($user_connected, (string)${$table_name}, true);
        echoResponse(400, false, "Tag inexistant !!", NULL);
    }
});

/**
* Delete an {$table_name}, need to delete from association table first
* url - /{$table_name}s/:id
* method - DELETE
* @params name
*/
$app->delete('/{$table_name}s/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    global $user_connected;
    ${$table_name} = $db->entityManager->{$table_name}[$id];

    ${$table_name}_{$table_name_affected} = $db->entityManager->{$table_name}_{$table_name_affected}("{$table_name}_id", $id)->fetch();

    if(${$table_name}_{$table_name_affected} != FALSE)
    {
        if($db->entityManager->{$table_name}_{$table_name_affected}("{$table_name}_id", $id)->delete())
        {
            if(${$table_name} && ${$table_name}->delete())
            {
                $logManager->setLog($user_connected, (string)${$table_name}, false);
                echoResponse(200, true, "{$table_name} id : $id supprimée avec succès", NULL);
            }
            else
            {
                $logManager->setLog($user_connected, (string)${$table_name}, true);
                echoResponse(200, false, "{$table_name} id : $id n'a pa pu être supprimée", NULL);
            }
        }
        else
        {
            $logManager->setLog($user_connected, (string)${$table_name}, true);
            echoResponse(400, false, "Erreur lors de la suppression de la {$table_name} ayant l'id $id !!", NULL);
        }
    }
    else if(${$table_name}_{$table_name_affected} == FALSE)
    {
        if(${$table_name} && ${$table_name}->delete())
        {
            $logManager->setLog($user_connected, (string)${$table_name}, false);
            echoResponse(200, true, "{$table_name} id : $id supprimée avec succès", NULL);
        }
        else
        {
            $logManager->setLog($user_connected, (string)${$table_name}, true);
            echoResponse(200, false, "{$table_name} id : $id n'a pa pu être supprimée", NULL);
        }
    }
});