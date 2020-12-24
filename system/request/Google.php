<?php

    /*
     * google custom search */
//  root : /customsearch/v1?key=&cx=&q=asus
namespace Request;
use Url;

class Google extends \BaseRequest
{
    private $key =  "AIzaSyC71A5JH_4X3TfBbN2glOSEarOyWb9kY5Q"; // search api key
    private $cx =   "009996048491022828352:zezoblhjrqm";  // search id
    private $ok =   false;
    public $data = "";
    public $links = [];
    private $items;
    private static $reqcount =0;
    public $count = 0;

    public function __construct(string $key = "", string $cx = "")
    {
        if($key !== "" && $cx !== ""){
            $this->key = $key;
            $this->cx = $cx;
        }
        $this->root = "https://www.googleapis.com";
        return parent::__construct();
    }

    /**
     * @return int
     */
    public static function getReqcount(): int
    {
        return self::$reqcount;
    }

    public function __toString()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function isOk() : bool
    {
        return $this->ok;
    }

    public function search(string  $query , bool  $cache = true){
        $query = Url::type($query);
        $url = $this->_uri($query);
        $response = $this->send($url, $cache);
        if(!isset($response['cache'])) { $this->countPlus(); }
        $this->data = $response['payload'];
        $this->processing();
        return $this;
    }

    public function countPlus(){
        self::$reqcount++;
        $this->count++;
    }

    public function processing(){
        foreach ($this->items() as $item ) {
            $this->links = [];
            $this->links[] = $item['link'];
        }
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    public function count() : int {
        return count($this->links);
    }

    public function hasResult() : bool {
        return $this->count() > 0;
    }

    public function items(){
        return isset($this->decode()['items']) && !empty($this->decode()['items'])  ?  $this->decode()['items']: [];
    }

    public function decode(){
        return json_decode($this->data , true);
    }

    private function _uri(string $query): string {

         return "/customsearch/v1?key=" . $this->key . "&cx=" . $this->cx . "&q=" . $query;
    }
}