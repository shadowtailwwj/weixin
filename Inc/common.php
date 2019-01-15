<?php
header("Content-Type:text/html;charset=UTF-8");
/**
 *公共函数库
 */

function getResult($arr){
    $portid=(is_array($arr) && array_key_exists("portid",$arr))?CheckId($arr["portid"]):0;
    $send=(is_array($arr) && array_key_exists("send",$arr))?$arr["send"]:"";
    $listid=(is_array($arr) && array_key_exists("listid",$arr))?CheckId($arr["listid"]):0;
    $where=(is_array($arr) && array_key_exists("where",$arr))?$arr["where"]:"";
    $skeys=(is_array($arr) && array_key_exists("skeys",$arr))?$arr["skeys"]:"";
    $orderby=(is_array($arr) && array_key_exists("orderby",$arr))?$arr["orderby"]:"";
    $pagebase=(is_array($arr) && array_key_exists("pagebase",$arr))?CheckId($arr["pagebase"]):20;
    $pageid=(is_array($arr) && array_key_exists("pageid",$arr))?CheckId($arr["pageid"]):1;
    $gettpye=(is_array($arr) && array_key_exists("gettype",$arr))?CheckId($arr["gettype"]):0;

    if(is_array($arr) && array_key_exists("sendarr",$arr)){
        //存在数组
        $send.=ObjToStr($arr["sendarr"]);
    }

    if($portid>0){
        $CIP=getIp();
        $seqno = time();
        $apiid = APIID;
        $ysid = YSID;
        $token =  TOKEN;
        $typeid = TYPEID;
        $ip = urlencode($CIP);
        $clientid = CLIENTID;
        $sourceid = SOURCEID;
        $eipid = EIPID;
        $portid = $portid;
        $isdes = 0;
        $return = urlencode('');
        $code = CODE;
        $codemessage = urlencode('');
        $sendmessage = "";
        $sendmessage .="SEND:".escape($send).";";
        $sendmessage .="LISTID:{$listid};";
        $sendmessage .="PAGEBASE:{$pagebase};";
        $sendmessage .="PAGEID:{$pageid};";
        $sendmessage .="PAGES:0;";
        $sendmessage .="ISDES:0;";
        $sendmessage .="ISSPECIAL:;";
        $sendmessage .="SWHERE:".escape($where).";";
        $sendmessage .="SORDERBY:".escape($orderby).";";
        $sendmessage .="IP:{$CIP};";
        $sendmessage .="SKEYS:{$skeys};";

        $sendmessage=escape($sendmessage);

        $param  = '';
        $param .= "SEQNO:{$seqno};";
        $param .= "APIID:{$apiid};";
        $param .= "YSID:{$ysid};";
        //$param .= "TOKEN:{$token};";
        $param .= "TYPEID:{$typeid};";
        $param .= "IP:{$ip};";
        $param .= "CLIENTID:{$clientid};";
        $param .= "EIPID:{$eipid};";
        $param .= "SOURCEID:{$sourceid};";
        $param .= "PORTID:{$portid};";
        $param .= "ISDES:{$isdes};";
        $param .= "SEND:{$sendmessage};";
        $param .= "RETURN:{$return};";
        $param .= "CODE:{$code};";
        $param .= "CODEMESSAGE:{$codemessage};";
        $md5 = strtoupper(MD5($param.$token));
        $param .= "MD5:{$md5};";

        $data = ['Title'=>$param];

        if($gettpye==1){
            $result = GetContents(APIURL."API.ashx", $data, true);
        }
        else{
            $result = curlRequest(APIURL."API.ashx", $data, true);
        }
        //$str = unescape($result);
        if($result!=""){
            $info = StrToObj($result);
            $info["result"]=$result;
            $info["apicode"]=0;
            $info["apicodemsg"]="";
            if(array_key_exists("CODE",$info)){
                $info["apicode"]=$info["CODE"];
            }if(array_key_exists("CODEMESSAGE",$info)){
                $info["apicodemsg"]=$info["CODEMESSAGE"];
            }
            $info["code"]=$info["apicode"];
            $info["codemessage"]=$info["apicodemsg"];
            $info["ContentStr"]="";
            $info["Content"]="";
            if(array_key_exists("RETURN",$info)){
                 $info["RETURN"]=StrToObj($info["RETURN"]);
                 //对外均单一接口
                 if(array_key_exists("CODE",$info["RETURN"])){
                    $info["code"]=$info["RETURN"]["CODE"];
                }
                if(array_key_exists("CODEMESSAGE",$info["RETURN"])){
                    $info["codemessage"]=$info["RETURN"]["CODEMESSAGE"];
                }
                 if(array_key_exists("RETURN",$info["RETURN"]) && array_key_exists("RETURNTYPE",$info["RETURN"]) && 1*$info["RETURN"]["RETURNTYPE"]>=0){//RETURNTYPE
                    $VType=1*$info["RETURN"]["RETURNTYPE"];
                    $info["ContentStr"]=$info["RETURN"]["RETURN"];
                    if($VType==1){
                        //list
                        $info["Content"]=StrToListObj($info["RETURN"]["RETURN"]);
                    }
                    elseif ($VType==2) {
                        //keyvlue
                        $info["Content"]=StrToObj($info["RETURN"]["RETURN"]);
                    }
                    else{
                        $info["Content"]=$info["RETURN"]["RETURN"];
                    }
                 }
             }
         }
         else{
            $info["result"]=$result;
            $info["apicode"]=0;
            $info["apicodemsg"]="";
            $info["code"]=0;
            $info["codemessage"]="";
            $info["ContentStr"]="";
            $info["Content"]="";
         }
     }
     else{
        $info["result"]="接口编号为空错误！";
        $info["apicode"]=0;
        $info["apicodemsg"]="";
        $info["code"]=0;
        $info["codemessage"]="";
        $info["ContentStr"]="";
        $info["Content"]="";
     }
    return $info;
}


