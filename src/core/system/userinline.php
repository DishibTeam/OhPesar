<?php

function MakeVoiceResponde($voiceinfo){
    return [
        'type' => 'voice',
        'id' => $voiceinfo['unique_id'].'__'.base64_encode(rand()),
        'voice_url' =>  $voiceinfo['url'],
        'title' => $voiceinfo['mode'] == 'private' ? '🔐 '.$voiceinfo['name'] : $voiceinfo['name'],
    ];
}

function startsWith ($string, $startString)
{
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

function arraypos($text, $array){
    foreach ($array as $i) {
        if(strpos($text, $i) !== false){
            return true;
        }
    }
    return false;
}

function SearchFilter($voiceinfo, $userinline, $inlineuserid, $inline_text){
    $kwargs = ['-id'];
    if($userinline['badvoices'] == 0){
        if( IsBadWord($voiceinfo['name']) ) return false;
    }
    if((strtolower($voiceinfo['mode']) == 'private') && (intval($voiceinfo['sender']) !== intval($inlineuserid))){ return false; }
    elseif(!$voiceinfo['accepted'] && strtolower($voiceinfo['mode']) == 'public'){ return false; }
    if((!(strpos(strtolower($voiceinfo['name']), strtolower($inline_text)) !== false) && strlen($inline_text) > 1) && !arraypos($inline_text, $kwargs)){ return false; }
    return true;
}

if(!is_null($inline_text)){
    $start_time = microtime(true);
    $inline_text = trim($inline_text);
    $results = [];
    $inlineuserid = $update->inline_query->from->id;
    $userinline = mysqli_fetch_assoc(mysqli_query($db, "SELECT * FROM `user` WHERE `id` = '{$inlineuserid}' LIMIT 1"));
    if(!$userinline){
        Bot('answerInlineQuery', [
            'inline_query_id' => $membercalls,
            'results' => json_encode($results),
            'switch_pm_text'=> 'برای استفاده از ربات باید ربات را استارت بزنید',
            'switch_pm_parameter'=> 'startforuse'
        ]);
        exit();
    }
    
    
    if(strpos($inline_text, '-id ') !== false){
        $inline_vid = str_replace('-id ', '', $inline_text);
        $querystring = "SELECT * FROM `voices` WHERE `id` = '{$inline_vid}' LIMIT 1";
    }

    elseif(strpos($inline_text, '-private') !== false){
        $inline_text = trim(str_replace('-private', '', $inline_text));
        $querystring = "SELECT * FROM `voices` WHERE `mode` = 'private'";
    }

    elseif(strpos($inline_text, '-public') !== false){
        $inline_text = trim(str_replace('-public', '', $inline_text));
        $querystring = "SELECT * FROM `voices` WHERE `mode` = 'public'";
    }

    elseif(strpos($inline_text, '-me') !== false){
        $inline_text = trim(str_replace('-me', '', $inline_text));
        $querystring = "SELECT * FROM `voices` WHERE `sender` = '{$inlineuserid}'";
    }
    
    elseif($userinline['sortby'] == 'newest'){
        $querystring = "SELECT * FROM `voices` ORDER BY `voices`.`id` DESC";
    }elseif($userinline['sortby'] == 'popularest'){
        $querystring = "SELECT * FROM `voices` ORDER BY `voices`.`usecount` DESC";
    }else{
        $querystring = "SELECT * FROM `voices` ORDER BY `voices`.`id` ASC";
    }
    $query = mysqli_query($db, $querystring);
    if(mysqli_num_rows($query) == 1){
        $voiceinfo = mysqli_fetch_assoc($query);
        if(SearchFilter($voiceinfo, $userinline, $inlineuserid, $inline_text))
            $results[] = MakeVoiceResponde($voiceinfo);
    }else{
        while ($voiceinfo = mysqli_fetch_assoc($query)) {
            if(SearchFilter($voiceinfo, $userinline, $inlineuserid, $inline_text))
                $results[] = MakeVoiceResponde($voiceinfo);
        }
    }
    $result_count = count($results);
    $results = array_splice($results, 0, 20, true);
    $dataval = [
        'inline_query_id' => $membercalls,
        'results' => json_encode($results),
        'is_personal'=> true,
        'cache_time'=> 1
    ];
    if($results == []){
        $dataval['switch_pm_text'] = 'نتیجه خاصی پیدا نشد';
        $dataval['switch_pm_parameter'] = 'noresult';
    }
    elseif(strlen($inline_text) < 1){
        $dataval['switch_pm_text'] = 'ارسال ویس جدید';
        $dataval['switch_pm_parameter'] = 'sendvoice';
    }
    elseif(!in_array('switch_pm_text', $dataval)){
        $time_end = microtime(true);
        $wait = round($time_end - $start_time, 4);
        if($result_count > 10){
            $dataval['switch_pm_text'] = "نتیجه جستوجو $result_count ویس در $wait ثانیه";
            $dataval['switch_pm_parameter'] = 'start';
        }
        
    }
    Bot('answerInlineQuery', $dataval);
}