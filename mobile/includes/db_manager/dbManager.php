<?php

/**
 * Class DBManager
 */
class DBManager
{
    private $host;
    private $dbName;
    private $user;
    private $password;
    private $pdo;

    public $dbManager;
    public $entityManager;

    /**
     * DBManager constructor.
     */
    public function __construct()
    {
        require_once dirname(dirname(dirname(__DIR__))) . '/libs/orm/NotORM.php';

        $this->host = "localhost";
        $this->dbName = "fap_db";
        $this->user = "root";
        $this->password = "root";

        $dsn = "mysql:dbname=$this->dbName;host=$this->host";
        $this->pdo = new PDO($dsn, $this->user, $this->password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->entityManager = new NotORM($this->pdo); //$this->getEntityManager();
        $this->entityManager->debug = true;
    }

    /**
     * Get entity manager
     * @return NotORM
     */
    public function getEntityManager()
    {
        $structure = new NotORM_Structure_Convention(
            $primary = 'id',
            $foreign = '%s_id',
            $table = '%s'
        );
        //return new NotORM($this->pdo, $structure);
        return new NotORM($this->pdo, null, new NotORM_Cache_File("notorm.dat"));
    }

}