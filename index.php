<?php

include_once ("config.php");

$wechatObj = new Wechat();


if(WeiXin_ISREG){
// 初始化验证绑定微信
//    Reg();  //这个方法不太稳定
    $wechatObj->valid(); //这个相对稳定些
}else{
    $wechatObj->responseMsg();
}

function UserToReg(){
    return '欢迎关注雨石官方微信！';
}

function UserToUnReg(){
    return '解除关注的openID';
}

function UserToKeys($KeyMsg){
    return "ReturnKeys:".$KeyMsg;
}

function Reg(){
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
    echo $ReturnMsg;
}

class Wechat
{
    public function responseMsg()
    {
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
                        $resultStr = $this->handleText($postObj);
                        break;
                    case "event":
                        $resultStr = $this->handleEvent($postObj);
                        break;
                    default:
                        $resultStr = "Unknow msg type: ".$RX_TYPE;
                        break;
                }
                echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }

    public function handleText($postObj)
    {
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
            echo $ReturnMsg;
        }
    }

    public function handleEvent($object)
    {
        $contentStr = "";
        switch ($object->Event)
        {
            case "subscribe":
                $contentStr = UserToReg();//此处写关注接口动作 $openid = $object->FromUserName;
                //file_put_contents('gz.txt',$object->FromUserName);
                break;
            case "unsubscribe": //取消关注
                $contentStr = UserToUnReg();//此处写取消关注的接口动作 $openid = $object->FromUserName;
                //file_put_contents('qg.txt',$object->FromUserName);
                break;
            default :
                $contentStr = "Unknow Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
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

//  绑定微信 绑定完以后注释掉第5行代码
    public function valid(){
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = WeiXin_TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }


}

?>