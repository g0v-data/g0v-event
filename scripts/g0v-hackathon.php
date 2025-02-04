<?php

$api_url = 'https://yaml2json.openfun.app';

$error = 0;
for ($i = 0; ; $i ++) {
    $target = __DIR__ . "/../list/g0v-hackath{$i}n.json";
    if (file_exists($target)) {
        if ($error > 10) {
            break;
        }
        continue;
    }

    error_log("fetching {$target}");
    $url = "https://raw.githubusercontent.com/g0v/jothon-net/refs/heads/master/data/events/{$i}.yaml";
    $data = file_get_contents($url);
    if ($data === false) {
        $error ++;
        break;
    }
    $error = 0;
    $curl = curl_init($api_url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = json_decode(curl_exec($curl));

    file_put_contents($target, json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}
