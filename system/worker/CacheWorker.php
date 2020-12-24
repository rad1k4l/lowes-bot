<?php
namespace worker;

use Threaded;

class CacheWorker extends Threaded
{
    private $url;
    private $count = 0;
    public function __construct(string $url , int $count = 0)
    {
        $this->count = $count;
        $this->url = $url;
    }

    public function run()
    {


        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: en\r\n".

                    "Cookie: sn=2254\r\n"
            )
        );

        $context = stream_context_create($opts);


            echo "START Task {$this->count}\n";
        try {
//            $file = file_get_contents('https://www.lowes.com' . $this->url, false, $context);
            var_dump($http_response_header[0]);
        }

        catch (\Exception $err){
            echo "ERR_STD ";
            echo $err->getCode()."\n";
            exit;
        }
//            \cache::set($this->url , $file);
            echo "END Task: {$this->count}\n";

    }
}
