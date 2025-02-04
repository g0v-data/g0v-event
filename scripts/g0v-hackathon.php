<?php

include(__DIR__ . '/../libraries/Helper.php');

$error = 0;
for ($i = 0; ; $i ++) {
    $target = __DIR__ . "/../list/g0v-hackath{$i}n.json";
    if (file_exists($target)) {
        if ($error > 10) {
            break;
        }
        continue;
    }

    $ret = new StdClass;

    error_log("fetching {$target}");
    // 檢查揪松網
    $url = "https://raw.githubusercontent.com/g0v/jothon-net/refs/heads/master/data/events/{$i}.yaml";
    $data = Helper::yamlToJSONURL($url);
    foreach ($data as $k => $v) {
        if (in_array($k, [
            'name',
            'description',
        ])) {
            $ret->{$k} = $v;
        } elseif($k == 'id') {
            $ret->id = "g0v-hackath{$v}n";
        } elseif($k == 'date') {
            $ret->time = new StdClass;
            $ret->time->date = $v;
        } elseif (in_array($k, [
            'video_pitch',
            'video_talk',
            'video_demo',
        ])) {
            if (!property_exists($ret, 'video')) {
                $ret->video = new StdClass;
            }
            $ret->video->{$k} = $v;
        } else {
            continue;
        }
        unset($data->{$k});
    }

    if (!property_exists($ret, 'link')) {
        $ret->link = new StdClass;
    }

    if (property_exists($data, 'usebookmode') and $data->usebookmode) {
        $ret->link->collaborate = "https://g0v.hackmd.io/@jothon/g0v-hackath{$i}n";
    } else {
        $ret->link->collaborate = "https://beta.hackfoldr.org/g0v-hackath{$i}n";
    }
    unset($data->usebookmode);

    unset($data->subtitle);
    if (json_encode($data) != '{}') {
        print_r($data);
        throw new Exception("{$target} 有多餘資料");
    }

    $kktix_id = $ret->id;
    if ($kktix_id == 'g0v-hackath0n') {
        $kktix_id = 'dab224';
    } elseif ($kktix_id == 'g0v-hackath2n') {
        $kktix_id ='g0v-hackath2n-taipei';
    } elseif ($kktix_id == 'g0v-hackath3n') {
        $kktix_id ='g0v-hackath3n-taipei';
    } elseif ($kktix_id == 'g0v-hackath4n') {
        $kktix_id ='g0v-hackath4n-taipei';
    } elseif ($kktix_id == 'g0v-hackath5n') {
        $kktix_id ='g0v-hackath5n-taipei';
    } elseif (in_array($kktix_id, [
        'g0v-hackath10n', // 資料科學年會黑客松
        'g0v-hackath38n', // 在家
        'g0v-hackath39n', // 又在家
    ])) {
        continue;
    }

    // check kktix
    $kktix_data = null;
    foreach (['g0v-tw', 'g0v-jothon'] as $workspace) {
        $url = "https://{$workspace}.kktix.cc/events/{$kktix_id}";
        $content = file_get_contents($url);
        if (!$content) {
            continue;
        }
        $doc = new DOMDocument;
        @$doc->loadHTML($content);
        // script type=application/ld+json
        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query('//script[@type="application/ld+json"]');
        $json = $nodes->item(0)->nodeValue;
        if (!$data = json_decode($json)) {
            continue;
        }
        $kktix_data = $data;
        break;
    }
    if (is_null($kktix_data)) {
        throw new Exception("找不到 {$kktix_id}");
    }
    $kktix_data = $kktix_data[0];
    $ret->link->event = $kktix_data->url;
    if (is_null($ret->time)) {
        print_r($ret);
        exit;
    }
    $ret->time->time_start = $kktix_data->startDate;
    $ret->time->time_end = $kktix_data->endDate;
    $ret->location = new StdClass;
    $ret->location->address = $kktix_data->location->address;
    $ret->location->name = $kktix_data->location->name;

    file_put_contents($target, json_encode($ret, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
