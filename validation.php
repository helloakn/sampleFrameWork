<?php
/*
Developed by : Akn via Zote Innovation
Date : 28-Oct-2020
Last Modify Date : 28-Oct-2020
*/
namespace Zote\Application;
use Zote\Application\ExceptionHandler;
use API\Application\Request;
class Validation{

}
class Validator{
    private static $_instance = null;

    private $_isValidate = true;
    private $_error = []; 
    private $_key = "";

    function _max($args){
        $value = Request::get($this->_key);
        if($args[0]<strlen($value)){
            $this->_isValidate = false;
            $this->_error[$this->_key][] = count($args)==2 ? $args[1] : $this->_key." must be under".$args[0];
        }
        return $this;  
    }
    function _notnull($args){
        $value = Request::get($this->_key);
        if($value==null){
            $this->_error[$this->_key][] = count($args)==1 ? $args[0] : $this->_key." should not be null";
        }
        return $this;  
    }
    function _min($args){
        $value = Request::get($this->_key);
        if($args[0]>strlen($value)){
            //echo "yes";
            $this->_isValidate = false;
            $this->_error[$this->_key][] = count($args)==2 ? $args[1] : "Minimum length of ". $this->_key." is ".$args[0];
           
                
        }
        return $this;  
    }
    function _field($args){
        $this->_key = $args[0];
        return $this;
    }
    function _error($args){
        //var_dump(get_object_vars($this->_error));
        return $this->_error;
    }
    function _validate($args){
        return $this->_isValidate;
    }

    function _seterror($args){
        $this->_isValidate = false;
        $this->_error[$this->_key][] = $args[0];
           
    }
    function _custom($args){
        //var_dump($args);exit;
        //var_dump($args[0]);
        //echo json_encode($args);
        //exit;
        //echo count($args[0]);
        call_user_func($args[0],$this);
        //$args[0]($this);
        //return $this->_isValidate;
        return $this;
    }
    function _rule($args){
        
        call_user_func($args[0],$this);
        //return $this->_isValidate;
        return $this;
    }

    public function __call($name, $arguments) {
        if($name=="custom"){
           // print_r($arguments);exit;
        }
        
        $name = "_".strtolower($name);
        //echo $name."<br>";
        //echo json_encode($arguments);
        $functionList = get_class_methods($this);
        if(in_array($name,$functionList)){
            return $this->$name($arguments);
        }
        else{
            throw new ExceptionHandler(ExceptionHandler::MethodNotFound($this,$name));
        }
    }

    public static function __callStatic($name, $arguments) {
        self::$_instance = (self::$_instance === null ? new self : self::$_instance);
        $name = "_".strtolower($name);
        $functionList = get_class_methods(self::$_instance);
        if(in_array($name,$functionList)){
            return self::$_instance->$name($arguments);
        }
        else{
            throw new ExceptionHandler(ExceptionHandler::MethodNotFound($this,$name));
        }
    }
}

?>