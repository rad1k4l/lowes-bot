<?php
set_time_limit(0);
require './vendor/autoload.php';
include "system/autoloader.php";
include "system/functions.php";
//$re =(new request())->send("https://www.lowes.com/pl/Bedside-assistance-Accessible-home/4294644781" , false);
//echo $re['obj']->getStatusCode();
////exit();

$poolSize = @$argv[1] ? $argv[1] : 10;
$pool = new Pool($poolSize);
echo "Started With {$poolSize} threads";
$data = cache::has("analyzer_links" , false);
while (true){
//    popen("cls" , 'w');
    echo "Waiting for links...\n";
    sleep(1);
    $data = cache::has("analyzer_links" , false);
    if ($data == true) break;
}
echo "STARTED\n";
$links = cache::get("analyzer_links");

foreach ($links as $k => $link){
    if (cache::has($link)) continue;
    $pool->submit(new \worker\CacheWorker($link , $k));
}
//for ($i = 0; $i < 16; ++$i) {
//    $pool->submit(new Task($i));
//}
while ($pool->collect());
$pool->shutdown();