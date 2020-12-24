<?php
/**
 * Created by PhpStorm.
 * User: Root
 * Date: 02.07.2019
 * Time: 15:37
 */

namespace crawler;


use DiDom\Document;

class Crawler
{
    /**
     * Secure search dom element
     * @author Orkhan Zeynalli
     * @param Document $doc
     * @param array $selectors
     * @param string $url
     * @return \DiDom\Element|\DOMElement|null
     */
    public static function f( $doc, array $selectors , string $url)  {
        foreach ($selectors as $selector) {
            $find = $doc->first($selector);
            if($find !== null )
                return  $find;
        }
        exit( self::class . " ERR f:".$url);
    }

    /**
     * @param Document $doc
     * @param array $selectors
     * @param string $url
     * @return array|null
     */
    public static function fd( $doc, array $selectors , string $url) : ?  array  {
        foreach ($selectors as $selector) {
            $find = $doc->find($selector);
            if($find !== null )
                return  $find;
        }
        exit( self::class . " ERR fd:".$url);
    }
}