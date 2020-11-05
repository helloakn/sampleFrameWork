<?php
/*
Developed by : Akn via Zote Innovation
Date : 26-Oct-2020
Last Modify Date : 26-Oct-2020
*/
namespace zFramework\Schema;

use zFramework\providers\Env;

class Database{
    
    public $conn = null;

    private static $_instance = null;

    function __construct() {
        //echo "db_";
        $this->connectDB();
    }

    function connectDB(){

    }

    static function Instance(){
        self::$_instance = (self::$_instance === null ? new self : self::$_instance);
        $servername = Env::get('DB_SERVER');
        $username = Env::get('DB_USER');
        $password = Env::get('DB_PASSWORD');
        $db =Env::get('DB_NAME');
        
        /*
        $servername = "localhost";
        $username = "haha";
        $password = "aknakn0091";
        $db ="searchApp";
        */
        self::$_instance->conn = new \mysqli($servername, $username, $password,$db);
        
        if (self::$_instance->conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
            exit();
        }
        else{
           // echo "conn success";
        }
        return self::$_instance;
    }
    function query($cmdString){
        return self::$_instance->conn->query($cmdString);
    }
}

//$db = new Database();
?>