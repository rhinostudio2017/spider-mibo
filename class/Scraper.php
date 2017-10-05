<?php

namespace MB;

class Scraper
{
    private $url;

    public function __construct($url = null)
    {
        $this->setUrl($url);
    }

    #region Methods
    public function getContent()
    {
        $options = [
            CURLOPT_URL            => $this->url,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true
        ];
        return CurlHelper::getContent($options);
    }

    public function scrapPage()
    {
        $content = $this->getContent();
        //CurlHelper::log($content, 'tmp');

        // For home page
        //$regexp = '/<a target=blank href="(.*)"><img class="moduleFeaturedThumb" height="90" src="(.*)" width="120" \/><\/a>/';
        // For pagination page
        $regexp = '/<div class="imagechannel(?:hd)?">[\r\n](?:<img[^>]*>[\r\n])?<a target=blank href="(\S*)">[\r\n]<img src="(\S*)"[^>]*>/';
        if (!preg_match_all($regexp, $content, $matches, PREG_SET_ORDER)) {
            return false;
        }

        // Iterate each regexp and scrap the related video node(in the player page)
        $log = 'Total: ' . count($matches) . PHP_EOL;
        $i = 0;
        foreach ($matches as $match) {
            $log .= 'No.: ' . ++$i . PHP_EOL;
            $videoLink = $match[1];
            $imageLink = $match[2];
            echo 'poster_link: ' . $imageLink . PHP_EOL;
            $log .= 'poster_link: ' . $imageLink . PHP_EOL;

            $this->setUrl($videoLink);
            $videoContent = $this->getContent();
            //CurlHelper::log($videoContent, 'tmp');

            // extract video link
            $videoRegExp = '/readonly="readOnly">([^<]*)<\/textarea>/';
            if (!preg_match($videoRegExp, $videoContent, $videoMatch)) {
                echo 'video_link: failed to extracted' . PHP_EOL;
                $log .= 'video_link: failed to extracted' . PHP_EOL;
            }else{
                echo 'video_link: ' . $videoMatch[1] . PHP_EOL;
                $log .= 'video_link: ' . $videoMatch[1] . PHP_EOL;
            }

            // extract video name
            $nameRegExp = '/<div id="viewvideo-title">[\r\n](.*)[\r\n]<\/div>/';
            if (!preg_match($nameRegExp, $videoContent, $nameMatch)) {
                echo 'name: failed to extracted' . PHP_EOL;
                $log .= 'name: failed to extracted' . PHP_EOL;
            }else{
                echo 'name: ' . $nameMatch[1] . PHP_EOL;
                $log .= 'name: ' . $nameMatch[1] . PHP_EOL;
            }

            // extract video description
            $descriptionRegExp = '/<meta name="title" content="(.*)[\r\n]*" \/>/';
            if (!preg_match($descriptionRegExp, $videoContent, $descriptionMatch)) {
                echo 'description: failed to extracted' . PHP_EOL;
                $log .= 'description: failed to extracted' . PHP_EOL;
            }else{
                echo 'description: ' . $descriptionMatch[1] . PHP_EOL;
                $log .= 'description: ' . $descriptionMatch[1] . PHP_EOL;
            }

            // extract video author
            $authorRegExp = '/<span class="info">From: <\/span>[\r\n]<a[^>]*><span class="title">(.*)<\/span>/';
            if (!preg_match($authorRegExp, $videoContent, $authorMatch)) {
                echo 'author: failed to extracted' . PHP_EOL;
                $log .= 'author: failed to extracted' . PHP_EOL;
            }else{
                echo 'author: ' . $authorMatch[1] . PHP_EOL;
                $log .= 'author: ' . $authorMatch[1] . PHP_EOL;
            }

            // extract video release_time
            $releaseTimeRegExp = '/<span class="info">Added: <\/span><span class="title">(.*)<\/span>/';
            if (!preg_match($releaseTimeRegExp, $videoContent, $releaseTimeMatch)) {
                echo 'produce_time: failed to extracted' . PHP_EOL;
                $log .= 'produce_time: failed to extracted' . PHP_EOL;
            }else{
                echo 'produce_time: ' . $releaseTimeMatch[1] . PHP_EOL;
                $log .= 'produce_time: ' . $releaseTimeMatch[1] . PHP_EOL;
            }

            // extract video run_time & views
            $runTimeViewsRegExp = '/<span class="info">Runtime:<\/span>(.*)[\r\n].*<span class="info"> Views:<\/span>(.*)<span class="info"> Comments/';
            if (!preg_match($runTimeViewsRegExp, $videoContent, $runTimeViewsMatch)) {
                echo 'run_time: failed to extracted' . PHP_EOL;
                echo 'views: failed to extracted' . PHP_EOL;
                $log .= 'run_time: failed to extracted' . PHP_EOL;
                $log .= 'views: failed to extracted' . PHP_EOL;
            }else{
                echo 'run_time: ' . $runTimeViewsMatch[1] . PHP_EOL;
                echo 'views: ' . $runTimeViewsMatch[2] . PHP_EOL;
                $log .= 'run_time: ' . $runTimeViewsMatch[1] . PHP_EOL;
                $log .= 'views: ' . $runTimeViewsMatch[2] . PHP_EOL;
            }

            echo PHP_EOL . PHP_EOL;
            $log .= PHP_EOL . PHP_EOL;
        }

        CurlHelper::log($log);
    }

    public function scrapPageTest()
    {
        $content = $this->getContent();

        $doc = new \DOMDocument();
        //$doc->loadHTML($this->convertEncoding($content, 'UTF-8'));
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        $xPath = new \DOMXPath($doc);

        // Query image list
        $imageXPath    = '//img[@class="moduleFeaturedThumb"]';
        $imageNodeList = $xPath->query($imageXPath);
        if ($imageNodeList === false || $imageNodeList->length === 0) {
            return false;
        }

        // Iterate each image node and scrap the related video node(in the player page)
        for ($i = 0; $i < $imageNodeList->length; $i++) {
            $imageNode = $imageNodeList->item($i);
            $videoLink = $imageNode->parentNode()->attributes->getNamedItem('href');
            echo $i . ': ' . $videoLink . PHP_EOL;
        }

    }
    #endregion

    #region Getters & Setters
    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }
    #endregion

    #region Utils
    private function convertEncoding($source, $targetEncoding)
    {
        // detect the character encoding of the incoming file
        $encoding = mb_detect_encoding($source, "auto");

        // escape all of the question marks so we can remove artifacts from
        // the unicode conversion process
        $target = str_replace("?", "[question_mark]", $source);

        // convert the string to the target encoding
        $target = mb_convert_encoding($target, $targetEncoding, $encoding);

        // remove any question marks that have been introduced because of illegal characters
        $target = str_replace("?", "", $target);

        // replace the token string "[question_mark]" with the symbol "?"
        $target = str_replace("[question_mark]", "?", $target);

        return $target;
    }
    #endregion
}