<?php
namespace renderer;

use out\File;

class TableRenderer
{
    private $dir = "views";
    private $view = "table";
    private $limit = 500;
    public $baseFileName;
    public function get(array $data) : string{

        return $this->view($data);
    }

    public function render($products){
        $count = 0; $page = 0;
        $start = 0; $end = 0;
        foreach ($products as $product) {

            if (isset($product['amazon']))
                $count++;
        }
        $pages = ceil($count/$this->limit);
        $baseFileName = "operation-" . time();
        $this->baseFileName = $baseFileName;
        $folder = File::folder(File::output(), "op-code-".time());
        for ($i = 0; $i < $pages ; $i++) {
            $page = $i+1;
            $start = $end;
            $offset = $end + $this->limit;
            $end = isset($products[$offset]) ? $offset : $count;
//            echo "start {$start}  end {$end} thispage {$page}\n";
//            continue;
           $filename = $this->getFilename($page);

            $rendered = $this->get([
                "products" =>   $products,  "pages" =>  $pages,
                "thisPage" =>   $page ,     "start" =>  $start,
                "end" => $end,  "count" => $count,
            ]);
            File::save($folder .DIRECTORY_SEPARATOR. $filename, $rendered);
        }

        $system = File::folder($folder.DIRECTORY_SEPARATOR ,"system" ). File::$dir;
//        echo "folder {$folder} system {$system}\n";
        File::copy(File::view("none.png") , $folder . File::$dir. "none.png");

        File::copy(File::view("framework.css") , $system."framework.css");


        return "file";
    }

    public function getFilename(int $page){
        if ($page == 1){
            $filename = "START.html";
        }else{
            $filename = $this->baseFileName."-{$page}.html";
        }
        return $filename;
    }

    /**
     * @param array $data
     * @return string
     */
    public function view(array $data) : string {
        extract($data);
        $file = __DIR__ . DIRECTORY_SEPARATOR . $this->dir . DIRECTORY_SEPARATOR .  $this->view .  ".php" ;

        ob_start();
        include $file;
        $buffer = ob_get_clean();
        return  $buffer === false ? 'empty' : $buffer;
    }

}