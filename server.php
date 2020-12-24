<?php


// unlimited execution time
set_time_limit(0);

include_once __DIR__.  "/vendor/autoload.php";
include_once __DIR__.  "/system/Autoloader.php";
include_once __DIR__.  "/system/config/sysvar.php";
include_once __DIR__.  "/system/config/main.php";
include_once __DIR__.  "/system/functions.php";


use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;

$host = 'http://localhost:9515';

$driver = \Facebook\WebDriver\Remote\RemoteWebDriver::create($host, \Facebook\WebDriver\Remote\DesiredCapabilities::chrome());

$loop = Factory::create();

$server = new Server(function (ServerRequestInterface $request) use($driver) {
    $payload =  $request->getBody()->getContents();
    $payload = trim($payload);
    $input = json_decode($payload , true);
    $site = $driver->get($input['url']);
    if (isset($input['url']))
        echo "requested " . $input['url'] . "\n";
    else
        echo "not isset\n";
    return new Response(
        200,
        array(
            'Content-Type' => 'application/json'
        ),
        $site->getPageSource()
    );
});
$socket = new \React\Socket\Server(isset($argv[1]) ? $argv[1] : '127.0.0.1:1111', $loop);
$server->listen($socket);
echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
$loop->run();