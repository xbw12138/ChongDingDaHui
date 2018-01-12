<?php
/**
 * Created by PhpStorm.
 * User: xubowen
 * Date: 2018/1/12
 * Time: 下午2:03
 */
header('Content-type: application/json; charset=UTF-8');
//http://htpmsg.jiecaojingxuan.com/msg/current
//{"code":0,"msg":"no data"}

//$dirt='{"code":0,"msg":"成功","data":{"event":{"answerTime":10,"desc":"11.动画片《哆啦a梦》中的胖虎是什么星座？","displayOrder":10,"liveId":98,"options":"[\"双子座\",\"白羊座\",\"狮子座\"]","questionId":1184,"showTime":1515734054248,"status":0,"type":"showQuestion"},"type":"showQuestion"}}';
//$dirt=getQuestion('http://htpmsg.jiecaojingxuan.com/msg/current');
//$dirt='{"code":0,"msg":"成功","data":{"event":{"answerTime":10,"correctOption":0,"desc":"2.冯小刚导演的电影《芳华》，改编自谁的作品？","displayOrder":1,"liveId":98,"options":"[\"严歌苓\",\"张爱玲\",\"三毛\"]","questionId":1175,"showTime":1515733542277,"stats":[402096,90527,22795],"status":2,"type":"showAnswer"},"type":"showAnswer"}}';
//$json=json_decode($dirt, true);
//$ques_msg=$json['msg'];
$order=1;
while(true){
    //$dirt='{"code":0,"msg":"成功","data":{"event":{"answerTime":10,"desc":"11.动画片《哆啦a梦》中的胖虎是什么星座？","displayOrder":1,"liveId":98,"options":"[\"双子座\",\"白羊座\",\"狮子座\"]","questionId":1184,"showTime":1515734054248,"status":0,"type":"showQuestion"},"type":"showQuestion"}}';
    $dirt=getQuestion('http://htpmsg.jiecaojingxuan.com/msg/current');
    $json=json_decode($dirt, true);
    $ques_msg=$json['msg'];
    if($ques_msg=='no data'){
        sleep(1);
        echo ".";
    }else if($json['data']['event']['displayOrder']==$order){
        $ques_desc=$json['data']['event']['desc'];
        $ques_options=$json['data']['event']['options'];
        echo "\n-----------------------------\n";
        echo $ques_desc."\n".$ques_options."\n";
        echo "结果统计:\n";
        getAnswer($ques_desc,$ques_options,$order);
        echo "-----------------------------\n";
        $order++;
        sleep(10);
    }else{
        sleep(1);
        echo ".";
    }
    if($order==12)break;
}
//获取结果
function getAnswer($ques_desc,$ques_options,$order){
    $baiduAnswer=simpBaidu(getBaidu(formString($ques_desc)));
    $result="";
    foreach (formOptions($ques_options) as &$select){
        $result=$result.$select."(".substr_count($baiduAnswer,$select).") ";
        echo $select."       ".substr_count($baiduAnswer,$select)."\n";
    }
    $order++;
    $str=$order.".".$result;
    //推送服务器自行搭建
    push("http://ip:9999/push",'{"hello":"'.$str.'","broadcast":true,"condition":""}');
}
//删除空格
function trimall($str){
    $oldchar=array(" ","　","\t","\n","\r");
    $newchar=array("","","","","");
    return str_replace($oldchar,$newchar,$str);
}
//格式化options
function formOptions($str){
    if($str=="") return ;
    $result = array();
    preg_match_all("/(?:\[)(.*)(?:\])/i",$str, $result);
    preg_match_all("#\"(.*?)\"#i",$result[1][0], $result);
    return $result[1];
}
//获取问题
function getQuestion($url){
    $ch = curl_init();
    $timeout = 5;
    curl_setopt ($ch, CURLOPT_URL,$url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $html = curl_exec($ch);
    curl_close ($ch);
    return $html;
}
//精简百度搜索内容
function simpBaidu($string){
    if($string=="") return ;
    $pattern = '/<div id="content_left">(.+?)<div style="clear:both;height:0;">/is';
    preg_match($pattern, $string, $match);
    return trimall($match[0]);
}
//获取百度搜索结果
function getBaidu($desc){
    $url = "http://www.baidu.com/s?wd=".$desc;
    $ch = curl_init ();
    $timeout = 5;
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.131 Safari/537.36');
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $content = curl_exec($ch);
    curl_close ($ch);
    return $content;
}
//格式化问题内容
function formString($str){
    $desc=$str;
    $desc=chop($desc,"？");//去掉？
    $desc=chop($desc,"?");//去掉?
    $index=strpos($desc,".")+1;//获取.的位置
    $res=substr($desc, $index);
    //echo "格式化问题---".$res;
    return $res; //截取.后的内容
}
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
