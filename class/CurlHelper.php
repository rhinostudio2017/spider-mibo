<?php

namespace MB;

class CurlHelper
{
    public static function getContent($options)
    {
        $curl     = self::getCurl($options);
        $response = curl_exec($curl);

        if ($response === false || curl_errno($curl)) {
            throw new \Exception('Error: Failed to exec curl to get response from curl');
        }
        return $response;
    }


    public static function getCurl($options)
    {
        $curl = curl_init();

        if ($curl === false) {
            throw new \Exception('Error: Failed to init curl');
        }

        $curlSetOptArr = curl_setopt_array($curl, $options);

        if ($curlSetOptArr === false) {
            throw new \Exception('Error: Failed to set curl options');
        }

        return $curl;
    }

    public static function closeCurl($curl)
    {
        curl_close($curl);
    }

    public static function log($message)
    {
        if (!defined('LOG_PATH')) {
            return '{LOG_PATH} is not configured correctly';
        }

        if (!file_exists(LOG_PATH)) {
            mkdir(dirname(LOG_PATH), 0777, true);
        }

        if (!($fh = fopen(LOG_PATH, 'a+'))) {
            return 'Failed to open log file {' . LOG_PATH . '}';
        }

        $message = (new \DateTime())->format('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;

        fwrite($fh, $message);

        fclose($fh);
    }
}