function GetYsFile($FileIds){
    $FileIds=unescape(trim($FileIds,","));
    $Fs=explode('|',$FileIds);
    $FileId="";
    $Path=FilePath;
    $Ext="";
    if(count($Fs)>3 && strlen($Fs[1])>8){
        $FileId=$Fs[1];   
        $Path.=substr($FileId,0,4)."/";
        $Path.=substr($FileId,4,2)."/";
        $Ext=".".$Fs[3];
        if (!file_exists(BASE_PATH.$Path.$FileId.$Ext) || (file_exists(BASE_PATH.$Path.$FileId.$Ext) && filesize(BASE_PATH.$Path.$FileId.$Ext)<100)){
            GetPathFile(APIURL."DownLoad.ashx?p=1&v=$FileIds",BASE_PATH.$Path,$FileId.$Ext);
        }
    }
    return $Path.$FileId.$Ext;
}

function GetFile($FileId){
    $Path=FilePath;
    if(strlen($FileId)>8){
        $Id=substr($FileId,8);
        $Path.=substr($FileId,0,4)."/";
        $Path.=substr($FileId,4,2)."/";
        if (!file_exists(BASE_PATH.$Path.$FileId) || (file_exists(BASE_PATH.$Path.$FileId) && filesize(BASE_PATH.$Path.$FileId)<100)){
            GetPathFile(APIURL."DownLoad.ashx?p=1&v=$Id|$FileId||||0|0||0|0",BASE_PATH.$Path,$FileId);
        }
    }
    return $Path.$FileId;
}

function GetPathFile($FileUrl,$Path,$FileName){
    if($FileUrl!=""){
        try{
            $Down=true;
            $curl = curl_init($FileUrl);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //对认证证书来源的检查;不验证证书下同
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //从证书中检查SSL加密算法是否存在;不验证证书下同
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 获取的信息以文件流的形式返回
            curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
            $File = curl_exec($curl);
            if (curl_errno($curl)) {
                $Down=false;
                Ys_Log("下载文件错误：[".$FileUrl."]==>[".$Path."][".$FileName."]==>[".curl_error($curl)."]");//捕抓异常
            }
            curl_close($curl);
            if($Down){
                if (!is_dir($Path)){
                    mkdir($Path,0777,true);
                }
                $fp = fopen($Path.$FileName,'w');//保存的文件名称用的是链接里面的名称
                fwrite($fp, $File);
                fclose($fp);
                $FSize=filesize($Path.$FileName);
                Ys_Log("下载文件：[".$FileUrl."]==>[".$Path."][".$FileName."][".$FSize."]");
            }
        }
        catch(Exception $e){
            Ys_Log("下载文件失败：[".$FileUrl."]==>[".$Path."][".$FileName."]==>[".$e->getMessage()."]");
        }
    }
}

