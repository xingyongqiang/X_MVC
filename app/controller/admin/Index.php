<?php
/**
 * 后台管理
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Index extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 管理首页
     */
    public function index()
    {
        //获取系统信息
        $system_info = array(
            'server_domain' => $_SERVER['SERVER_NAME'] . ' [ ' . gethostbyname($_SERVER['SERVER_NAME']) . ' ]',
            'server_os' => PHP_OS,
            'web_server' => $_SERVER["SERVER_SOFTWARE"],
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->db->get_server_info(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_execution_time' => ini_get('max_execution_time') . '秒',
            'safe_mode' => (boolean)ini_get('safe_mode') ? "是" : "否",
            'zlib' => function_exists('gzclose') ? "是" : "否",
            'curl' => function_exists("curl_getinfo") ? "是" : "否",
            'timezone' => function_exists("date_default_timezone_get") ? date_default_timezone_get() : "否"
        );


        //最新文章
        $sql = "select * from `xyq_article` order by id desc limit 0, 6";
        $list = $this->db->query($sql)->result_array();

        //模板赋值
        $this->assign('list', $list);
        $this->assign('system_info', $system_info);
        $this->display();
    }
}
