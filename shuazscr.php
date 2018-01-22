<?php
//$str='【芝士超人】登录验证码：873459 。请不要向他人泄露，保护账户安全。本短信由系统自动发出，如非本人操作请忽略。';
if(!isset($argv[1])){
    echo '请输入邀请码'."\n";
    exit;
}
$uName='';
$pWord='';
$Developer='';
$resLogin=apiLogin($uName,$pWord,$Developer);
$resArray=explode("&",$resLogin);
$token=$resArray[0];
$idArray=[['西瓜视频','132365'],['冲顶大会','181815'],['芝士超人','183628'],['花椒视频','16853'],['一直播','48549']];
$itemId=$idArray[2][1];
$phone=apiGetPhone($itemId,$token);
$phone=str_replace(';','',$phone); 
echo "获取手机号--".$phone."\n";
$url='http://service.h7tuho5mf.cn/api/v1/verification_code';
$post='{"region":"cn","phone":"86'.$phone.'"}';
$re=apiRequest($url,$post);
$json=json_decode($re, true);
echo "发送验证码--".$json['error_msg']."\n";
$request_id=$json['request_id'];
$code=$argv[1];//'4WSYQ';//3M9UE
$time=12;
echo '验证码获取中';
while($time--){
    echo '.';
    $msg=apiGetVerCode($itemId,$token,$phone);
    $res=judgeFalse($msg);
    if($res){
        $msgArray=explode("&",$msg);
        preg_match('/[1-9]\d*/', $msgArray[3], $matches);
        $url='http://service.h7tuho5mf.cn/api/v1/login';
        $post='{"code":"'.$matches[0].'","phone":"86'.$phone.'","request_id":"'.$request_id.'","platform":"phone"}';
        $re=apiRequest($url,$post);
        $json=json_decode($re, true);
        echo "\n登录芝士--".$json['error_msg']."\n";
        $uid=$json['uid'];
        $session=$json['session'];
        $url='http://service.h7tuho5mf.cn/api/invite_code/bind?code='.$code.'&uid='.$uid.'&sid='.$session;
        $re=apiRequest($url);
        $json=json_decode($re, true);
        echo "获取复活卡--".$json['error_msg']."\n";
        apiExit($token);
        echo "退出";
        break;
    }
    sleep(5);
}

//判断是否包含False
function judgeFalse($str){
    if(strpos($str,"False") !== false){//包含
        return false;
    }else {//不包含
        return true;
    }
}
//获取手机号
function apiGetPhone($itemId,$token){
    $url='http://api.shjmpt.com:9002/pubApi/GetPhone?ItemId='.$itemId.'&token='.$token;
    return apiRequest($url);
}
//登录
function apiLogin($uName,$pWord,$Developer){
    $url='http://api.shjmpt.com:9002/pubApi/uLogin?uName='.$uName.'&pWord='.$pWord.'&Developer='.$Developer;
    return apiRequest($url);
}
//退出
function apiExit($token){
    $url='http://api.shjmpt.com:9002/pubApi/uExit?token='.$token;
    return apiRequest($url);
}
//获取短信验证
function apiGetVerCode($itemId,$token,$phone){
    $url='http://api.shjmpt.com:9002/pubApi/GMessage?token='.$token.'&ItemId='.$itemId.'&Phone='.$phone;
    return apiRequest($url);
}
//请求
function apiRequest($url,$post = ''){
    $ch = curl_init();
    $timeout = 5;
    curl_setopt ($ch, CURLOPT_URL,$url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    if( $post != '' && !empty( $post ) ){
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $html = curl_exec($ch);
    curl_close ($ch);
    return $html;
}