function UpLoadFile($FileUrl,$FileName,$FilePath){
    $FileId="";
    $array=getResult(array(
        'portid' => 1000900,
        'send'=>"fileurl:".escape(BASE_HTTP.BASE_HOST.SitePath.$FileUrl).";filename:".escape($FileName).";filepath:".escape($FilePath).";",
        ));

    if($array["code"]==100){
        $FileId=$array["ContentStr"];
    }
    //将文件移动到本地上传文件夹中
    UpFileMoveTo($FileUrl,$FileId);
    return $FileId;
}

function UpFileMoveTo($GFilePath,$FileId){
    $TFilePath="";
    $TFileName="";
    if($FileId!=""){
        $Fs=explode("|",$FileId);
        if(count($Fs)>3){
            $Fid=$Fs[1];//201901032229040764
            $Ext=$Fs[3];
            if(strlen($Fid)>8){
                $TFilePath.=substr($Fid,0,4)."/";
                $TFilePath.=substr($Fid,4,2)."/";
                $TFilePath=BASE_PATH.FilePath.$TFilePath;
                $TFileName=$Fid.".".$Ext;
            }
        }
    }
    FileMoveTo(BASE_PATH.$GFilePath,$TFilePath,$TFileName);
}

function FileMoveTo($GFilePath,$TFilePath,$TFileName){
    if($GFilePath!="" && $TFilePath!="" && $TFileName!=""){        
        if (file_exists($GFilePath) && (!file_exists($TFilePath.$TFileName) || (file_exists($TFilePath.$TFileName) && filesize($TFilePath.$TFileName)<100))){
            if (!is_dir($TFilePath)){
                mkdir($TFilePath,0777,true);
            }
            //移动文件
            copy($GFilePath,$TFilePath.$TFileName);
            //unlink($GFilePath);
        }
    }
}

function curlRequest($url, $postData=array(), $isPost=false){
    $html="";
    if (empty($url)) {
        $html='ErrMsg:Url Is Empty!';//捕抓异常
    }
    $postData=$postData==null?"":$postData;
    $postData =is_array($postData)? http_build_query($postData):$postData;
    if($html==""){        
        if(!$isPost){
            $url = $url.(strpos($url,'?')>0?"&":"?").$postData;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //对认证证书来源的检查;不验证证书下同
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); //从证书中检查SSL加密算法是否存在;不验证证书下同
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        if($isPost){
            curl_setopt($curl, CURLOPT_POST, 1);// 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);// Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        }
        curl_setopt($curl, CURLOPT_HEADER, 0);// 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 获取的信息以文件流的形式返回
        $html = curl_exec($curl);
        if (curl_errno($curl)) {
            $html='ErrMsg:'.curl_error($curl);//捕抓异常
        }
        curl_close($curl);
    }
    Ys_Log("远端[curlRequest]访问：".$url."[".$postData."]==>".$html);
    return $html;
}

function GetContents($url, $postData=array(), $isPost=false){
    $html="";
    if (empty($url)) {
        $html='ErrMsg:Url Is Empty!';//捕抓异常
    }
    $postData=$postData==null?"":$postData;
    $postData =is_array($postData)? http_build_query($postData):$postData;

    if($isPost && $postData!=""){
        $opts = array (
            'http' => array (
                'method' => 'POST',
                'header'=> "Content-type: application/x-www-form-urlencodedrn" .
                "Content-Length: " . strlen($postData) . "rn",
                'content' => $postData
                ),

            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                )
            );
        $context = stream_context_create($opts);
        $html = file_get_contents($url, false, $context);
    }
    else{
        $html = file_get_contents($url.(strpos($url,'?')>0?"&":"?").$postData);
    }
    Ys_Log("远端[GetContents]访问：".$url."[".$postData."]==>".$html);
    return $html;
}


