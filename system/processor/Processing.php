<?php


namespace processor;

use crawler\Category;
use crawler\ProductAnalyzer;
use crawler\ProductList;
use crawler\Products;

class Processing
{
    public $category;
    
    public $productList;
    
    public $productLinks;

    public function start(){
        return $this->processing();
    }

    // work logic
    public function processing() : bool {
        echo "--STARTED CRAW FRON LOWES.COM--\n";
        $products = $this->lowes();
        echo "--END PROCESS CRAW FROM LOWES.COM--\n";

        // plug in processor 2
        $result = (new  Search())->start($products , false);
        echo "EVENT END OF PROCESS AMAZON CRAW (c) Orkhan 2019\n";
        return true;
    }

    public function lowes()
    {
        echo "processing categories\n";
        $category =  (new Category())->get();

        echo "success\nloading product lists\n";
        $productList = (new ProductList())->get($category);

        echo "success\nloading product links\n";
        $productLinks = (new Products())->get($productList);
        echo "save links to cache\n";
        return (new ProductAnalyzer())->get($productLinks  );
    }



}