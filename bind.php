<?php
// 微信接口类
include_once ("config.php");

if(WeiXin_ISREG){
    $echoStr = $_GET["echostr"];
    $signature = $_GET["signature"];
    $timestamp = $_GET["timestamp"];
    $nonce = $_GET["nonce"];
    $ReturnMsg="";
    $token = WeiXin_TOKEN;
    $tmpArr = array($token, $timestamp, $nonce);
    sort($tmpArr);
    $tmpStr = implode( $tmpArr );
    $tmpStr = sha1( $tmpStr );

    if( $tmpStr == $signature ){
        $ReturnMsg = $echoStr;
    }
    echo $ReturnMsg;exit;
}


function UserToReg(){
    return '感谢关注测试323公众号';//openid
}

function UserToUnReg(){
    return '解除关注的openID';//openid
}

function UserToKeys($KeyMsg){
    return "ReturnKeys:".$KeyMsg;
}

$wechatObj = new Wechat();
$OutMsg = $wechatObj->responseMsg();
// 微信接口类
class Wechat{

    /**
     * UserReg 用户关注，解除关注
     * @param  array $object
     * @return string
     */
    public function UserReg($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = UserToReg();//openid
                //file_put_contents('123.txt',$object->FromUserName);
                break;
            case "unsubscribe": //取消关注
                $contentStr = UserToUnReg();//openid
                //file_put_contents('111.txt',$object->FromUserName);
                break;
            default :
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        Ys_Log("微信用户关注操作[Wechat->UserReg]：".$object->Event."[".$object->FromUserName."]==>".$contentStr);
        return $resultStr;
    }

    /**
     * ReturnKeys 关键字回复
     * @param  array $postObj
     * @return string
     */
    public function ReturnKeys($postObj)
    {
        $ReturnMsg="请输入关键字...";
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
        if(!empty( $keyword ))
        {
            $msgType = "text";
            $ReturnMsg = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, UserToKeys($keyword));
        }
        Ys_Log("微信用户关键字回复[Wechat->ReturnKeys]：".$keyword."==>".$ReturnMsg);
        return $ReturnMsg;
    }

    //信息通讯===基础操作
    public function responseMsg()
    {
        $resultStr="";
        //get post data, May be due to the different environments
        if(PHP_VERSION >= '7.0.0'){
            $postStr = file_get_contents('php://input');
        }else{
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        }

        //extract post data
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            switch($RX_TYPE)
            {
                case "text":
                    $resultStr = $this->ReturnKeys($postObj);
                    break;
                case "event":
                    $resultStr = $this->UserReg($postObj);
                    break;
                default:
                    $resultStr = "Unknow msg type: ".$RX_TYPE;
                    break;
            }
        }
        return $resultStr;
    }

    public function responseText($object, $content, $flag=0)
    {
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }

}


?>