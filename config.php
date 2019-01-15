<?php
//接口配置

define('APIURL', 'http://home.yushidns.com:8061/Admin/CNet/');//API地址
define('APIID', 4);//条件ID号，唯一固定号
define('YSID', 2000180828031);//系统编号，唯一编号
/*
define('APIURL', 'http://home.yushidns.com:8088/CNet/');//API对接地址
define('APIID', 3);//条件ID号，唯一固定号
define('YSID', 2000180828021);//系统编号，唯一编号
*/
define('TOKEN', 'ToKen');//密钥信息，对YSID以及时间轴进行加密
define('CLIENTID', 0);//访问会员用户Id，session
define('SOURCEID', 6);//交易来源渠道
define('TYPEID', 1);//请求类型：0为初始化请求1为数据请求，固定送1
define('EIPID', 1);//EIP系统用户Id
define('CODE', 0);//响应代码，100-199为正常


//微信配置
define('WeiXin_APPID', 'wx38c99a513d30e52e');//微信开发者ID
define('WeiXin_APPSECRET', 'eff6a287724b3cac40aa39592eaa01f2');//微信开发者密码
define("WeiXin_TOKEN", "wechat");//微信设置的Token
define("WeiXin_ISREG", false);//验证注册，仅在身份验证时为true，验证结束后设置为false

//站点配置
define('SitePath', '/Demo/');//不同站点文件夹位置不同，需要重新配置
define('LogPath', 'E:/YsEip/WebSite/Demo/');//站点日志文件夹位置，可写绝对地址，斜杠结尾
define('FilePath', 'UpLoad/');//站点上传文件夹位置，建议写根目录开始的相对路径，斜杠结尾



//基础参数
define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
define('BASE_HTTP',((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://');
define('BASE_HOST',$_SERVER["HTTP_HOST"]);
?>