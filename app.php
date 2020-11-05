<?php
/*
Developed by : Akn via Zote Innovation
Date : 26-Oct-2020
Last Modify Date : 26-Oct-2020
*/
namespace API\Application;
use zFramework\providers\Request;
use zFramework\Schema\Database;
use zFramework\providers\ExceptionHandler;

//error_reporting(E_ERROR | E_PARSE);
class App {
    public $rootDir = "";
    public $routePrefix = [];
    public $guard = "";
    private $prefixIndex = -1;
    function __construct() {
        $this->routeList = [];
        $this->rootDir = getCwd()."/../";
        $this->controllerDir = getcwd().'/../controller/';

    }
    
    function replace($str,$replaceList){
        foreach($replaceList as $val){
            $str = str_replace($val,"",$str);
        }
        return $str;
    }

    function start(){
        $url = $_SERVER['REQUEST_URI'];
        $url = explode("?",$url);
        $url = $url[0];
        $replaceList = array(
            "/index.php/"
        );
        $url = $this->replace($url,$replaceList);
        $this->route($url);
    }

    function addroute($method,$url,$controllerPath,$functionName){
        //$url = $url[0]=="/"?$url:"/".$url;
        $url = implode("",$this->routePrefix).$url;
        $url = $url[0]=="/"?$url:"/".$url;
        //echo "/".$url.'<br>';//exit;
        $this->routeList[$url] =array(
            'includeClass' => $controllerPath==""?false:true,
            'method' => $method,
            'controller' => $controllerPath,
            'function' => $functionName,
            'guard' => $this->guard
        );
    }
    
    function routeGuard($guard,$function){
        $this->guard = $guard;
        call_user_func($function,$this);
        $this->routePrefix[$this->prefixIndex] = "";
        $this->guard = "";
    }
    
    function withGuard($guard){
        $this->guard = $guard;
        return $this;
    }
    function routePrefix($prefix,$function,$defaultPrefix=""){
        $this->prefixIndex = $this->prefixIndex  +1;
        /*
        $this->routePrefix = $this->routePrefix."/".$prefix;
        var_dump($function);exit;
        call_user_func($function,$this,);
        $this->guard = "";
        $this->routePrefix = "";
        */
           # echo "default -> ".$defaultPrefix."<br>";
         #   echo $prefix."<br>";
         #   echo $this->prefixIndex;
           // $this->routePrefix[$this->prefixIndex].$defaultPrefix."/".$prefix;
        $this->routePrefix[$this->prefixIndex] = $prefix[0]=="/"?$prefix:"/".$prefix;
        //var_dump($function);exit;
        //echo $defaultPrefix."1<br>";
        //echo $this->routePrefix."1<br>";
        call_user_func($function,$this,$prefix);
        $this->guard = "";
        
        unset($this->routePrefix[$this->prefixIndex]);
        $this->prefixIndex = $this->prefixIndex -1;
    }

    function mapping(){

    }
    function route($url){
       //echo ">>".$url;#exit;
        //var_dump($this->routeList);exit;
        //$db = Database::Instance();
        if (array_key_exists($url, $this->routeList)) {
           
            $route = $this->routeList[$url];
            if($route['guard']!=''){
                $status = Auth::guard('User')->isLogin();
                if(!$status){
                    $data = array(
                        "status"=>403,
                        "message" => "Access Denied for Incorrect Token"
                    );
                    
                    Response::outPut($data);
                    return false;
                }
            }
            if( $route['includeClass']==true){
                $nameSpace = "Controller\\".str_replace("/","\\",$route["controller"]);
                $classPath =  $this->controllerDir.$route["controller"].".php";
               
                if(file_exists($classPath)){
                    include $classPath;
    
                    $functionName = $route['function'];
                    $obj = new $nameSpace();
                    $request = new Request();
                    if(in_array($functionName,get_class_methods($obj)))
                    {
                        $result = $obj->$functionName($request);
                        Response::outPut($result);
                    }
                    else{
                        throw new ExceptionHandler(ExceptionHandler::FunctionNotFound($obj,$functionName,$route));
                    }
                    
                }
                else{
                    echo "Controller not found -> $classPath";
                }
            }
            else{
                $function = $route['function'];
                call_user_func($function,$this);
            }
            
        }
        else{
            //akn
            throw new ExceptionHandler(ExceptionHandler::RouteNotFound($url,$this->routeList));
            
        }
    }
}
$providersDir = "providers/";
$app = new App();
include 'Main.php';
include $providersDir.'ExceptionHandler.php';
include $providersDir.'env.php';
$route = $app;

include '../route/route.php';
$app = $route;
include 'schema/database.php';
include 'remoteDevice.php';
include 'auth.php';
include 'schema/table.php';
include $providersDir.'request.php';
include $providersDir.'validation.php';
include $providersDir.'response.php';
include 'hash.php';
//print_r(Auth::guard);

$dirs = scandir($app->rootDir."model");
foreach($dirs as $dir){
    if(strpos($dir, ".php") !== false){
        include $app->rootDir."model/".$dir;
    }
}
$app->start();
?>