<?php
use GuzzleHttp\Client;

class Request
{
    private $client ;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri'=>'https://www.lowes.com',
            'http_errors' => false
        ]);

        return $this;
    }
    public function sendData(string $uri, string $data){
        $client = new Client(
            ["base_uri" => "https://azebot.ga",]
        );
        $params = [
            'form_params' =>[
                'data' => $data
            ],
        ];

         return $client->request('POST' , $uri , $params);
    }

    public function send($uri ,  $cache  = true ,$method = "GET"){
        $data = cache::get($uri);
        if ($data !== null && $cache == true) {
            return [ 'obj' => null , 'payload' => $data];
        }

        $params = [
            "headers" => [
                'User-Agent' => "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36",
                'Cookie' => 'sn=2254',
            ],
        ];
        $response = [];
        while(true) {
            try {
                $response = $this->client->request($method, $uri, $params);
                break;
            } catch (\Exception $error) {
                echo "<br>ERR_MSG : " . print_r($error->getMessage());
                echo "<br>ERR_CODE : " . print_r($error->getCode());
                echo "<br>QUERY_STRING : " . print_r($uri);
                echo "<br>PARAMS : " . print_r($params);
                echo "<br>ENDPOINT_NAME : " . self::class;
                echo "<br>";
                \out\console::input("network error :: retry ?");
            }
        }
        $content = $response->getBody()->getContents();
       if ($cache)
           cache::set($uri , $content);

        return ['obj'=>$response, 'payload' => $content ];
    }

    public function departments(){
        $response = $this->send('/c/Departments');
        return $response
        ['payload'];
    }



}