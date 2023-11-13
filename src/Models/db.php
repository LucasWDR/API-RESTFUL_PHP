<?php

namespace App\Models;

use \PDO;

class DB
{
    
    private $host = 'localhost:3325';
    private $user = 'root';
    private $pass = '1533';
    private $dbname = 'slim-api';

    public function connect()
    {
        $conn_str = "mysql:host=$this->host;dbname=$this->dbname";
        $conn = new PDO($conn_str, $this->user, $this->pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }
}