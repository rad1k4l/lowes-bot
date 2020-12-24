<?php
namespace out;

class console{


    /**
     * @return bool|resource
     */
    public static function clean(){
        return popen('cls', 'w');
    }

    public static function print($data , $endl = true ){
        print_r($data);
        if($endl == true)  print("\n");

    }

    public static function input($text = false){
        if($text !== false) self::print($text);
        return trim(fgets(STDIN));
    }

}