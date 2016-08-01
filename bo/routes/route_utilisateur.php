<?php
/**
 * Routes user manipulation - 'user' table concerned
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
 * Get all users - filter by role
 * url - /users
 * method - GET
 */
$app->get('/users', 'authenticate', function() use ($app, $db, $logManager) {
    global $userConnected;

    $role_id = $userConnected['role_id'];

    switch($role_id)
    {
        case "1" : //Superadmin -- can access to admin, client, supplier, and displayer list
        {
            $users = $db->entityManager->utilisateur();
            $usersArray = JSON::parseNotormObjectToArray($users);

            if(count($usersArray) > 0)
            {
                $dataUser = array();

                //foreach ($users as $user) array_push($dataUser, JSON::removeNode($user, "password_hash"));
                foreach ($users as $user) array_push($dataUser, removeKeyByUser($user, $user['role_id']));

                $logManager->setLog($userConnected, (string)$users, false);
                echoResponseWithRecordsName(200, true, "All user returned", $dataUser, "users");
            }
            else
            {
                $logManager->setLog($userConnected, (string)$users, false);
                echoResponse(400, true, "Error! May be there are not users in DB.", NULL);
            }

            break;
        }
        case "2" : //Admin -- can access client, supplier, and displayer list
        {
            $users = $db->entityManager->utilisateur("role_id", array(3, 4, 5));
            $usersArray = JSON::parseNotormObjectToArray($users);

            if(count($usersArray) > 0)
            {
                $dataUser = array();

                //foreach ($users as $user) array_push($dataUser, JSON::removeNode($user, "password_hash"));
                foreach ($users as $user) array_push($dataUser, removeKeyByUser($user, $user['role_id']));

                $logManager->setLog($userConnected, (string)$users, false);
                echoResponseWithRecordsName(200, true, "All user returned", $dataUser, "users");
            }
            else
            {
                $logManager->setLog($userConnected, (string)$users, false);
                echoResponse(400, true, "Error! May be there are not users in DB.", NULL);
            }

            break;
        }
        case "3" : //Client -- can access to supplier, and displayer list
        {
            echoResponse(401, true, "You're not allowed to this ressource", NULL);
            break;
        }
        case "4" : //Fournisseur -- can access to displayer list
        {
            $users = $db->entityManager->utilisateur("role_id", 5);
            $usersArray = JSON::parseNotormObjectToArray($users);

            if(count($usersArray) > 0)
            {
                $dataUser = array();

                //foreach ($users as $user) array_push($dataUser, JSON::removeNode($user, "password_hash"));
                foreach ($users as $user) array_push($dataUser, removeKeyByUser($user, $user['role_id']));

                $logManager->setLog($userConnected, (string)$users, false);
                echoResponseWithRecordsName(200, true, "All user returned", $dataUser, "users");
            }
            else
            {
                $logManager->setLog($userConnected, (string)$users, false);
                echoResponse(400, true, "Error! May be there are not users in DB.", NULL);
            }

            break;
        }
        case "5" : //Afficheur -- can't access to list
        {
            echoResponse(401, true, "You're not allowed to this ressource", NULL);
            break;
        }
    }

    foreach ($userConnected as $user) {

    }

});

/**
* Get one user by id
* url - /users/:id
* method - GET
*/
$app->get('/users/:id', 'authenticate', function($id) use ($app, $db, $logManager) {
    global $userConnected;

    $role_id = $userConnected['role_id'];

    switch($role_id)
    {
        case "1": //superadmin -- can access admin, client, supplier, and displayer list
        {
            $users = $db->entityManager->user[$id];

            if(count($users) > 0)
            {
                $logManager->setLog($userConnected, (string)$users, false);
                echoResponse(200, true, "User returned", $users);
            }
            else
            {
                $logManager->setLog($userConnected, (string)$users, false);
                echoResponse(400, true, "Error, can't fetch user.", NULL);
            }

            break;
        }
        case "2": //admin -- can access client, supplier, and displayer list
        {
            $users = $db->entityManager->user[$id];

            if(count($users) > 0 && in_array($users['role_id'], array(3, 4, 5)))
            {
                $logManager->setLog($userConnected, (string)$users, false);
                echoResponse(200, true, "User returned", $users);
            }
            else
            {
                $logManager->setLog($userConnected, (string)$users, false);
                echoResponse(400, true, "Error, can't fetch user.", NULL);
            }

            break;
        }
        case "3": //client -- can't access
        {
            echoResponse(401, true, "You're not allowed to this ressource", NULL);
            break;
        }
        case "4": //fournisseur -- can access client, supplier, and displayer list
        {
            $users = $db->entityManager->user[$id];

            if(count($users) > 0 && $users['role_id'] == 5)
            {
                $logManager->setLog($userConnected, (string)$users, false);
                echoResponse(200, true, "User returned", $users);
            }
            else
            {
                $logManager->setLog($userConnected, (string)$users, false);
                echoResponse(400, true, "Error, can't fetch user.", NULL);
            }

            break;
        }
        case "5": //afficheur -- can access client, supplier, and displayer list
        {
            echoResponse(401, true, "You're not allowed to this ressource", NULL);
            break;
        }
    }

});