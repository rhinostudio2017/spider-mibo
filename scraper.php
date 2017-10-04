<?php

require __DIR__ . '/autoload.php';
require __DIR__ . '/config/common.php';

$pageLink = 'http://91porn.com/video.php?category=rf&page=111';

$scraper = new MB\Scraper($pageLink);

//\MB\CurlHelper::log($scraper->getContent());

$scraper->scrapPage();