function escape($string, $in_encoding = 'UTF-8',$out_encoding = 'UCS-2') { 
    $return = ''; 
    if (function_exists('mb_get_info')) { 
        for($x = 0; $x < mb_strlen ( $string, $in_encoding ); $x ++) { 
            $str = mb_substr ( $string, $x, 1, $in_encoding );
            if (strlen ( $str ) > 1) { // 多字节字符
                if($str=='°'){
                    $return .= '%B0';
                }elseif ($str=='·'){
                    $return .= '%B7';
                }elseif ($str=='¥'){
                    $return .= '%A5';
                }else{
                    $return .= '%u' . strtoupper ( bin2hex ( mb_convert_encoding ( $str, $out_encoding, $in_encoding ) ) );
                }

            } else {
                if ($str=='='){
                    $return .= '%3D';
                }elseif ($str=='^'){
                    $return .= '%5E';
                }elseif ($str=='{'){
                    $return .= '%7B';
                }elseif ($str=='}'){
                    $return .= '%7D';
                }elseif ($str=='|'){
                    $return .= '%7C';
                } elseif ($str==','){
                    $return .= '%2C';
                }elseif ($str=='?'){
                    $return .= '%3F';
                }elseif ($str=='>'){
                    $return .= '%3E';
                } elseif ($str=='<'){
                    $return .= '%3C';
                }elseif ($str=='['){
                    $return .= '%5B';
                }elseif ($str==']'){
                    $return .= '%5D';
                }elseif ($str==';'){
                    $return .= '%3B';
                }elseif ($str==':'){
                    $return .= '%3A';
                }elseif ($str=='~'){
                    $return .= '%7E';
                }elseif ($str=='`'){
                    $return .= '%60';
                } elseif(preg_match('/^[A-Za-z0-9@+.*-—_\/^]+$/u',$str)){
                    $return .= $str;
                } else{
                    $return .= '%' . strtoupper ( bin2hex ( $str ) );
                }
            }
        } 
    } 
    return $return; 
}


function unescape($str)
{
    $ret = '';
    $len = strlen($str);
    for ($i = 0; $i < $len; $i ++)
    {
        if ($str[$i] == '%' && $str[$i + 1] == 'u')
        {
            $val = hexdec(substr($str, $i + 2, 4));
            if ($val < 0x7f)
                $ret .= chr($val);
            else
                if ($val < 0x800)
                    $ret .= chr(0xc0 | ($val >> 6)) .
                        chr(0x80 | ($val & 0x3f));
                else
                    $ret .= chr(0xe0 | ($val >> 12)) .
                        chr(0x80 | (($val >> 6) & 0x3f)) .
                        chr(0x80 | ($val & 0x3f));
            $i += 5;
        } else
            if ($str[$i] == '%')
            {
                $ret .= urldecode(substr($str, $i, 3));
                $i += 2;
            } else
                $ret .= $str[$i];
    }
    return $ret;
}



function format($str){
    preg_match('/({.*})/',$str,$arr);
    $array = json_decode($arr[0],TRUE);
    return $array;
}

function StrToListObj($Str,$un=0){
    $ListArr=array();
    if($Str!="" && $Str!=null){
        $li=0;
        $StrsT=explode("|",$Str);
        $StrsK=explode(",",$StrsT[0]);
        for($i=1;$i<count($StrsT);$i++){
            $Arr=array();
            if($StrsT[$i]!=""){
                $StrsV=explode(",",$StrsT[$i]);
                for($s=0;$s<count($StrsK) && $s<count($StrsV);$s++){
                    $Arr[$StrsK[$s]]=$un==1?$StrsV[$s]:unescape($StrsV[$s]);
                }
                $ListArr[$li]=$Arr;
                $li++;
            }
        }        
    }
    return $ListArr;
}

function StrToObj($Str,$un=0){
    $Arr=array();
    if($Str!="" && $Str!=null){
        $Strs=explode(";",$Str);
        for($i=0;$i<count($Strs);$i++){
            if($Strs[$i]!=""){
                $StrsT=explode(":",$Strs[$i]);
                if(count($StrsT)>=2){
                    $Arr[$StrsT[0]]=$un==1?$StrsT[1]:unescape($StrsT[1]);
                }
            }            
        }
    }
    return $Arr;
}

