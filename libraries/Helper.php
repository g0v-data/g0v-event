<?php

class Helper
{
    public static function yamlToJSONURL($url)
    {
        $api_url = 'https://yaml2json.openfun.app';

        $data = file_get_contents($url);
        if ($data === false) {
            throw new Exception("file_get_contents failed: {$url}");
        }
        $error = 0;
        $curl = curl_init($api_url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = json_decode(curl_exec($curl));
        return $response;
    }
}
