<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 应用路径为'app'，默认控制器为'Index'，默认方法为'index'，均不可更改
 */

//开发环境调试模式
define('MODULE_ALLOW_LIST', "admin,api");//允许访问的模块
define('DEFAULT_MODULE', "admin");//默认模块
define('DEFAULT_CONTROLLER', "Index");//默认控制器
define('DEFAULT_ACTION', "index");//默认方法

//服务器域名
define('SERVER_NAME', 'xcx.xingyongqiang.com');//正式环境主机名称，不带http
define('WEB_SERVER', 'https://' . SERVER_NAME); //web服务器地址
define('STATIC_SERVER', '/static'); //静态文件css/img/js服务器地址

//错误日志路径
define('ERRLOG_PATH', BASEPATH . 'data/logs');
//会话信息保存路径
define('SESSION_SAVE_PATHS', BASEPATH . 'data/session');
//缓存目录
define('CACHE_PATH', BASEPATH . 'data/cache');
//加载URI路由配置
require APPPATH . 'config/routes.php';
//加载导航
require APPPATH . 'config/menu.php';

//模板引擎
define('TPL_COMPILE_PATH', BASEPATH . 'data/runtime'); //编译文件输出路径
define('TPL_LEFT_SEPERATOR', '<{'); //左界定符
define('TPL_RIGHT_SEPERATOR', '}>'); //右界定符

//数据库连接
if ($_SERVER['SERVER_NAME'] == SERVER_NAME) {
    define('DEVELOPMENT_ENVIRONMENT', true); //正式环境设为false
    //正式服务器配置
    //mysql
    $config['db_1'] = array(
        'hostname' => '127.0.0.1:3306',
        'username' => 'xiaochengxu_com',
        'password' => 'ybDpZaRsxwTyB7Dm',
        'database' => 'xiaochengxu_com',
        'dbdriver' => 'mysqli',
    );
} else {
    define('DEVELOPMENT_ENVIRONMENT', false); //正式环境设为false
    //本地开发服务器
    //mysql
    $config['db_1'] = array(
        'hostname' => '127.0.0.1:3306',
        'username' => 'xiaochengxu_com',
        'password' => 'ybDpZaRsxwTyB7Dm',
        'database' => 'xiaochengxu_com',
        'dbdriver' => 'mysqli',
    );
}