function ObjToStr($arr){
    $str="";
    if(is_array($arr)){
        foreach ($arr as $key => $value) {
            $str.=$key.":".escape($value).";";
        }
    }
    else{
        $str=$arr;
    }
    return $str;
}


function GetArr($arr,$skey){
    return GetArrDef($arr,$skey,"");
}

function GetArrDef($arr,$skey,$def=""){
    return (is_array($arr) && $skey!="" && array_key_exists($skey,$arr))?$arr[$skey]:$def;
}

function SetArr($arr,$skey,$val){
    $newArr=array();
    if(is_array($arr)){
        $newArr=$arr;
    }
    if($skey!=""){
        $newArr[$skey]=$val;
    }
    return $newArr;
}

function base64EncodeImage($image_file) {
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}

function getIp(){
    $ip=false;

    if(!empty($_SERVER["HTTP_CLIENT_IP"])){
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi ("^(10│172.16│192.168).", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

//数字处理
function checknum($cnum,$min,$max,$gonum){
    $cnum=($cnum=="" || $cnum==null)?$gonum:$cnum;
    $cnum=is_numeric($cnum)?$cnum:$gonum;
    $cnum=($min<>"" && $cnum<$min)?$gonum:$cnum;
    $cnum=($max<>"" && $cnum>$max)?$gonum:$cnum;
    $cnum=is_numeric($cnum)?$cnum:$gonum;
    return $cnum;
}

//判断主键
function CheckId($cnum){
    return checknum($cnum,0,"",0);
}

//=======信息获取区域=====
/**
 * get_page_url 获取完整URL
 * @return url
 */
function GetPageUrl($type = 0){
    $pageURL = 'http';
    if($_SERVER["HTTPS"] == 'on'){
        $pageURL .= 's';
    }
    $pageURL .= '://';
    if($type == 0){
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }else{
        $pageURL .= $_SERVER["SERVER_NAME"];
    }
    return $pageURL;
}

//记入日志
function Ys_Log($Info,$TypeId=0){
    //获取绝对路径
    $path=LogPath."YsLog/".date("Ym");
    $filename=$path."/Log_".date("Ymd").".log";
    //创建相关文件夹
    //$dpath=iconv("UTF-8", "GBK", $path);
    if (!is_dir($path)){
        mkdir($path,0777,true);
    }
    //创建相关文件
    //$dfilename=iconv("UTF-8", "GBK", $filename);
    if (!file_exists($filename)){
         file_put_contents($filename,'');
    }
    //书写日志内容
    $_IP=GetIp();
    $_Id=(isset($_SESSION["id"])?CheckId($_SESSION["id"]):0);
    $_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
    if($_SERVER["SERVER_PORT"]!=80){
        $_url='http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    }
    $WInfo="[DateTime] ".date("Y-m-d H:i:s.")." \n";
    $WInfo.="[Parameter] $TypeId|10|0|$_Id|0|$_IP|$_url \n";
    $WInfo.="[Info] $Info \n\n";
    //执行书写
    $of=fopen($filename ,"a+");//写入数据
    //fputs($of,$WInfo);//写入
    fwrite($of,$WInfo);//写入
    fclose($of);//关闭文件
    //header("Content-type: text/plain;");//生成下载什么样的格式
    //header("Content-Disposition:attachment;filename='$filename'");
    //readfile($filename);
    return true;
}

 function findCityByIp($ip){
    $data ="{\"code\":0,\"data\":{\"ip\":\"0.0.0.0\",\"country\":\"-\",\"area\":\"\",\"region\":\"-\",\"city\":\"-\",\"county\":\"-\",\"isp\":\"-\",\"country_id\":\"-\",\"area_id\":\"\",\"region_id\":\"0\",\"city_id\":\"0\",\"county_id\":\"0\",\"isp_id\":\"0\"}}";
    try{
        $data = file_get_contents('http://ip.taobao.com/service/getIpInfo.php?ip='.$ip);
    }
    catch(Exception $e){}
    return json_decode($data,$assoc=true);
}

?>