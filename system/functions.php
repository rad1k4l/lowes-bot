<?php
    $request = new Request();
    function request(){
        global $request;

        return $request;
    }

    function conf(string $confname){
        global $conf;

        return $conf[$confname];
    }