<?php
/*
Developed by : Akn via Zote Innovation
Date : 26-Oct-2020
Last Modify Date : 26-Oct-2020
*/
namespace API\Application\Schema;
use Zote\Application\Env;

class Database{
    public $conn = null;
    function __construct() {
        
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
        $this->conn = new \mysqli($servername, $username, $password,$db);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
            exit();
        }
        else{
           // echo "conn success";
        }
    }
    function query($cmdString){
        return $this->conn->query($cmdString);
    }
}
//$db = new Database();
?>