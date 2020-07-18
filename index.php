<?php
/**
 * XS框架
 */

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; charset=utf-8");

//页面开始执行时间
define('BEGINTIME', microtime(true));
//网站根路径
define('BASEPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
//应用路径
define('APPPATH', BASEPATH . 'app' . DIRECTORY_SEPARATOR);

// 在这里引入Composer的自动加载文件
//require BASEPATH . 'vendor/autoload.php';

//加载配置文件
require APPPATH . 'config/config.php';

//加载核心文件
require APPPATH . 'core/kernel.php';