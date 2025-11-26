<?php
// app/Database.php
class Database {
    private $pdo;
    public function __construct($host,$dbname,$user,$pass){
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $opts = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
        $this->pdo = new PDO($dsn,$user,$pass,$opts);
    }
    public function pdo(){ return $this->pdo; }
}
