<?php
/**
 * Created by PhpStorm.
 * User: xubowen
 * Date: 2018/1/12
 * Time: 下午6:34
 */
$str=$_POST['content'];
push("http://ip:9999/push",'{"hello":"'.$str.'","broadcast":true,"condition":""}');
function push($url,$info){
    $ch = curl_init();
    $timeout = 5;
    curl_setopt ($ch, CURLOPT_URL,$url);
    curl_setopt ($ch, CURLOPT_POST, 1);
    curl_setopt ($ch, CURLOPT_POSTFIELDS, $info);
    $result = curl_exec($ch);
    curl_close ($ch);
    return $result;
}

