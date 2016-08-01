<?php
/**
 * All function utils
 */

include_once "set_headers.php";
require_once dirname(__DIR__) . '/Log.class.php';

/**
 * Add key=>value into array after specific key
 * @param $arr
 * @param $key
 * @param $val
 * @param $index
 * @return array
 */
function insertKeyValuePairInArray($arr, $key, $val, $index){
    $arrayEnd = array_splice($arr, $index);
    $arrayStart = array_splice($arr, 0, $index);
    return (array_merge($arrayStart, array($key=>$val), $arrayEnd ));
}

/**
 * Replace key name in array
 * @param $array
 * @param $old_key
 * @param $new_key
 * @return array
 */
function replaceKeyNameInArray($array, $oldkey, $newkey)
{
    if( ! array_key_exists( $oldkey, $array ) )
        return $array;

    $keys = array_keys( $array );
    $keys[ array_search( $oldkey, $keys ) ] = $newkey;

    return array_combine( $keys, $array );
}

/**
 * Verify all requiered params send by POST
 * @param $required_fields
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = $_REQUEST;

    // Manipulation params de la demande PUT
    if ($_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'POST') {
        global $app;
        $request_params = json_decode($app->request()->getBody(), true);
    }
    foreach ($required_fields as $field) {
        //if(!is_array($request_params[$field])) $strlen_values_fields = strlen(trim($request_params[$field])) <= 0;
        if (!isset($request_params[$field])) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        //Champ (s) requis sont manquants ou vides, echo erreur JSON et d'arrêter l'application
        global $app;
        echoResponse(400, false, 'Champ(s) requis ' . substr($error_fields, 0, -2) . ' est (sont) manquant(s) ou vide(s)', NULL);
        $app->stop();
    }
}

/**
 * Validate E-mail address
 * @param $email
 */
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        global $app;
        echoResponse(400, false, "Adresse e-mail pas valide", NULL);
        $app->stop();
    }
}

/**
 * JSON Response
 * @param String $status_code  Code de réponse HTTP
 * @param Int $response response Json
 */
function echoResponse($status_code, $state, $message, $data) {
    global $app;

    $app->status($status_code); // Code de réponse HTTP

    $app->contentType('application/json'); // la mise en réponse type de contenu en JSON

    $response = array();
    $response["result"]["state"] = $state;
    $response["result"]["message"] = $message;
    $response["records"] = $data;

    echo utf8_encode(json_encode($response));
}

/**
 * JSON Response with records name option
 * @param String $status_code  Code de réponse HTTP
 * @param Int $response response Json
 */
function echoResponseWithRecordsName($status_code, $state, $message, $data, $recordName) {
    global $app;

    $app->status($status_code); // Code de réponse HTTP

    $app->contentType('application/json'); // la mise en réponse type de contenu en JSON

    $response = array();
    $response["result"]["state"] = $state;
    $response["result"]["message"] = $message;
    $response[$recordName] = $data;

    echo utf8_encode(json_encode($response));
}

/**
 * JSON Response with log insert option
 * @param String $status_code  Code de réponse HTTP
 * @param Int $response response Json
 */
function echoResponseWithLog($status_code, $state, $message, $data, $log = array()) {
    global $app;

    $app->status($status_code); // Code de réponse HTTP

    $app->contentType('application/json'); // la mise en réponse type de contenu en JSON

    $response = array();
    $response["result"]["state"] = $state;
    $response["result"]["message"] = $message;
    $response["records"] = $data;

    echo utf8_encode(json_encode($response));

    if(count($log) > 0) exceptionLog($log); else return; //Write into log
}

/**
 * Build message log
 * @param $user
 * @param $ressourceUri
 * @param $sql_query
 * @return mixed
 */
function buildMessageLog($user, $ressourceUri, $sql_query, $ip_request)
{
    //$message_log["user"] = is_null($user) ? NULL : array("id" => $user["id"], "nom&prenom" => $user["nom"] . $user["prenom"]);
    $message_log["user"] = is_null($user) ? NULL : array("id" => $user["idUser"], "nom&prenom" => $user["nom"] . $user["prenom"]);
    $message_log["ressource"] = $ressourceUri;
    $message_log["sql query"] = $sql_query;
    $message_log["IP request"] = $ip_request;

    return $message_log;
}

/**
 * Return message log with state
 * @param $message_log
 * @param $state
 * @return mixed
 */
function sendMessageLog($message_log, $state, $method)
{
    $message = $message_log;
    $message = insertKeyValuePairInArray($message, "error", $state, 0);
    $message = insertKeyValuePairInArray($message, "method", $method, 1);
    return $message;
}

/**
 * Write into log and return exception
 * @param  string $message
 * @return string
 */
function exceptionLog($message)
{
    $log = new Log();
    $log->write(utf8_encode(json_encode($message))); #Write into log
}

/**
 * Build insert sql query (string) - need on write into log
 * @param $nameTable
 * @param $values
 * @return string
 */
