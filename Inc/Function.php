<?php

//判断在线
//
function OnlineCK($path,$gopage){
	if(!isset($_SESSION["ID"]) || (isset($_SESSION["ID"]) && $_SESSION["ID"]<=0)){
		//未登录，跳转到登陆页面
		//重定向浏览器
		header("Location:".$path."login/?page=".$gopage); 
		//确保重定向后，后续代码不会被执行 
		exit;
	}
}
?>