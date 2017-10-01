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

        $regexp = '/<a target=blank href="(.*)"><img class="moduleFeaturedThumb" height="90" src="(.*)" width="120" \/><\/a>/';
        if (!preg_match_all($regexp, $content, $matches, PREG_SET_ORDER)) {
            return false;
        }

        // Iterate each regexp and scrap the related video node(in the player page)
        foreach ($matches as $match) {
            $videoLink = $match[1];
            $imageLink = $match[2];
            echo $imageLink . ': ' . $videoLink . PHP_EOL;

            $this->setUrl($videoLink);
            $videoContent = $this->getContent();
            //echo $videoContent . PHP_EOL;
            CurlHelper::log($videoContent);
            // extract video link
            $videoRegExp = '/readonly="readOnly">([^<]*)<\/textarea>/';
            if (!preg_match($videoRegExp, $videoContent, $videoMatch)) {
                break;
            }
            echo $videoMatch[1] . PHP_EOL . PHP_EOL;
            /*
            // extract video name
            $videoRegExp = '/<form id="linkForm2".*readonly="readOnly">(.*)<\/textarea><\/form>/';
            if (!preg_match($videoRegExp, $videoContent, $videoMatch)) {
                echo $videoMatch[1] . PHP_EOL . PHP_EOL;
            }
            // extract video description
            $videoRegExp = '/<form id="linkForm2".*readonly="readOnly">(.*)<\/textarea><\/form>/';
            if (!preg_match($videoRegExp, $videoContent, $videoMatch)) {
                echo $videoMatch[1] . PHP_EOL . PHP_EOL;
            }
            // extract video author
            $videoRegExp = '/<form id="linkForm2".*readonly="readOnly">(.*)<\/textarea><\/form>/';
            if (!preg_match($videoRegExp, $videoContent, $videoMatch)) {
                echo $videoMatch[1] . PHP_EOL . PHP_EOL;
            }
            // extract video views
            $videoRegExp = '/<form id="linkForm2".*readonly="readOnly">(.*)<\/textarea><\/form>/';
            if (!preg_match($videoRegExp, $videoContent, $videoMatch)) {
                echo $videoMatch[1] . PHP_EOL . PHP_EOL;
            }
            */

            break;
        }

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