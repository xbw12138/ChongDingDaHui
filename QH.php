<?php
/**
 * Created by PhpStorm.
 * User: xubowen
 * Date: 2018/1/12
 * Time: 下午2:03
 */
header('Content-type: application/json; charset=UTF-8');
//https://www.google.com/search?q=电视剧《乡村爱情》中%2C角色“谢大脚”在剧中的本名+谢红
while(true){
    //$dirt='{"code":0,"msg":"成功","data":{"event":{"answerTime":10,"desc":"10.哪个品牌目前不生产跑车?  ","displayOrder":9,"liveId":98,"options":"[\"五菱宏光\",\"法拉利\",\"兰博基尼\"]","questionId":1183,"showTime":1515733995489,"status":0,"type":"showQuestion"},"type":"showQuestion"}}';
    $dirt=getQuestion('http://htpmsg.jiecaojingxuan.com/msg/current');
    $json=json_decode($dirt, true);
    $ques_msg=$json['msg'];
    if($ques_msg=='no data '){
        sleep(1);echo ".";
    }else if($ques_msg=='成功'||$json['data']['type']=='showQuestion'){
        $order=$json['data']['event']['displayOrder'];
        $ques_desc=$json['data']['event']['desc'];
        $ques_options=$json['data']['event']['options'];
        echo "\n-----------------------------\n";
        echo $ques_desc."\n".$ques_options."\n";
        echo "-----------------------------\n";
        echo "结果统计:\n";
        $ques_desc=formString($ques_desc);
        $ques_options=formOptions($ques_options);
        echo "-----------------------------\n";
        $rr=getAnswer($ques_desc,$ques_options,$order);//统计结果
        echo "\n-----------------------------\n";
        $pre=getDescOptAnswer($ques_desc,$ques_options,$order);//精确结果
        echo "\n-----------------------------\n";
        $finalresult=$rr."推荐答案:".$pre;
        echo $finalresult."\n";
        push("http://182.254.146.68:9999/push",'{"hello":"'.$finalresult.'","broadcast":true,"condition":""}');
        sleep(10);
        if($order==11)break;
    }else{
        sleep(1);echo "。";
    }
}
//根据问题描述加选项搜索结果数量来返回答案
function getDescOptAnswer($ques_desc,$ques_options,$order){
    $result="";
    $answer="";
    $max=0;
    //脚本版
    $oo=$ques_options;
    for($i=0;$i<sizeof($oo);$i++){
        $count=getBaiduCount($ques_desc,$oo[$i]);
        if($count>$max){
            $max=$count;
            $answer=$oo[$i];
        }
        $result=$result.$oo[$i]."(".$count.") ";
    }
    $order++;
    $str=$order.".".$result;
    echo "百度结果统计：".$str;
    return $answer;
}
//获取百度搜索结果个数
function getBaiduCount($ques_desc,$ques_option){//问题加单个选项
    $baiduAnswer=getBaidu($ques_desc."%20".$ques_option);
    if($baiduAnswer=="")return 0;
    $pattern = '/<i class="c-icon searchTool-spanner c-icon-setting"><\/i>(.+?)<\/div>(.+?)<\/div><\/div><\/div>/';
    $isempty=preg_match($pattern, $baiduAnswer, $match);
    if($isempty){//搜索有结果
        return findNum($match[2]);
    }else{
        return 0;
    }
}
//提取字符串中数字
function findNum($str=''){
    $str=trim($str);
    if(empty($str)){return '';}
    $result='';
    for($i=0;$i<strlen($str);$i++){
        if(is_numeric($str[$i])){
            $result.=$str[$i];
        }
    }
    return $result;
}
//问题否定
function judgeQuestion($ques_desc){
    if(strpos($ques_desc,"不是") !== false){
        return false;
    }else {
        return true;
    }
}
//获取结果
function getAnswer($ques_desc,$ques_options,$order){
    $baiduAnswer=simpBaidu(getBaidu($ques_desc));
    if($baiduAnswer=="")return ;
    $result="";
    //脚本版
    $oo=$ques_options;
    for($i=0;$i<sizeof($oo);$i++){
        $result=$result.$oo[$i]."(".substr_count($baiduAnswer,$oo[$i]).") ";
        //echo $oo[$i]."(".substr_count($baiduAnswer,$oo[$i]).")\n";
    }
    $order++;
    $str=$order.".".$result;
    echo "关键词统计：".$str;
    return $str;
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
    $isempty=preg_match($pattern, $string, $match);
    if($isempty){
        return trimall($match[0]);
    }else return "";
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
/*function formString($str){
    $desc=$str;
    $desc=chop($desc,"？");//去掉？
    $desc=chop($desc,"?");//去掉?
    $desc=chop($desc," ");//去掉空格
    $index=strpos($desc,".")+1;//获取.的位置
    $res=substr($desc, $index);
    //echo "格式化问题---".$res;
    return $res; //截取.后的内容
}*/
//格式化问题内容
function formString($str){
    $desc=$str;
    $desc=str_replace('？','',$desc); 
    $desc=str_replace('?','',$desc); 
    $desc=str_replace(' ','',$desc);
    $index=strpos($desc,".")+1;//获取.的位置
    $res=substr($desc, $index);
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
