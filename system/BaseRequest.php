<?php

use GuzzleHttp\Client;

class BaseRequest
{
    public $client;
    protected $root = false;
    private static $configured;

    public function __construct() 
    {
        $this->client = new Client([
            'http_errors' => false
        ]);
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    public function sendData(string $uri , string $data){
        
        $params = [
            'form_params' => [
                'data' => $data
            ],
        ];
        return $this->client->request( 'POST' , $uri , $params );
    }

    public function json(string $uri ,array $data , bool $cache= true ){
        $response = $this->client->request('POST', $uri, [
            'json' => $data
        ]);
        $content = $response->getBody()->getContents();
        return [ 'obj' => $response, 'payload' => $content ];
    }




    public function send($uri, $cache  = true, $method = "GET"){
        $uri = $this->root !== false ? $this->root . $uri : $uri;
        $data = cache::get($uri , null);
        if ($data !== null && $cache === true) {
            return [
                'obj' => null,
                'payload' => $data,
                'cache' => true,
            ];
        }

        $params = [
//            'debug'=> true,
            "proxy"=>[
                "https" => "195.122.185.95:3128",
            ],
            "headers" => [
                'User-Agent' => "Mozilla/5.0 (Windows NT 6.3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 YaBrowser/17.6.1.749 Yowser/2.5 Safari/537.36",
                'Cookie' => 'sn=2254',
            ],
        ];

        try{
//            echo $this->root ; exit();


            $response = $this->client->request($method , $uri , $params);

        }catch (\Exception $error){
            echo  "\nERR_MSG : ".print_r( $error->getMessage() );
            echo  "\nERR_CODE : ".print_r( $error->getCode() );
            echo  "\nQUERY_STRING : ".print_r(  $uri);
            // echo  "\nPARAMS : ".print_r(  $params);
            echo  "\nENDPOINT_NAME : ". self::class;
            echo  "\n";
            exit(1);
        }

        $content = $response->getBody()->getContents();
        cache::set($uri , $content);
        return [ 'obj' => $response, 'payload' => $content ];
    }

    public function browser($uri, $cache = true){
        \out\console::print("\trequesting to browser for html...");

        if(self::$configured == false){
            $this->_browser($uri,$cache);
            \out\console::input("WebBrowser not connfigured\nWaiting to config browser...");
            self::$configured= true;
        }

        return $this->_browser($uri,$cache);
    }

    protected function _browser($uri, $cache  = true){

//        $uri = $this->root !== false ? $this->root . $uri : $uri;
        $data = cache::get($uri , null);
        if ($data !== null && $cache === true) {
            return [
                'payload' => $data,
                'cache' => true,
            ];
        }
        $response = [];
        while (true)
        {
            $response = $this->json(conf("webdrive.host"), ["url"=>$uri]);
            $status = $response['obj']->getStatusCode();
            \out\console::print("\tstatus {$status}");
            if($status == 200){
                break;
            }else
                \out\console::input("RESPONSE: HTTP_STATUS " . $status .". Type for retry...");
        }
        cache::set($uri , $response['payload']);
        return [ 'payload' => $response['payload'] ];
    }

}