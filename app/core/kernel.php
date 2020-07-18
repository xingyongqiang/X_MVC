<?php
/**
 * 加载核心文件
 *
 * @package        Hooloo framework
 * @author        Bill
 * @copyright    Hooloo Team
 * @version        1.1
 * @release        2017.07.25
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

//授权域名访问
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if ($origin && strstr($origin, SERVER_NAME)) {
    header('Access-Control-Allow-Origin:' . $origin);
}

//AJAX请求标志
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
define('IS_POST', strtolower($_SERVER['REQUEST_METHOD']) == 'post');
define('IS_GET', strtolower($_SERVER['REQUEST_METHOD']) == 'get');

//开发环境配置
if (DEVELOPMENT_ENVIRONMENT == true) {
    //开发环境打印所有错误
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
} else {
    //正式环境记录错误到日志文件
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 'On');
    ini_set('log_errors', 'On');
    ini_set('error_log', ERRLOG_PATH . '/error.log');
}
//设置会话保存路径
ini_set("session.save_path", SESSION_SAVE_PATHS);
if (!session_id()) session_start();

//设置时区
ini_set('date.timezone', 'Asia/Shanghai');

//检测全局变量设置（register globals）并移除他们
if (ini_get('register_globals')) {
    $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
    foreach ($array as $value) {
        foreach ($GLOBALS[$value] as $key => $var) {
            if ($var === $GLOBALS[$key]) {
                unset($GLOBALS[$key]);
            }
        }
    }
}

//错误处理
function _error_handler($severity, $message, $filepath, $line)
{
    $is_error = (((E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);
    if (($severity & error_reporting()) !== $severity) {
        return;
    }
    if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
        $error_data['severity'] = $severity;
        $error_data['message'] = $message;
        $error_data['filepath'] = $filepath;
        $error_data['line'] = $line;
        $error_data['title'] = 'A PHP Error was encountered';
        show_error(1, $error_data);
    }
    if ($is_error) {
        exit();
    }
}

//异常处理
function _exception_handler($exception)
{
    if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
        $error_data['exception'] = $exception;
        $error_data['title'] = 'An uncaught Exception was encountered';
        show_error(2, $error_data);
    }
    exit(); // EXIT_ERROR
}

//致命错误报告处理
function _shutdown_handler()
{
    $last_error = error_get_last();
    if (isset($last_error) && ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))) {
        _error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
    }
}

//显示错误信息
function show_error($type = 0, $data = array())
{
    $html = '<!DOCTYPE html>
			<html>
			<head>
			<meta charset="utf-8">
			<title>Error</title>
			<style type="text/css">
			body {
				margin: 40px;
				font: 13px/20px normal Helvetica, Arial, sans-serif;
			}
			h1 {
				color: #444;
				border-bottom: 1px solid #D0D0D0;
				font-size: 19px;
				font-weight: normal;
				margin: 0 0 14px 0;
				padding: 14px 15px 10px 15px;
			}
			#container {
				margin: 10px;
				border: 1px solid #D0D0D0;
				box-shadow: 0 0 8px #D0D0D0;
			}
			p {
				margin: 12px 15px 12px 15px;
			}
			</style>
			</head>
			<body>
				<div id="container">';
    if (DEVELOPMENT_ENVIRONMENT === true) {
        $title = isset($data['title']) ? $data['title'] : "404 Page Not Found";
        switch ($type) {
            case 1:
                //错误处理
                $html .= '<h1>' . $title . '</h1>';
                $html .= '<p>Severity: ' . $data['severity'] . '</p>';
                $html .= '<p>Message: ' . $data['message'] . '</p>';
                $html .= '<p>Filename: ' . str_replace(BASEPATH, '', $data['filepath']) . '</p>';
                $html .= '<p>Line: ' . $data['line'] . '</p>';
                break;
            case 2:
                //异常处理
                $exception = $data['exception'];
                $message = $exception->getMessage();
                if (empty($message)) $message = '(null)';
                $html .= '<h1>' . $title . '</h1>';
                $html .= '<p>Type: ' . get_class($exception) . '</p>';
                $html .= '<p>Message: ' . $message . '</p>';
                $html .= '<p>Filename: ' . $exception->getFile() . '</p>';
                $html .= '<p>Line Number: ' . $exception->getLine() . '</p>';
                break;
            default:
                //默认错误
                $html .= '<h1>' . $title . '</h1>';
                $html .= '<p>The page you requested was not found. (' . $type . ')</p>';
        }
    } else {
        $html .= '<h1>500 Internal Server Error</h1>';
        $html .= '<p>The server encountered an unexpected condition which prevented it from fulfilling the request.</p>';
    }
    $html .= '</div></body></html>';
    exit($html);
}

/* 主请求方法，主要目的拆分URL请求 */
function call_hook()
{
    //解析控制器和方法
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : DEFAULT_MODULE . "/" . DEFAULT_CONTROLLER . "/" . DEFAULT_ACTION;
    $path_info = trim(strtolower($path_info), '/');
    $path_info = str_ireplace(".html", "", $path_info);
    $path_info = str_ireplace(".shtml", "", $path_info);
    $path_info = str_ireplace(".htm", "", $path_info);

    $path_str = _set_routes($path_info);

    $path_arr = explode('/', $path_str);
    $controller = empty($path_arr[0]) ? DEFAULT_CONTROLLER : $path_arr[0];
    $action = empty($path_arr[1]) ? DEFAULT_ACTION : $path_arr[1];
    if (isset($path_arr[2])) {
        $query_arr = array_slice($path_arr, 2);
    } else {
        $query_arr = array();
    }

    //分配全局变量
    $GLOBALS['controller'] = $controller;
    $GLOBALS['action'] = $action;

    //加载控制器执行
    $controller = ucwords($controller);
    $method = get_class_methods($controller);
    if ($method) {
        if (isset($method) && in_array($action, $method)) {
            $dispatch = new $controller();
            call_user_func_array(array($dispatch, $action), $query_arr);
        } else {
            show_error(998);
        }
    } else {
        //控制器不存在
        show_error(999);
    }
}

