<?php

/*****************************************************************************************/

require_once('template/class/dao/sql/Connection.class.php');
require_once('template/class/dao/sql/ConnectionFactory.class.php');
require_once('template/class/dao/sql/ConnectionProperty.class.php');
require_once('template/class/dao/sql/QueryExecutor.class.php');
require_once('template/class/dao/sql/Transaction.class.php');
require_once('template/class/dao/sql/SqlQuery.class.php');
require_once('template/class/Template.php');
require_once('template/class/dao/config.php');

/**
 * Run generate routes
 * @throws Exception
 */
function generate()
{
    init();
    $sql = 'SHOW TABLES';
    $ret = QueryExecutor::execute(new SqlQuery($sql));
    generateAllRoutesFiles($ret);
}

/**
 * Init function, create generated folder
 */
function init()
{
    @mkdir("generated");
    @mkdir("generated/routes");

    /** Auto generate ' . WSVERSION . ' */
    @mkdir("../../" . WSVERSION);
    @mkdir("../../" . WSVERSION . "/routes");
    @mkdir("../../" . WSVERSION . "/includes");
    @mkdir("../../" . WSVERSION . "/includes/db_manager");
    @mkdir("../../" . WSVERSION . "/includes/functions");

    copy("toInclude/includes/db_manager/dbManager.php", "../../" . WSVERSION . "/includes/db_manager/dbManager.php");
    copy("toInclude/includes/functions/json.php", "../../" . WSVERSION . "/includes/functions/json.php");
    copy("toInclude/includes/functions/security_api.php", "../../" . WSVERSION . "/includes/functions/security_api.php");
    copy("toInclude/includes/functions/set_headers.php", "../../" . WSVERSION . "/includes/functions/set_headers.php");
    copy("toInclude/includes/functions/utils.php", "../../" . WSVERSION . "/includes/functions/utils.php");
    copy("toInclude/includes/Log.class.php", "../../" . WSVERSION . "/includes/Log.class.php");
    copy("toInclude/includes/pass_hash.php", "../../" . WSVERSION . "/includes/pass_hash.php");


    copy("toInclude/.htaccess", "../../" . WSVERSION . "/.htaccess");
    copy("toInclude/index.php", "../../" . WSVERSION . "/index.php");
}

/**
 * Test if table contains primary key
 * @param $row
 * @return bool
 */
function doesTableContainPK($row)
{
    $row = getFields($row[0]);
    for($j=0; $j<count($row); $j++)
    {
        if($row[$j][3]=='PRI') return true;
    }
    return false;
}

/**
 * Test if column is type number or like
 * @param $columnType
 * @return bool
 */
function isColumnTypeNumber($columnType)
{
    if(strtolower(substr($columnType,0,3)) == 'int' || strtolower(substr($columnType,0,7)) == 'tinyint')
    {
        return true;
    }
    return false;
}

/**
 * Get all fields in table
 * @param $table
 * @return all name tables
 * @throws Exception
 */
function getFields($table)
{
    $sql = 'DESC '.$table;
    return QueryExecutor::execute(new SqlQuery($sql));
}

/**
 * Test if table is a user table
 *
 * @return array
 */
function getTableUser()
{
    $allTableName = getAllTableName();
    $allUserTable = array();

    foreach ($allTableName as $tableName)
    {
        if($tableName == 'panneau') continue;
        $fields = getFields($tableName[0]);

        foreach ($fields as $field)
        {
            if($field["Field"] == "api_key" || $field["Field"] == "apiKey" || $field["Field"] == "user_id" || $field["Field"] == "utilisateur_id")
            {
                array_push($allUserTable, $tableName[0]);
            }
        }
    }

    return $allUserTable;
}

/**
 * Get all params need to post
 * @param $tableName
 * @return string
 */
function getFieldsParams($tableName)
{
    $allFields = getFields($tableName);
    $champs = "";
    foreach ($allFields as $champ) {
        if(($champ["Key"] == "PRI" && $champ["Extra"] == "auto_increment") || $champ["Key"] == "PRI" || $champ["Default"] != NULL || ($champ["Key"] == "MUL" && isColumnTypeNumber($champ["Type"]) == TRUE)) continue; //si c'est un clé primaire ou auto_increment ou un champ de type number ou un index
        else
            $champs .= $champ["Field"] . "','";
    }
    $champs = rtrim($champs, "'");
    $champs = rtrim($champs, ",");
    $champs = rtrim($champs, "'");

    return $champs;
}

