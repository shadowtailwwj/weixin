<?php
header('content-type:image/png');
include_once("../config.php");
$url=isset($_GET["url"])?$_GET["url"]:"";
if($url==""){
	$url="";
}
$url=BASE_PATH.$url;
if (!file_exists($url)){
	$url="";
}
$filename=$url;
$handle=fopen($filename,'rb+'); //读写二进制，图片的可移植性
$res=fread($handle,filesize($filename));
fclose($handle);
echo $res;
?>