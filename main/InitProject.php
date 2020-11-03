<?php
namespace API\Application\Main;
class InitProject{
    private $argv = [];
    function __construct($args) {
        $this->$args = $args;
        foreach($this->$args as $arg){
            $cmd = explode(":",$arg);
            if(count($cmd)>1){
                echo $cmd[0];
                if($cmd[0]=="doc"){
                    $doc = $cmd[1];
                    $this->createDir($doc);
                    $this->createDir($doc."/public");
                    $this->createDir($doc."/model");
                    $this->initEnv($doc."/.env");
                }
            }
        }
    }
    private function createDir($dir){
        if(!is_dir($dir)){
            mkdir($dir);
        }
    }

    public function initEnv($file){
        $file = fopen($file, "w") or die("Unable to open file!");
        fwrite($file, "DB_SERVER=localhost\n");
        fwrite($file, "DB_NAME=zote\n");
        fwrite($file, "DB_USER=root\n");
        fwrite($file, "DB_PASSWORD=\n");
        fclose($file);
    }
}
?>