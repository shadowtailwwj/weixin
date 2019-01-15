<?php
header("Content-Type:text/html;charset=UTF-8");
include_once("config.php");
include_once("Inc/common.php");
include_once("Inc/Wechat.php");

function UserToReg(){
    return '感谢关注测试323公众号';//openid
}

function UserToUnReg(){
    return '解除关注的openID';//openid
}

function UserToKeys($KeyMsg){
    return "ReturnKeys:".$KeyMsg;
}

$OutMsg="";
$wxportid=(isset($_GET["wxportid"]) && $_GET["wxportid"]!="")?CheckId($_GET["wxportid"]):0;
$wechatObj = new Wechat();

if(WeiXin_ISREG){
	$echoStr = $_GET["echostr"];
	$signature = $_GET["signature"];
	$timestamp = $_GET["timestamp"];
	$nonce = $_GET["nonce"];
    $wechatObj->Reg($echoStr,$signature,$timestamp,$nonce);exit;
}
else if($wxportid==0){
    $OutMsg = $wechatObj->responseMsg();
}
else{
	switch($wxportid){
		case 1000://获取个人信息
		$OutMsg=$wechatObj->getUserInfo("oSt_E1JuddalKeKHVii3YFYWaowM");
		break;
		case 1001://设置菜单
        $data='{
		 "button":[
		 {
			   "name":"公共查询",
			   "sub_button":[
				{
				   "type":"click",
				   "name":"天气查询",
				   "key":"tianQi"
				},
				{
				   "type":"click",
				   "name":"公交查询",
				   "key":"gongJiao"
				},
				{
				   "type":"click",
				   "name":"快递查询",
				   "key":"kuaiDi"
				}]
		  },
		  {
			   "name":"关于我们",
			   "sub_button":[
				{
				   "type":"view",
				   "name":"联系我们",
				   "url":"http://47.52.115.175"
				},
				{
				   "type":"click",
				   "name":"关于我们",
				   "key":"suzhouScenic"
				}]
		   },
		   {
			   "type":"view",
			   "name":"微网站",
			   "url":"www.yseip.com/"
		   }]
       }';
		$OutMsg=$wechatObj->SetMenu($data);
		break;
		case 1002:
		//接口编号定义测试，发送消息
		$OutMsg=$wechatObj->pushMessage("oSt_E1JuddalKeKHVii3YFYWaowM","xKUSVnCnMUpgJFbGT9XkQ-1MEkpSAlj3xZcEO3iXb9M",'https://www.yseip.com/',[ // 模板消息内容，根据模板详情进行设置
            'first'    => ['value' => urlencode("尊敬的某某某先生，您好，您本期还款已成功扣收。"),'color' => "#743A3A"],
            'keyword1' => ['value' => urlencode("2476.00元"),'color'=>'blue'],
            'keyword2' => ['value' => urlencode("13期"),'color'=>'blue'],
            'keyword3' => ['value' => urlencode("15636.56元"),'color' => 'green'],
            'keyword4' => ['value' => urlencode("6789.23元"),'color' => 'green'],
            'remark'   => ['value' => urlencode("更多贷款详情，请点击页面进行实时查询。"),'color' => '#743A3A']
        ]);
		break;
		default:
		break;		
	}
}
echo $OutMsg;





//=================目录上送格式==================
//    $data格式
//    $data='{
//		 "button":[
//		 {
//			   "name":"公共查询",
//			   "sub_button":[
//				{
//				   "type":"click",
//				   "name":"天气查询",
//				   "key":"tianQi"
//				},
//				{
//				   "type":"click",
//				   "name":"翻译查询",
//				   "key":"fanYi"
//				},
//				{
//				   "type":"click",
//				   "name":"快递查询",
//				   "key":"kuaiDi"
//				}]
//		  },
//		  {
//			   "name":"关于我们",
//			   "sub_button":[
//				{
//				   "type":"click",
//				   "name":"联系方式",
//				   "key":"mobile"
//				},
//				{
//				   "type":"click",
//				   "name":"关于我们",
//				   "key":"about"
//				}]
//		   },
//		   {
//			   "type":"view",
//			   "name":"微官网",
//			   "url":"http://47.52.115.175/wx/jz/index.html"
//		   }]
//       }';
?>