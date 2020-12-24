<?php

namespace Request;


use crawler\Crawler;
use DiDom\Document;

class UnGoogle extends \BaseRequest
{
    private  $data;
    private $payload;
    protected  $root;
    private static $reqCount = 0;
    public $count = 0;
    public function __construct()
    {
        $this->root = "https://www.google.com";

        return parent::__construct();
    }

    public function getLinks(){
        return $this->getData();
    }

    /**
     * @return int
     */
    public static function getReqCount(): int
    {
        return self::$reqCount;
    }

    public function hasResult(): bool {
        return count($this->data) >0;
    }


    public function search(string $query = '', array $params = []){
        if (isset($params['site'])) $query = "site:" . $params['site'] . ' '. $query;
        $query = explode(" " , $query);
        $query = implode("+" , $query);
        $this->_search($query);
        unset($query);
        return $this;
    }

    private function _search(string  $query){
        $response = $this->send("/search?q=" . $query . "&filter=0" );
        if(isset($response['cache'])){ $this->addCount(); }
        $this->payload  = $response['payload'];
        unset($response);
        $this->craw();
        return $this;
    }

    private function addCount(){
        self::$reqCount++;
    }


    /**
     * @return mixed
     */
    public function getData(){ return $this->data; }

    private function craw(){
        $doc = new Document($this->payload);
        $searchBlocks = Crawler::fd($doc , [
            "#rso > div ",
        ],"no url" );

        foreach ($searchBlocks as $block){
            $results = Crawler::fd($block,[
                "div[class=g] > div > div > div > a",
                "div.g > div > div > div > a",
            ],"no_url" );

            foreach ($results as $result) {
                $link = $result->attr("href");
                if($link == "#"){ continue; }
                $this->data[] = $link;
            }
        }
    }

    public function commented() {
    //        foreach ($results as $result) {
//            echo $result->html();
//            echo "<br> <br>\n\n\n\n\n\n\n\n\n\n";
//        }
//            exit();
//        foreach ($searchResults as $result) {
//            echo $result->html();
//            echo "<br> <br>\n\n\n\n\n\n\n\n\n\n";
////            $this->data[] = Crawler::f($result , [
////                "div > div > div.r > a",
////                ""
////            ],"no url" )->attr("href");
//        }
}

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

}