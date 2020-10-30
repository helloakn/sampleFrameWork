<?php
/*
Developed by : Akn via Zote Innovation
Date : 26-Oct-2020
Last Modify Date : 26-Oct-2020
*/
namespace API\Application\Schema;
use API\Application\Schema\Database;

class Table{

    protected $defaultTableName="s";
    protected $defaultColumnName=[];
    protected static $hiddenColumns = [];
    protected $whereCase = "";
    protected $orderBy = "";
    protected $groupBy = "";
    protected $softDelete = false;

    protected $database = NULL;

    public $id = null;
    private static $_instance = null;

    protected $defaultPros = [
        "database",
        "hiddenColumns",
        "groupBy",
        "orderBy",
        "whereCase",
        "defaultTableName",
        "defaultColumnName",
        "_instance",
        "defaultPros",
        "softDelete",
        "tableName",
        "columnName"
    ];
    
    function __construct() {
        $this->database = new Database();
    }

    public function __call($name, $arguments) {
        $name = "_".$name;
        $functionList = get_class_methods($this);
        if(in_array($name,$functionList)){
            $tableName =static::$tableName;
            return $this->$name($tableName,$arguments);
        }
        else{
            throw new ExceptionHandler(ExceptionHandler::MethodNotFound($this,$name));
        }
    }
    public static function __callStatic($name, $arguments) {
        self::$_instance = (self::$_instance === null ? new self : self::$_instance);
        self::$_instance->database = new Database();
        $name = "_".$name;
        $functionList = get_class_methods(self::$_instance);
        if(in_array($name,$functionList)){
            $tableName =static::$tableName;
           return self::$_instance->$name($tableName,$arguments);
        }
        else{
            throw new ExceptionHandler(ExceptionHandler::MethodNotFound($this,$name));
        }
    }
    function _select($tableName,$args){
        $this->defaultTableName = $tableName;
        foreach($args as $arg){
            $this->defaultColumnName[] = $arg;
        }
        return $this;
    }

    function where($queryString){
        $this->whereCase = ($this->whereCase==""?" WHERE ".$queryString:$this->whereCase." ".$queryString);
        return $this;
    }

    function orderBy($queryString){
        $this->orderBy = " ORDER BY ".$queryString;
        return $this;
    }
    function groupBy($queryString){
        $this->groupBy = " GROUP BY ".$queryString;
        return $this;
    }

    function get(){
       $select = $this->defaultColumnName ? implode(",",$this->defaultColumnName) : " * ";
       $oBy = $this->orderBy!==""?$this->orderBy:"";

       $cmdString = "SELECT ".$select." FROM ".$this->defaultTableName.
       ($this->whereCase!==""?$this->whereCase:"").
       ($this->groupBy!==""?($this->groupBy.$oBy):$oBy);
       $database = $this->database;
       //echo $cmdString;
        return $database->query($cmdString);     
    }
    function first(){
        $database = $this->database;
        $select = $this->defaultColumnName ? implode(",",$this->defaultColumnName) : " * ";
        $oBy = $this->orderBy!==""?$this->orderBy:"";
 
        $cmdString = "SELECT ".$select." FROM ".$this->defaultTableName.
        ($this->whereCase!==""?$this->whereCase:"").
        ($this->groupBy!==""?($this->groupBy.$oBy):$oBy);
        $cmdString = $cmdString." LIMIT 0,1";

        $result = $database->query($cmdString);
        if($result->num_rows==1){
            foreach($result as $key=>$value)
            {
                $authUser = new \stdClass();
                foreach($value as $k=>$v)
                {
                    $authUser->$k = $v;
                }
                return $authUser;
            }
        }
        else{
            return false;
        }
        //return $database->query($cmdString);     
     }

    function delete(){

    }
    
    function _find($tableName,$args){
        $id = $args[0];
        //echo static::$tableName;exit;
        $this->defaultTableName = $tableName;

        $cmdString = "SELECT * FROM ".$tableName ." WHERE id=$id";
        //$database = new Database();
        $database = self::$_instance->database;
        $result =  $database->query($cmdString);
        foreach($result as $row){
            foreach($row as $key=>$value){
                $hiddenColumns = static::$hiddenColumns;
                if(!in_array($key,$hiddenColumns)){
                    self::$_instance->$key = $value;
                }
            }
        }
        return self::$_instance;
    }
     
    function _save($tableName){
        //print_r($tableName);
        $columns = [];
        $data = [];
        
        foreach (get_object_vars($this) as $prop_name => $prop_value) {
            if(!in_array($prop_name,$this->defaultPros)){
                $columns[] = "`".$prop_name."`";
               
                if($prop_value==null){
                    $data[] = "NULL";
                }
                else{
                    $data[] = "'".$prop_value."'";
                }
            }
        }
        if($this->softDelete){
            if (!in_array("`created_at`",$columns))
            {
                $columns[] = "`created_at`";
                $data[] = "'".date("yy-m-d h:i:s")."'";
            }
            if (!in_array("`updated_at`",$columns))
            {
                $columns[] = "`updated_at`";
                $data[] = "'".date("yy-m-d h:i:s")."'";
            }
            if (!in_array("`id`",$columns))
            {
                $columns[] = "`id`";
                $data[] = "NULL";
            }
        }
        //$tableName = static::$tableName;
        $cmdString = "INSERT INTO `$tableName` (".implode(",",$columns).") VALUES " ."(".implode(",",$data).");";
        //$database = new Database();
        $database  = $this->database;
        $result = $database->query($cmdString);
        if ($result  === TRUE) {
            $this->id = $database->conn->insert_id;
        } else {
            echo $database->conn->error;
            exit;
        }
    }
}
?>