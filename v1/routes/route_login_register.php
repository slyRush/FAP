<?php
/**
 * Routes login manipulation - 'users' table concerned
 * ----------- METHOD no need authentification---------------------------------
 */

include_once dirname(__DIR__)  . '/includes/functions/set_headers.php';

require_once dirname(__DIR__)  . '/includes/functions/utils.php';
require_once dirname(__DIR__)  . '/includes/functions/json.php';
require_once dirname(__DIR__)  . '/includes/functions/security_api.php';
require_once dirname(__DIR__)  . '/includes/db_manager/dbManager.php';
require_once dirname(__DIR__)  . '/includes/pass_hash.php';
require_once dirname(__DIR__)  . '/includes/Log.class.php';
require_once dirname(__DIR__)  . '/includes/const.php';

global $app;
$db = new DBManager();
$logManager = new Log();

/**
 * Login User
 * url - /login
 * method - POST
 * @params email, password
 */
$app->post('/login', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('email','password')); // vérifier les paramètres requises

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"]; //$app->request()->post('password');
    $password = $request_params["password"]; //$app->request()->post('email');

    validateEmail($email); // valider l'adresse email

    $utilisateur_query = $db->entityManager->utilisateur("email = ?", $email);
    $utilisateur = $utilisateur_query->fetch();

    if( $utilisateur != FALSE ) //false si l'email de l'utilisateur n'est pas trouvé
    {
        if (PassHash::check_password($utilisateur['password_hash'], $password))
        {
            $utilisateur = JSON::removeNode($utilisateur, "password_hash"); //remove password_hash column
            if($utilisateur["statut"] == 1) //user active
            {
                $role = $db->entityManager->role_utilisateur("niveau = ?", $utilisateur['role_utilisateur_id'])->fetch();
                $utilisateur_addinfo = $db->entityManager->$role["role"]("utilisateur_id = ?", $utilisateur['id'])->fetch();

                foreach ($utilisateur_addinfo as $k => $v) {
                    $utilisateur[$k] = $v;
                }

                $utilisateur = JSON::removeNode($utilisateur, "utilisateur_id");

                $logManager->setLog($utilisateur, (string)$utilisateur_query, false);
                echoResponseWithRecordsName(200, true, "Connexion réussie", $utilisateur, "user"); // Mot de passe utilisateur est correcte
            }
            else
            {
                $logManager->setLog($utilisateur, (string)$utilisateur_query, true);
                echoResponseWithRecordsName(200, true, "Votre compte a été désactivé", NULL, "user");
            }
        }
        else
        {
            $logManager->setLog($utilisateur, (string)$utilisateur_query, true);
            echoResponseWithRecordsName(200, false, "Mot de passe incorrecte", NULL, "user"); // erreur inconnue est survenue
        }
    }
    else
    {
        $logManager->setLog($utilisateur, (string)$utilisateur_query, true);
        echoResponseWithRecordsName(200, false, "Echec de la connexion. identificateurs incorrectes", NULL, "user"); // identificateurs de l'utilisateur sont erronés
    }
});

/**
* Register user
* url - /register
* methode - POST
* params - name, email, password
*/
$app->post('/register', function() use ($app, $db, $logManager) {
    verifyRequiredParams(array('nom', 'prenom', 'email', 'telephone', 'url_photo', 'password')); // vérifier les paramédtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $name = $request_params['name'];
    $email = $request_params['email'];
    $password = $request_params['password'];

    validateEmail($email); //valider adresse email

    $utilisateur_exist_query = $db->entityManager->utilisateur("email = ?", $email);
    $utilisateur_exist = $db->entityManager->utilisateur("email = ?", $email)->fetch();

    if($utilisateur_exist == FALSE) //email utilisateur doesn't exist
    {
        $data = array(
            "name"              => $name,
            "email"             => $email,
            "api_key"           => generateApiKey(), // Générer API key
            "password_hash"     => PassHash::hash($password), //Générer un hash de mot de passe
        );

        $insert_utilisateur = $db->entityManager->utilisateur()->insert($data);

        if($insert_utilisateur == FALSE)
        {
            $logManager->setLog(null, (string)$utilisateur_exist_query . " / " . buildSqlQueryInsert("utilisateur", $data), true);
            echoResponse(400, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
        }
        else
        {
            if($insert_utilisateur != FALSE || is_array($insert_utilisateur))
            {
                $logManager->setLog(null, (string)$utilisateur_exist_query . " / " . buildSqlQueryInsert("utilisateur", $data), false);
                echoResponse(201, true, "Author inscrit avec succès", $insert_utilisateur);
            }
        }
    }
    else
    {
        if($utilisateur_exist != FALSE || count($utilisateur_exist) > 1)
        {
            $logManager->setLog(null, (string)$utilisateur_exist_query, false);
            echoResponse(400, false, "Désolé, cet E-mail éxiste déja", NULL);
        }
    }
});