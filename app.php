<?php
/*
Developed by : Akn via Zote Innovation
Date : 26-Oct-2020
Last Modify Date : 26-Oct-2020
*/
namespace API\Application;
use API\Application\Request;
use API\Application\Schema\Database;
use Zote\Application\ExceptionHandler;

//error_reporting(E_ERROR | E_PARSE);
class App {
    public $rootDir = "";
    public $routePrefix = "";
    public $guard = "";
    function __construct() {
        $this->routeList = [];
        $this->rootDir = getCwd()."/../";
        $this->controllerDir = getcwd().'/../code/controller/';
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
        $url = $url[0]=="/"?$url:"/".$url;
        $url = $this->routePrefix.$url;
        //echo $url;exit;
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
        $this->routePrefix = "";
        $this->guard = "";
    }
    
    function withGuard($guard){
        $this->guard = $guard;
        return $this;
    }
    function routePrefix($prefix,$function){
        $this->routePrefix = $this->routePrefix."/".$prefix;
        call_user_func($function,$this);
        $this->guard = "";
        $this->routePrefix = "";
    }

    function mapping(){

    }
    function route($url){
        $db = new Database;
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
                $nameSpace = "API\\Controller\\".str_replace("/","\\",$route["controller"]);
                $classPath =  $this->controllerDir.$route["controller"].".php";
               
                if(file_exists($classPath)){
                    include $classPath;
    
                    $functionName = $route['function'];
                    $obj = new $nameSpace();
                    $request = new Request($db);
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
            throw new ExceptionHandler(ExceptionHandler::RouteNotFound($url,$this->routeList));
            
        }
    }
}
$app = new App();
include 'Main.php';
include 'ExceptionHandler.php';
include 'env.php';
$route = $app;
include '../code/route.php';
$app = $route;
include 'schema/database.php';
include 'remoteDevice.php';
include 'auth.php';
include 'schema/table.php';
include 'request.php';
include 'validation.php';
include 'response.php';
include 'hash.php';
//print_r(Auth::guard);

$dirs = scandir($app->rootDir."code/model");
foreach($dirs as $dir){
    if(strpos($dir, ".php") !== false){
        include $app->rootDir."code/model/".$dir;
    }
}
$app->start();
?>