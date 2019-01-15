<?php

// 微信接口类
class Wechat{

    //===========功能配置区域================
    //
    ///
    /**
     * getUserInfo 获取用户信息
     * @param  string $OpenId         微信用户OpenId
     * @return array
     */
    public function getUserInfo($OpenId){
        $new_access_token = $this->getToken();
        //全局access token获得用户基本信息
        $userinfo_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$new_access_token}&openid={$OpenId}";
        $userinfo_json = curlRequest($userinfo_url);
        $userinfo_array = json_decode($userinfo_json, true);
        Ys_Log("微信获取个人信息[Wechat->getUserInfo]：".$OpenId."==>".$userinfo_array);
        return $userinfo_array;
    }

    /**
     * SetMenu 设置菜单
     * @param  array $data         微信菜单
     * @return bool
     */
    public function SetMenu($data){
        $ReturnMsg=false;
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->getToken();

        //curl上送json
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
        $info = curl_exec($ch);
//        $info = curlRequest($url,$data,false);
        $menu = json_decode($info);//创建成功返回：{"errcode":0,"errmsg":"ok"}
        $ReturnMsg=$menu->errcode == "0";
        Ys_Log("微信设置菜单[Wechat->SetMenu]：".$ReturnMsg);
        return $ReturnMsg;
    }

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

                $contentStr = UserToReg($object->FromUserName);//openid

                break;
            case "unsubscribe": //取消关注

                $contentStr = UserToUnReg($object->FromUserName);//openid
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

    /**
     * pushMessage 发送自定义的模板消息
     * @param  string $OpenId 用户openid
     * @param  string $ModuleId 消息模板数据
     * @param  string $Url 跳转链接
     * @param  array  $data          模板数据
                $data =  [ // 消息模板数据
                'first'    => ['value' => urlencode('黄旭辉'),'color' => "#743A3A"],
                'keyword1' => ['value' => urlencode('男'),'color'=>'blue'],
                'keyword2' => ['value' => urlencode('1993-10-23'),'color' => 'blue'],
                'remark'   => ['value' => urlencode('我的模板'),'color' => '#743A3A']
                ];
     * @param  string $topcolor 模板内容字体颜色，不填默认为黑色
     * @return array
     */
    public function pushMessage($OpenId,$ModuleId,$Url,$data = [],$topcolor = '#0000'){
        $template = [
            'touser'      => $OpenId,
            'template_id' => $ModuleId,
            'url'         => $Url,
            'topcolor'    => $topcolor,
            'data'        => $data
        ];
        $json_template = json_encode($template);
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->getToken();
        $result = curlRequest($url, urldecode($json_template),true);
        $resultData = json_decode($result, true);
        Ys_Log("微信用户消息推送[Wechat->pushMessage]：".$json_template."==>".$resultData);
        return $resultData;
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


    //===========基础信息配置区域================

    // 初始化验证绑定微信
    public function Reg($echoStr,$signature,$timestamp,$nonce){
        $ReturnMsg="ceshi";
        $token = "wechat";
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            $ReturnMsg = $echoStr;
        }
        echo $ReturnMsg;
    }


    // 获取TOKEN
    public function getToken(){
        $urla = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . WeiXin_APPID . "&secret=" . WeiXin_APPSECRET;
        $outputa = curlRequest($urla);
        $result = json_decode($outputa, true);
        return $result['access_token'];
    }

    // 微信授权地址
    public function getAuthorizeUrl($url){
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . WeiXin_APPID . "&redirect_uri=". urlencode($url) ."&response_type=code&scope=snsapi_base&state=1#wechat_redirect";
    }

    /**
     * getUserInfo 获取用户OpenId
     * @param  string $code         微信授权code
     * @return string
     */
    public function getOpenId($code){
        $access_token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . WeiXin_APPID . "&secret=" . WeiXin_APPSECRET . "&code={$code}&grant_type=authorization_code";
        $access_token_json = curlRequest($access_token_url);
        $access_token_array = json_decode($access_token_json, true);
        $openid = $access_token_array['openid'];
        return $openid;
    }

    /**
     * responseText 组合反馈信息
     * @param  string $object         微信基础信息
     * @param  string $content         微信响应内容
     * @param  int $flag         
     * @return string
     */
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