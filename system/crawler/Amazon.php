<?php
namespace crawler;

use DiDom\Document;
use out\console;
use out\File;

class Amazon
{
    private  $request;

    public function __construct() { $this->request = new \Request\Amazon(); }

    public static function searchPage(array $links) : array {
        $result = [];
        $regexp = "#.\/s\?rh|/s\?k#";
        foreach ($links as $link) {
            $match = [];
            if (preg_match($regexp, $link, $match))
            {
                $result [ ] = $link;
            }
        }
        return $result;
    }

    public static function link(array $links) : array {
        $result = [];
        $regexp = "#.com/ask/.|.\/s\?rh|.com/slp/|.com/review/|.com/product-reviews|/b\?ie|\.com/sitemaps|/reviews-render|/s?k|/b?node#";
        foreach ($links as $link) {
            $match = [];
            if (!preg_match($regexp, $link, $match)) {
                $result[] = $link;
            }
        }
        return $result;
    }

    public static function only(array  $links){
        $result = [];
        $regexp = "#.amazon\.com.#";
        foreach ($links as $link) {
            $match = [];
            if (preg_match($regexp, $link, $match))
            {
                $result[] = $link;
            }
        }
        return $result;
    }

    public function get(string $url, $cache = true) : ? array {
        return $this->craw($url);
    }

    public function craw(string $url) : ? array {
        $response = $this->request->browser($url ,true);
//        $httpStatus = $response['obj']->getStatusCode();
//        if ($response['obj'] !== null && $httpStatus != 200 ){
//            echo "HTTP {$httpStatus} : on url {$url}\n";
//            return [];
//        }
        $payload = $response['payload'];
        $result = [
            "img" => false,
            "price" => 0,
            "url" => $url,
            "currency" => '$',

        ];
        $doc = new Document($payload);
        unset($payload, $response);

        $price =  $this->f($doc, conf("amazon")['product']['price.selectors'], $url , "price");

        if ($price == null) { echo "\tcontinue price not found\n"; return []; }

        $price = explode("$", $price->text());

        if(count($price) == 2) $result['price'] = $price[1];
        elseif(count($price) == 3)
        {
            $splitted = $price;
            $price1 = explode(" ", $splitted[1])[0];
            $price2 = explode(" ", $splitted[2])[0];
            $result['price'] = floatval($price1) + floatval($price2);
        } else $result['price'] = $price;
        unset($price);

        $img = $this->f($doc, [
            "img#landingImage",
            "#imgBlkFront",
            "#zg-ordered-list > li:nth-child(1) > span > div > span > a > span > div > img"
        ], $url , "get_image");

        if($img !== null) {
            $result['img'] = $img->attr('src');
        }else{
            echo "\tIMG not found at {$url}\n";
            $result['img'] = "none.png";
        }
        return $result;
    }

    private function f( Document $doc, array $selectors , $url ,$action = "undefined") {

//        echo "search...\n";
        foreach ($selectors as $selector) {
            $find = $doc->first($selector);
            if($find !== null )
                return  $find;
        }

//        $selector = console::input("stoped on {$action} action : on url {$url} \ntype selector:");
        $selector = "continue";
        if($selector == "html"){
            $filename = console::input("filename: ");
            $filename = File::output($filename);
            File::save($filename , $doc->html());
        }else if($selector == "continue"){
            return null;
        }else
            $selectors[] = $selector;
        return $this->f($doc, $selectors, $url, $action);
    }

}