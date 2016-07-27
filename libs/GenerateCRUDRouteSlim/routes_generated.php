<?php
/*****************************************************************************************/

/**
* SETTING CONFIG
*/

$host = "";
$user = "";
$password = "";
$databaseName = "";
$version = "";

if(isset($_POST["host"]) && isset($_POST["databaseName"]) && isset($_POST["user"]) && isset($_POST["pwd"]))
{
    $host           = $_POST["host"];
    $user           = $_POST["user"];
    $password       = $_POST["pwd"];
    $databaseName   = $_POST["databaseName"];
    $version        = $_POST["version"];

    $fileConfig = dirname(dirname(__DIR__)) . '/libs/GenerateCRUDRouteSlim/template/class/dao/config.php';

    $fileContent = fopen($fileConfig, 'w');

    fwrite($fileContent, "<?php");
    fwrite($fileContent, "\n");
    fwrite($fileContent, 'define("HOST", "' . $host . '");' . "\n");
    fwrite($fileContent, 'define("DATABASE", "' . $databaseName . '");' . "\n");
    fwrite($fileContent, 'define("USER", "' . $user . '");' . "\n");
    fwrite($fileContent, 'define("PASSWORD", "' . $password . '");' . "\n");
    fwrite($fileContent, 'define("WSVERSION", "' . $version . '");' . "\n");

    fclose($fileContent); ?>

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
            <h4 class="card-title">Votre fichier de configuration a été crée</h4>
            <p class="card-text">Cliquez sur le bouton ci-dessous pour générer tous les routes</p>

            <form class="form-inline" action="index.php" method="post">
                <div class="md-form form-group">
                    <input type="submit" class="btn btn-default btn-sm" value="Generate route">
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
else header('location:setting.php');