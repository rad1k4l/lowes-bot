<?php

namespace crawler;


use DiDom\Document;
use cache;
class Category
{
    public $request;

    public function __construct()
    {
        $this->request = new \Request();
    }

    public function get ( $cache = true ) {
        $data = $this->request->departments();

        if($cache === true){ 
            return cache::setorget("category.list", 
            function  () use($data) {
                return $this->craw($data); 
            });
        }else{
            $data = $this->craw($data);
            cache::set("category.list", $data);
            return $data;
        }
    }

    public function craw(&$data) {

        $doc = new Document($data);

        $section = $doc->find("section#mainContent>div.categorylist");
        $departments = $section[0]->find("li>h6>a");
        $result  = [];

        foreach ($departments as $k => $department){
            $result[] = [
                'url' => $department->attr('href'),
                'name' => $department->text()
            ];
        }
        $result = [
            'data' => $result,
            'count' => count($result),
        ];
        unset($data);
        return ($result);
    }


}