/**
 * Get all table name
 * @throws Exception
 */
function getAllTableName()
{
    $result = array();
    $sql = 'SHOW TABLES';
    $allTableName = QueryExecutor::execute(new SqlQuery($sql));

    foreach ($allTableName as $tableName)
    {
        array_push($result, $tableName);
    }

    return $result;
}

/**
 * Get all table name in database indicated
 * @return array
 * @throws Exception
 */
function buildTableNameWithAssociation()
{
    $result = array();
    $sql = 'SHOW TABLES';
    $allTableName = QueryExecutor::execute(new SqlQuery($sql));

    foreach ($allTableName as $tableName)
    {
        if($tableName == "fournisseur_adresse_livraison" || $tableName == "bon_commande" || $tableName == "etat_panneau" || $tableName == "format_panneau" || $tableName == "photo_panneau" || $tableName == "raison_signalement" || $tableName == "role_utilisateur" || $tableName == "zone_affichage") continue;

        if(strpos($tableName[0], "_") !== FALSE)
        {
            $tableNameExplode = explode("_", $tableName[0]);

            if(count($tableNameExplode) == 2)
            {
                $tableNameAssociation = array(
                    $tableNameExplode[0] => $tableNameExplode[1]
                );
                $result[] = $tableNameAssociation;
            }
            else
            if(count($tableNameExplode) == 3)
            {
                if($tableName == "bon_commande_departement")
                {
                    $tableNameAssociation = array(
                        $tableNameExplode[0] . "_" . $tableNameExplode[1] => $tableNameExplode[2]
                    );
                    $result[] = $tableNameAssociation;
                }

                $tableNameAssociation = array(
                    $tableNameExplode[0] => $tableNameExplode[1] . "_" . $tableNameExplode[2]
                );
                $result[] = $tableNameAssociation;
            }
        }
    }

    return $result;
}

/**
 * Get all tableName associated
 * @param $tableName
 * @return array
 */
function getTableNameAssociated($tableName)
{
    $tableNameWithAssociation = buildTableNameWithAssociation();
    $result = array();

    foreach ($tableNameWithAssociation as $association)
    {
        if(array_key_exists($tableName, $association))
            $result[] = $association[$tableName];
    }

    return $result;
}

function existInArray($array, $key)
{
    foreach ($array as $tab)
    {
        if(array_key_exists($key, $tab)) return TRUE;
    }
    return FALSE;
}

/**
 * Enter description here...
 *
 * @param $ret
 * @return null
 */