/* 解析路由器 */
function _set_routes($path_str)
{
    //解析URL
    $path_arr = explode("/", $path_str);

    //解析模块
    $module = empty($path_arr[0]) ? DEFAULT_CONTROLLER : $path_arr[0];
    if (!in_array($module, explode(",", MODULE_ALLOW_LIST))) {
        $module = DEFAULT_MODULE;
    } else {
        //去除url中模块
        $path_str = ltrim($path_str, $module);
        $path_str = trim($path_str, "/");
    }
    $GLOBALS['module'] = $module;

    //读取路由配置，如果存在分组路由，则进行解析URL解析；否则原封不动返回；
    global $route;
    if ($route && $GLOBALS['module'] == DEFAULT_MODULE) {
        //便利路由规则
        foreach ($route as $key => $val) {
            $key = str_replace(array(':any', ':num'), array('[^/]+', '[0-9]+'), $key);
            if (preg_match('#^' . $key . '$#', $path_str, $matches)) {
                if (!is_string($val) && is_callable($val)) {
                    array_shift($matches);
                    $val = call_user_func_array($val, $matches);
                } elseif (strpos($val, '$') !== FALSE && strpos($key, '(') !== FALSE) {
                    $val = preg_replace('#^' . $key . '$#', $val, $path_str);
                }
                return $val;
            }
        }
    }
    return $path_str;
}

/* 自动加载控制器、模型、类文件 */
spl_autoload_register(function ($class_name) {
    $class_name = ucwords($class_name);
    if (file_exists(APPPATH . 'controller/' . $GLOBALS['module'] . "/" . $class_name . '.php')) {
        include APPPATH . 'controller/' . $GLOBALS['module'] . "/" . $class_name . '.php';
    } elseif (file_exists(APPPATH . 'library/' . $class_name . '.php')) {
        include APPPATH . 'library/' . $class_name . '.php';
    }
//    elseif (file_exists(APPPATH . 'vendor/' . $class_name . '.php')) {
//        include BASEPATH . 'vendor/' . $class_name . '.php';
//    }
    else {
        //var_dump(APPPATH . 'controller/' . $GLOBALS['module'] . "/" . $class_name . '.php');
        //var_dump(APPPATH . 'library/' . $class_name . '.php');
        //var_dump(BASEPATH . 'vendor/' . $class_name . '.php');
        show_error(997);
    }
});

//捕获错误和异常
set_error_handler('_error_handler');
set_exception_handler('_exception_handler');
register_shutdown_function('_shutdown_handler');

//加载公共函数
require APPPATH . 'helper/common.php';

//加载主控制器
require APPPATH . 'core/Controler.php';

call_hook();