function buildSqlQueryInsert($nameTable, $values)
{
    $values_sql = "";
    foreach ($values as $key => $value) {
        $values_sql .= $value . ",";
    }
    $values_sql = rtrim($values_sql, ","); //delete last ','

    return "INSERT INTO $nameTable VALUES($values_sql)";

}

/**
 * Test if data are same on update
 * @param $table
 * @param $requestParams
 * @return array
 */
function isSameData($table, $requestParams)
{
    $testSameData = array();
    foreach($table as $k => $v)
    {
        if($k == 'id') continue;
        else
            if($requestParams[$k] == $v) $testSameData[] = "TRUE";
            else
                $testSameData[] = "FALSE";
    }
    return $testSameData;
}

/**
 * Remove all key not need by user role
 * @param $arrayObjectUser
 * @param $roleId
 * @return mixed
 */
function removeKeyByUser($arrayObjectUser, $roleId)
{
    switch($roleId)
    {
        case "1": //superadmin
        case "2": //admin
        {
            /** BEGIN: Remove no need column */
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "password_hash"); //remove password_hash column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "raison_sociale"); //remove raison_sociale column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "siret"); //remove siret column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "iban"); //remove iban column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "ville"); //remove ville column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "code_postal_facturation"); //remove code_postal_facturation column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "num_siret_fournisseur"); //remove num_siret_fournisseur column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "code_postal"); //remove code_postal column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "fournisseur_id"); //remove fournisseur_id column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "longitude"); //remove longitude column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "latitude"); //remove latitude column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "role_id"); //remove latitude column from $arrayObjectUser
            /** END */

            return $arrayObjectUser;
            break;
        }
        case "3": //client
        {
            /** BEGIN: Remove no need column */
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "password_hash"); //remove password_hash column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "raison_sociale"); //remove raison_sociale column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "siret"); //remove siret column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "iban"); //remove iban column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "ville"); //remove ville column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "code_postal_facturation"); //remove code_postal_facturation column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "num_siret_fournisseur"); //remove num_siret_fournisseur column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "code_postal"); //remove code_postal column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "fournisseur_id"); //remove fournisseur_id column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "longitude"); //remove longitude column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "latitude"); //remove latitude column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "role_id"); //remove latitude column from $arrayObjectUser
            /** END : Remove no need column */

            return $arrayObjectUser;
            break;
        }
        case "4": //supplier
        {
            /** BEGIN: Remove no need column */
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "password_hash"); //remove password_hash column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "code_postal_facturation"); //remove code_postal_facturation column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "fournisseur_id"); //remove fournisseur_id column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "num_siret_fournisseur"); //remove num_siret_fournisseur column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "code_postal"); //remove code_postal column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "longitude"); //remove longitude column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "latitude"); //remove latitude column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "role_id"); //remove latitude column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "prenom"); //remove prenom column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "url_photo"); //remove url_photo column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "raison_sociale"); //remove raison_sociale column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "ville"); //remove ville column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "api_key"); //remove api_key column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "date_creation"); //remove date_creation column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "iban"); //remove iban column from $arrayObjectUser
            /** END : Remove no need column */

            return $arrayObjectUser;
            break;
        }
        case "5": //displayer
        {
            /** BEGIN: Remove no need column */
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "raison_sociale"); //remove raison_sociale column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "siret"); //remove siret column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "iban"); //remove iban column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "ville"); //remove ville column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "code_postal_facturation"); //remove code_postal_facturation column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "fournisseur_id"); //remove code_postal_facturation column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "role_id"); //remove latitude column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "url_photo"); //remove url photo column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "num_siret_fournisseur"); //remove num siret fournisseur column from $arrayObjectUser
            $arrayObjectUser = JSON::removeNode($arrayObjectUser, "date_creation"); //remove date creation column from $arrayObjectUser
            $arrayObjectUser = replaceKeyNameInArray($arrayObjectUser, "id", "idUser"); //change id key to idUser
            $arrayObjectUser = replaceKeyNameInArray($arrayObjectUser, "email", "login"); //change email key to login
            $arrayObjectUser = replaceKeyNameInArray($arrayObjectUser, "code_postal", "codePostal"); //change code_postal key to codePostal
            $arrayObjectUser = replaceKeyNameInArray($arrayObjectUser, "longitude", "Longitude"); //change code_postal key to codePostal
            $arrayObjectUser = replaceKeyNameInArray($arrayObjectUser, "latitude", "Latitude"); //change code_postal key to codePostal
            $arrayObjectUser = replaceKeyNameInArray($arrayObjectUser, "password_hash", "password"); //change code_postal key to codePostal
            /** END : Remove no need column */

            return $arrayObjectUser;
            break;
        }
    }
}

/**
 * Set email path
 * @param $filepath
 * @return string
 */
function setEmail($filepath)
{
    $email_template = file_get_contents($filepath);
    return $email_template;
}

/**
 * Change '' to ' in mail confirmation
 *
 * @param string $data
 *
 * @return string
 */
function mssql_escape_for_mail($data) {
    return str_replace("''", "'", $data);
}