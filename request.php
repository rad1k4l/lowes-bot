<?php
include "connector.php";
use GuzzleHttp\Client;
$client = new Client([
    'http_errors' => false
]);
$limit = 20;
$requests = [];
for ($i =0; ; $i++) {
 if($i == $limit) break;
    $promise = $client->getAsync('http://127.0.0.1/stand.php');
    $promise->then(
        function (\Psr\Http\Message\ResponseInterface $res) {
            echo "requested -> " . $res->getStatusCode() . "\n";
        },
        function (\GuzzleHttp\Exception\RequestException $e) {
            echo $e->getMessage() . "\n";
            echo $e->getRequest()->getMethod();
        }
    );
    $requests [] = $promise;
}

    foreach ($requests as $k =>  $request) {
        echo "response {$k}\n";
        $request->wait();
    }




