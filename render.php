<?php
include "connector.php";

echo "loading data to memory\n";
$products = cache::get("amazon.result" , []);
$html   = new \renderer\TableRenderer();

echo "rendering in html\n";
$html->render($products);

echo "success\n";