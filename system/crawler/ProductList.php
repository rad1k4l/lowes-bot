<?php
/**
 * Created by PhpStorm.
 * User: Root
 * Date: 15.05.2019
 * Time: 1:30
 */

namespace crawler;
use cache;
use DiDom\Document;

class ProductList
{
    public $request;
    
    public function getDate(){
        date_default_timezone_set("Asia/Baku");
        return date("Y-m-d H:i:s");
    }


    public function saveStatus(array $data = []){


        $payload = "
        payload : 
                Hazırki mərhələ 2/4
                ->
                Məhsulların siyahısı yığılır
                Başladı(bu mərhələ):".$data['started']."     
                Ümümi kateqoriya sayı:". $data['sum']."
                Səhifə sayı: ".$data['count']."
                Emal edilmiş kateqoriya sayi: ".$data['categories']."
                Faiz ilə (bitirmə) : ".$data['prc']."%
        end;
        --------------------------------------------
        bot-status: working**
        hosted-on: or-host@173.*.*.*
        process-id: 1723439
        server: standby AzeBot server
        (c) 2017-2019 powered by Orkhan cedvel_bot engine
        ";
        $this->request->sendData("/btdn_cve/cedvel/savestatus.php" , serialize($payload));
    }


    public function __construct()
    {
        $this->request = new \Request();
    }



    public function get($categories , $cache = true) : array {
        if($cache === true)
        {
            return cache::setorget("product.list",
                function() use($categories) {
                    return $this->craw($categories);
                }
            );
        }else{
            $data = $this->craw($categories);
            cache::set("product.list", $data);
            return $data;
        }
    }

    public function craw(&$categories){
        $started = $this->getDate();
        $result = [];
        $sum = count($categories);
        $count = 0;
        foreach ($categories['data'] as $k => $category){
            $urls = $this->_list($category['url']);
            $count += count($urls);
            $data = [
                "sum" => $sum,
                'count' => $count,
                'categories' => $k+1,
                'started' =>$started,
                'prc' => ($k+1/$sum )*100
            ];

            foreach ($urls as $url) {
                foreach ($url as $item) {
                    $result[] = $item;
                }
            }
            $this->saveStatus($data);
        }
        return $result;
    }


    public function _list($url){
        $data = $this->request
                    ->send($url)
                    ['payload'];
        $doc = new Document($data);
        $result= [
            'root' => $url,
        ];
        $pagination = $doc->find("ul.pagination.js-pagination.met-pagination.art-pl-pagination");

        if (count($pagination) !== 0){
            $pages = $pagination[0]->find('li');

            foreach ($pages as $k => $page ){

                if ($page->hasAttribute('class')){
                    $attr = $page->attr('class');

                    if($attr == "active"){
                        $result['data'][] = [
                            'type' => "active",
                            "url"  => $page->find('a')[0]->attr('href'),
                            'num'  => $page->find('a')[0]->text(),
                        ];
                    }elseif ($attr == "more"){
                        $result['data'][] = [
                            'type' => 'more',
                        ];
                    }
                }else {
                    $result['data'][] = [
                        'type' => "page",
                        "url" => $page->find('a')[0]->attr('href'),
                        'num' => $page->find('a')[0]->text(),
                    ];
                }
            }
            $links = $this->generateLinks($result);
        }else{
            $links = [ [ $url] ];
        }
        return $links;
    }

    public function generateLinks( array $elements) : array {

        $lis = $elements['data'];
        $url = $elements['root'];
        end($lis);
        $count =$lis[key($lis)]['num'];
        $result = [ ];
        for ($i = 1 ; $i <=$count+1; $i++){
            $result[ ] = [
                $url . "?page=" . $i,
            ];
        }

        return $result;
    }
}