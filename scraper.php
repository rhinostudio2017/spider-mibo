<?php

require __DIR__ . '/autoload.php';
require __DIR__ . '/config/common.php';

$scraper = new MB\Scraper('http://91porn.com/index.php');

//\MB\CurlHelper::log($scraper->getContent());

$scraper->scrapPage();
