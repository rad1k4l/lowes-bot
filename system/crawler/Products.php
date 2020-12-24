<?php
/**
 * Created by PhpStorm.
 * User: Root
 * Date: 16.05.2019
 * Time: 1:19
 */

namespace crawler;
use cache;

use DiDom\Document;
use http\Env\Response;

class Products
{
    public $request;
    public $reference ;


    public function __construct()
    {
        $this->request = new \Request();
    }

    public function getDate(){

        date_default_timezone_set("Asia/Baku");
        return date("Y-m-d H:i:s");
    }


    public function saveStatus(array $data = []){
        $started = $data['started'];
        $sum = $data['sum'];
        $payload = "
        payload : 
                Hazırki mərhələ 3/4
                ->
                Məhsulların linkləri yığılır
                Başladı(bu mərhələ):{$started}      
                Yığılan linklər: {$sum}
                Faiz ilə (bitirmə): undefined
        end;
        ---------------------------------
        bot-status: working**
        hosted-on: or-host@173.*.*.*
        process-id: 1723439
        server: standby AzeBot server
        (c) 2017-2019 powered by Orkhan cedvel_bot engine
        ";
        $this->request->sendData("/btdn_cve/cedvel/savestatus.php" , serialize($payload));
    }



    /**
     * @param array $categoryList
     * @return array
     */
    public function get(array $categoryList , $cache= true) : array {
//exit();
        if($cache === true)
        { 
            return cache::setorget("products.list",
            function  () use($categoryList) {
                return $this->generate($categoryList); 
            });
        }else{
            $data = $this->generate($categoryList);
            cache::set("products.list", $data);
            return $data;
        }
    }

    public function generate(array $categoryList){
        $sum = 0;
        $started = $this->getdate();
        $links  = [ ];
        foreach ($categoryList as  $cat) {
            $in = 0;
            $data = $this->craw($cat);

            if(empty($data)){ continue; }

            foreach ($data as $dat) {
                $links[] = $dat;
                $in++;
                $sum++;
            }
            $data = [
                'sum'   => $sum,
                'started'   => $started,
            ];
            $this->saveStatus($data);
            echo "this -> " . $in . " summary-> " . $sum ."\n";
        }
        return $links;
    }

    

    public function craw(string $url) : array {
        $response = $this->request
            ->send($url  );

        if ($response['obj'] !== null && $response['obj']->getStatusCode() == 404){
            return [];
        }
        $doc = new Document($response
            ['payload']
        );

        $ul = $doc->find("ul.product-cards-grid");
        if (count($ul) == 0 ) {
            return [];
        }
        $elements = $ul[0]->find("li.product-wrapper");
        if (count($elements) == 0 ){
            return [];
        }
        $result = [ ];
        foreach ($elements as $element)
        {
            $result[] = $element->attr("data-producturl");
        }
        return $result;
    }

    public function processing() : array {
        return [];
    }

}