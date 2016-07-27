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
            <h4 class="card-title">Bienvenue sur l'interface de génération des APIs à partir d'une BDD</h4>
            <p class="card-text">Veuillez remplir les informations suivantes</p>

            <form class="form-inline" action="routes_generated.php" method="post">

                <div class="md-form form-group">
                    <i class="fa fa-ioxhost prefix"></i>
                    <input type="text" id="form91" class="form-control validate" placeholder="Your host" name="host" size="30">
                </div><br>

                <div class="md-form form-group">
                    <i class="fa fa-database prefix"></i>
                    <input type="text" id="form92" class="form-control validate" placeholder="Your database name" name="databaseName" size="30">
                </div><br>

                <div class="md-form form-group">
                    <i class="fa fa-user prefix"></i>
                    <input type="text" id="form92" class="form-control validate" placeholder="User MySQL" name="user" size="30">
                </div><br>

                <div class="md-form form-group">
                    <i class="fa fa-lock prefix"></i>
                    <input type="password" id="form92" class="form-control validate" placeholder="Password MySQL" name="pwd" size="30">
                </div><br>

                <div class="md-form form-group">
                    <i class="fa fa-angle-double-up prefix"></i>
                    <input type="text" id="form92" class="form-control validate" placeholder="Version de l'api (ex: v1)" name="version" size="30">
                </div><br>

                <div class="md-form form-group">
                    <input type="submit" class="btn btn-default btn-lg" value="Setup">
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