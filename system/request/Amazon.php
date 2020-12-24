<?php
namespace Request;

class Amazon 

extends \BaseRequest
{
    public $root;

    public function __construct()
    {
        return parent::__construct();
    }

    public function request(string $url = "", bool $root = false) : array {

        $response =  $root === true? $this->root.$url : $url;

        return $this->client->send($response);
    }


}