function generateAllRoutesFiles($ret)
{
    error_reporting(E_ALL ^ E_DEPRECATED); //don't display error depreciated wamp

    $list_user_tables = getTableUser(); //récuperer tous les tables utilisateurs
    $list_table_affected_by_association = buildTableNameWithAssociation(); //récupérer les tables avec ce qui y sont associés

    $fileCreated = "";

    for($i=0;$i<count($ret);$i++)
    {
        if(!doesTableContainPK($ret[$i])) continue;

        $tableName = $ret[$i][0];

        if(in_array($tableName, $list_user_tables)) //si c'est un table d'utilisateur de l'application
        {
            $template = new Template('template/tpl/route_[users].tpl');
            $template->set('table_name', $tableName);
            $template->set('required_params', getFieldsParams($tableName));
            $template->write('generated/routes/route_'.$tableName.'.php');

            //login-register
            $template = new Template('template/tpl/route_[login_register].tpl');
            $template->set('table_name', $tableName);
            $template->set('required_params_login', "email','password");
            $template->set('required_params_register', "name','email','password");
            $template->write('generated/routes/route_login_register_'.$tableName.'.php');

            $fileCreated .= "require_once 'routes/route_$tableName.php'; <br>";
            $fileCreated .= "require_once 'routes/route_login_register_$tableName.php'; <br>";

            //' . WSVERSION . '
            copy('generated/routes/route_'.$tableName.'.php', '../../' . WSVERSION . '/routes/route_'.$tableName.'.php');
            copy('generated/routes/route_login_register_'.$tableName.'.php', '../../' . WSVERSION . '/routes/route_login_register_'.$tableName.'.php');
        }
        else
            if(existInArray($list_table_affected_by_association, $tableName)) //si la table est en relation avec un autre
            {
                foreach ($list_table_affected_by_association as $association)
                {
                    if(array_key_exists($tableName, $association))
                    {
                        $template = new Template('template/tpl/route_[table_affected_by_association].tpl');
                        $template->set('table_name', $tableName);
                        $template->set('required_params', getFieldsParams($tableName));
                        $template->set('table_name_affected', $association[$tableName]);
                        $template->write('generated/routes/route_'.$tableName.'.php');

                        $fileCreated .= "require_once 'routes/route_$tableName.php'; <br>";

                        //' . WSVERSION . '
                        copy('generated/routes/route_'.$tableName.'.php', '../../' . WSVERSION . '/routes/route_'.$tableName.'.php');
                    }
                }
            }
            else
                if(strpos($tableName, "_") !== FALSE) //si c'est une table d'association
                {
                    $template = new Template('template/tpl/route_[tablename_association].tpl');
                    $template->set('table_name', $tableName);
                    $template->set('required_params', getFieldsParams($tableName));
                    $template->set('table_name_first_part', explode("_",$tableName)[0]);
                    $template->set('table_name_second_part', explode("_",$tableName)[1]);
                    $template->write('generated/routes/route_'.$tableName.'.php');

                    $fileCreated .= "require_once 'routes/route_$tableName.php'; <br>";

                    //' . WSVERSION . '
                    copy('generated/routes/route_'.$tableName.'.php', '../../' . WSVERSION . '/routes/route_'.$tableName.'.php');
                }
                else
                    if(strpos($tableName, "_") === FALSE) //si c'est une table simple à màj (crud) tout simplement
                    {
                        $template = new Template('template/tpl/route_[tablename].tpl');
                        $template->set('table_name', $tableName);
                        $template->set('required_params', getFieldsParams($tableName));
                        $template->write('generated/routes/route_'.$tableName.'.php');

                        $fileCreated .= "require_once 'routes/route_$tableName.php'; <br>";

                        //' . WSVERSION . '
                        copy('generated/routes/route_'.$tableName.'.php', '../../' . WSVERSION . '/routes/route_'.$tableName.'.php');
                    }
    }

    copy('generated/routes/route_login_register_utilisateur.php', 'generated/routes/route_login_register.php');
    copy('generated/routes/route_login_register_utilisateur.php', '../../' . WSVERSION . '/routes/route_login_register.php');

    $fileCreated .= "require_once 'routes/route_login_register.php'; <br>"; ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">

        <title>Generate CRUD Route Slim</title>

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">

        <!-- Bootstrap core CSS -->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet">

        <!-- Material Design Bootstrap -->
        <link href="assets/css/mdb.min.css" rel="stylesheet">
    </head>
    <body>
    <!--Panel-->
    <div class="card text-xs-center">
        <div class="card-header default-color-dark white-text">
    Generate CRUD Route Slim
    </div>
        <div class="card-block">
            <h4 class="card-title">Tous les routes (APIs) on été crée</h4>
            <p class="card-text">Veuillez copier tous les lignes de code suivant dans le fichier index.php du répértoire v1</p>
            <p class="card-text"><?php echo $fileCreated; ?></p>

            <form class="form-inline" action="index.php" method="post">
                <div class="md-form form-group">
                    <input type="submit" class="btn btn-default btn-sm" value="Download source">
                </div>
            </form>

        </div>
        <div class="card-footer text-muted default-color-dark white-text">
    Copyright &copy; <?php echo date("Y"); ?>. Create by Hery RAKOTONARIVO
    </div>
    </div>
    <!--/.Panel-->
    </body>
    </html>

<?php
}

$fileConfig = dirname(dirname(__DIR__)) . '/libs/GenerateCRUDRouteSlim/template/class/dao/config.php';

if(file_get_contents($fileConfig) == "" || file_get_contents($fileConfig) == NULL)
{
    header('location:setting.php');
}
else generate(); //generate all route