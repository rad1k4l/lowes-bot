<?php

namespace processor;

// google searcher
use crawler\Amazon;
use crawler\ProductAnalyzer;
use crawler\Products;
use out\console;
use renderer\TableRenderer;
use Request\Google;
use Request\UnGoogle;

class Search
{
    private $endpoints = [];
    private $configs = [];
    private $amazon;
    private $requestCount = [];

    /**
     * Search constructor.
     */
    public function __construct()
    {

        $this->configs = conf("google");
        if(!$this->initEndpoints()) exit("ERR misconfiguration: Engines not found!");
        $this->endpoints[0] = new Google();
        $this->amazon = new Amazon();
    }
    /**
     * @return bool
     */
    private function initEndpoints (){
        $result = false;
        foreach ($this->configs as $config) {
            $this->endpoints[] = new Google($config['key'], $config['cx']);
            $this->requestCount[] = 0;
            $result = true;
        }
        return $result;
    }

    public function start(array $products , bool $cache = true ) : array {
        define("cachename" ,"amazon.result");
        if($cache == true){
            $data =  \cache::setorget(cachename, function () use ($products)
            {
                return $this->find($products);
            });
        }
        $data = $this->find($products);
        \cache::set(cachename, $data);
        return $data;
    }

    public function requested(){
        return (new ProductAnalyzer())->checkMail();
    }

    public function handleRequest($data, $products){
        $file = (new TableRenderer())->render($products);
        echo "created file {$file} \n";
        (new ProductAnalyzer())->deleteMail();
    }


    /**
     * @param array $products
     * @return array
     */
    public function find(array $products){
        $i = 0;  $googleRequest = 0;
        echo "-STARTED AMAZON CRAWLER-\n";
        foreach($products as $k =>$product){
            $i++; $count = count($products);
            $status =  "STATUS iterator {$i}/{$count} : Google requests  - {$googleRequest} ";
            echo $status . "\n";
            $product['amazon'] =  $this->processing($product);
            if (empty($product['amazon'])) { echo "continue;\n"; continue; }
//            $product = $this->price($product);
            $products[$k] = $product;
            $googleRequest = Google::getReqCount();
            $requested = $this->requested();
            if($requested){
                $this->handleRequest($requested , $products);
            }
            \cache::set("amazon.result" , $products);
        }
        return $products;
    }

    public function price(float $price1, float $price2) : int {
        $price1 =  $price1 + ($price1 * 0.09); // lowes

        $price2  = $price2 - ($price2 * 0.15); //amazon

        if ($price1 < $price2) return 1;

        return 0;
    }

    public function processing(array $product) {
        echo "\tsearch product...\n";
        $links = $product['model'] !== false ? $this->search(trim($product['model'])) : [];
        $links = Amazon::link($links);
        if(count($links) < 3)
        {
            $additional = $this->search(trim($product['name']));
            foreach ($additional as $item) { $links[] = $item; }
            $links = Amazon::link($links);
        }
        $links = \Url::unique($links);
        $result = [];
        $x = 0;
//        print_r($links);
        foreach ($links as $k => $link)
        {
            echo "\tcraw from amazon...\n";
            $amazon = $this->amazon->get($link);
            echo $link  . "  | x = {$x}". "\n";
            if(!empty($amazon)){
               $x++;
               $amazon['price.passed'] = $this->price( floatval($product['amount']), floatval($amazon['price']) );
               $result[] = $amazon;
            }
            if($x >= 2) return $result;
        }
        return [ ];
    }

    private static $endid = 0;
    private function search(string $term) : array {
        $endid = self::$endid;
        $ids = count($this->endpoints) - 1;
        if($ids === $endid ){ $endid = 0; }else{ $endid++; }
        if (isset($this->endpoints[$endid])) {
            echo "\tsearch {$term}\n";
            $links = $this->endpoints[$endid]
                ->search($term);
        }else exit("ERR: search misconfiguration on endpoint id ".$endid);
        if ($links->hasResult())
        {
            return $links->getLinks();
        }

        while (true) {
            $empt = "\t\tGoogle search is empty : \"{$term}\"\n";
            $command = console::input($empt);

            if ($command == "print") {
                print_r($links->data);
            }elseif (\Url::in(["cont" , "continue" , "c", "skip"] , $command)){
                break;
            }elseif ($command == "help"){
                echo "search php -> 159line\n";
            }

        }
        return [];
    }



}