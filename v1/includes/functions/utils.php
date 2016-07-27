<?php
/**
 * Tous les fonctions utiles
 */

include_once "set_headers.php";
require_once dirname(__DIR__) . '/Log.class.php';

/**
 * Ajouter une paire [key] => [value] après un Key spécifique
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
 * Vérification les params nécessaires posté ou non
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
 * Validation adresse e-mail
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
 * Faisant écho à la réponse JSON au client
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
 * Faisant écho à la réponse JSON au client avec un recordname indiqué
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
 * Faisant écho à la réponse JSON au client et écriture dans le log
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
 * Constuire le message de log
 * @param $user
 * @param $ressourceUri
 * @param $sql_query
 * @return mixed
 */
function buildMessageLog($user, $ressourceUri, $sql_query, $ip_request)
{
    $message_log["user"] = is_null($user) ? NULL : array("id" => $user["id"], "nom" => $user["nom"]);
    $message_log["ressource"] = $ressourceUri;
    $message_log["sql query"] = $sql_query;
    $message_log["IP request"] = $ip_request;

    return $message_log;
}

/**
 * Renvoyé message avec l'état
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
 * Ecrire dans le log et retourné les excéptions
 *
 * @param  string $message
 * @return string
 */
function exceptionLog($message)
{
    $log = new Log();
    $log->write(utf8_encode(json_encode($message))); #Write into log
}

/**
 * Construire la requete SQL insertion - utile pour l'écriture dans le log
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
 * Tester si les données sont les mêmes lors de l'uptade
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