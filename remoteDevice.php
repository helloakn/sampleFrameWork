<?php
/*
Developed by : Akn via Zote Innovation
Date : 26-Oct-2020
Last Modify Date : 26-Oct-2020
*/
namespace API\Application;

class RemoteDevice{

  
    function __construct() {
    }

    static function Device(){
        $browser = get_browser(null, true);
        return $browser['browser'];
    }
    static function ip(){
        return $_SERVER['REMOTE_ADDR'];
    }

}
?>