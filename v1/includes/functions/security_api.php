<?php
/**
 * Tous les fonctions pour sécuriser le WS avec un API Key : clé API obligatoire avant de pouvoir consommer les ressources après authentification
 */

include_once "set_headers.php";

require_once dirname(__DIR__)  . '/db_manager/dbManager.php';

/**
 * Génération aléatoire unique MD5 String pour utilisateur clé Api
 */
function generateApiKey()
{
    return md5(uniqid(rand(), true));
}

/**
 * Ajout de Couche intermédiaire pour authentifier chaque demande
 * Vérifier si la demande a clé API valide dans l'en-tête "Authorization"
 */
function authenticate() {
    $headers = apache_request_headers(); // Obtenir les en-têtes de requêtes

    // Vérification de l'en-tête d'autorisation
    if (isset($headers['Authorization'])) {
        $db = new DBManager();

        $api_key = $headers['Authorization']; // Obtenir la clé d'api dans le header

        $isValidApiKey = $db->entityManager->author("api_key = ?", $api_key)->fetch();

        if ($isValidApiKey == FALSE) // Valider la clé API - existe dans la base
        {
            global $app;
            echoResponse(401, false, "Accés Refusé. Clé API invalide", NULL);
            $app->stop();
        }
        else
        if ($isValidApiKey != FALSE)
        {
            global $user_id, $user_connected;
            $user_id = $isValidApiKey["id"]; // Obtenir l'ID utilisateur (clé primaire)
            $user_connected = $isValidApiKey;
        }
    }
    else
    {
        // Clé API est absente dans la en-tête
        global $app;
        echoResponse(400, false, "Vous ne pouvez pas accéder à cette ressource. Clé API absente dans l'en-tête", NULL);
        $app->stop();
    }
}