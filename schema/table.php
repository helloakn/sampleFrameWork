<?php
/*
Developed by : Akn via Zote Innovation
Date : 26-Oct-2020
Last Modify Date : 26-Oct-2020
*/
namespace zFramework\Schema;
use zFramework\Schema\Database;

class Table{

    protected $defaultTableName="";

    protected $defaultColumnName=[];

    protected static $autoIncreaseKeys = [];

    protected static $hiddenColumns = [];
    protected $staticPrimaryKey = [];

    protected $whereCase = "";

    protected $orderBy = "";

    protected $groupBy = "";

    protected $softDelete = false;

    protected $database = NULL;
    
    private static $_instance = null;

    protected $defaultPros = [
        "database",
        "hiddenColumns",
        "groupBy",
        "orderBy",
        "whereCase",
        "defaultTableName",
        "defaultColumnName",
        "autoIncreaseKeys",
        "primaryKeys",
        "staticPrimaryKey",
        "_instance",
        "defaultPros",
        "softDelete",
        "tableName",
        "columnName"
    ];
    
    function __construct() {
       //$this->database = new Database();
       //echo "constructor";
        $this->database = Database::Instance();
        //$this->defaultTableName = static::$tableName;
        //$this->staticPrimaryKey = static::$primaryKeys;
    }

    public function __call($name, $arguments) {
        //echo "__call";
        /*
        self::$_instance = (self::$_instance === null ? new self : self::$_instance);
        self::$_instance->database = Database::Instance();
        $name = "_".$name;
        $functionList = get_class_methods($this);
        if(in_array($name,$functionList)){
            self::$_instance->defaultTableName =static::$tableName;
            self::$_instance->staticPrimaryKey = static::$primaryKeys;
            return self::$_instance->$name(
                self::$_instance->defaultTableName,
                self::$_instance->staticPrimaryKey,
                $arguments);
        }
        else{
            throw new ExceptionHandler(ExceptionHandler::MethodNotFound($this,$name));
        }
        */
        $name = "_".$name;
        $functionList = get_class_methods($this);
        if(in_array($name,$functionList)){
            //echo $this->defaultTableName;exit;
            if(!$this->defaultTableName){
                $this->defaultTableName =static::$tableName;
                $this->staticPrimaryKey = static::$primaryKeys;
            }
            
            return $this->$name(
                $this->defaultTableName,
                $this->staticPrimaryKey,
                $arguments);
        }
        else{
            throw new ExceptionHandler(ExceptionHandler::MethodNotFound($this,$name));
        }
    }
    public static function __callStatic($name, $arguments) {
        self::$_instance = (self::$_instance === null ? new self : self::$_instance);
        self::$_instance->database = Database::Instance();
        $name = "_".$name;
        $functionList = get_class_methods(self::$_instance);
        if(in_array($name,$functionList)){
            self::$_instance->defaultTableName =static::$tableName;
            self::$_instance->staticPrimaryKey = static::$primaryKeys;
           return self::$_instance->$name(self::$_instance->defaultTableName,
           self::$_instance->staticPrimaryKey,
           $arguments);
        }
        else{
            throw new ExceptionHandler(ExceptionHandler::MethodNotFound($this,$name));
        }
    }
    function _select($tableName,$primaryKeys,$args){
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
    function _update($tableName,$primaryKeys,$args){
        #echo "hello";exit;
        #echo $tableName;exit;
        //print_r(static::$tableName);
        $cmdString = "UPDATE $tableName SET ";
        $cmd = [];
        foreach (get_object_vars($this) as $prop_name => $prop_value) {
            if(!in_array($prop_name,$this->defaultPros)){
                //echo " x ".$prop_name.":".$prop_value." y ";
                if($primaryKeys[0]==$prop_name){
                    $cmd[] = $prop_name."=".(is_int($prop_value)?$prop_value:"'".$prop_value."'")." ";
                }
                else{
                    $cmd[] = $prop_name."=".(is_int($prop_value)?$prop_value:"'".$prop_value."'")." ";
                }
                
            }
        };
        //print_r($primaryKeys);exit;
        $key = $primaryKeys[0];
        //var_dump($this);
        //echo $key;exit;
        //echo $this->$key;exit;
        $cmdString = $cmdString. implode(",",$cmd) ." WHERE ".$primaryKeys[0]."=".(is_int($this->$key)?$this->$key:"'".$this->$key."'").";";
        //echo $cmdString;
        $database  = $this->database;
        $result = $database->query($cmdString);
        if ($result  === TRUE) {
            $this->id = $database->conn->insert_id;
        } else {
            echo $database->conn->error;
            exit;
        }
    }
    function _find($tableName,$primaryKeys,$args){
        //echo $tableName;exit;
        #print_r($primaryKeys);exit;
       # print_r($args);exit;
        $id = $args[0];
        #echo $id;exit;
        $columnId = $primaryKeys[0];
        #echo $columnId;exit;
        //echo static::$tableName;exit;
        $this->defaultTableName = $tableName;

        
        $cmdString = "SELECT * FROM ".$tableName ." WHERE $columnId=".(is_int($id)?$id:"'".$id."'");
        //$database = new Database();
        #echo $cmdString ;exit;
        $database = self::$_instance->database;
        $result =  $database->query($cmdString);   
        //echo $result['num_rows'];exit; 
        //var_dump($result);
        if($result){
            foreach($result as $row){
                foreach($row as $key=>$value){
                    $hiddenColumns = static::$hiddenColumns;
                    if(!in_array($key,$hiddenColumns)){
                        if($key!="primaryKeys"){
                            self::$_instance->$key = $value;
                        }
                        
                    }
                }
            }
        }
        return self::$_instance;
    }
     
    function _save($tableName,$primaryKeys){
        //echo $tableName;exit;
        //echo $tableName;exit;
        //print_r($tableName);
        $columns = [];
        $data = [];
        //var_dump(get_object_vars($this));
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
                if(count($primaryKeys)==0){
                    $columns[] = "`id`";
                    $data[] = "NULL";
                }
                
            }
        }
        //echo "hello";
        //$tableName = static::$tableName;
       // echo implode(",",$columns);exit;
        $cmdString = "INSERT INTO `$tableName` (".implode(",",$columns).") VALUES " ."(".implode(",",$data).");";
      //  echo $cmdString; exit;
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