<?php
/**
 * 广告管理
 */
defined('BASEPATH') or exit("No direct script access allowed");

class Adv extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    //首页
    public function index($p = 1)
    {
        //页码
        $p = (int)$p <= 0 ? 1 : (int)$p;
        $size = 15;
        $start = ($p - 1) * $size;

        //总数
        $sql = "select count(*) as c from `xyq_adv`";
        $total = $this->db->query($sql)->row_array();
        $total = $total["c"];

        //查询
        $sql = "select * from `xyq_adv` order by id asc limit $start,$size";
        $list = $this->db->query($sql)->result_array();

        //分页
        $page = page_format($total, $size, $p, "");

        //赋值变量
        $this->assign("list", $list);
        $this->assign("page", $page);
        $this->display();

    }

    //添加
    public function add()
    {
        if (IS_POST) {
            //接收参数
            $name = isset($_POST["name"]) ? sql_format($_POST["name"]) : "";//广告名称
            $type = isset($_POST["type"]) ? intval($_POST["type"]) : 0;//广告类别
            $url = isset($_POST["url"]) ? sql_format($_POST["url"]) : "";//链接地址
            $img = isset($_POST["img"]) ? sql_format($_POST["img"]) : "";//广告图片
            $status = isset($_POST["status"]) ? intval($_POST["status"]) : 1;//是否启用

            // 判断
            if (!$name) ajax_return(0, "广告名称不允许为空！");
            if ($status < 0 || $status > 1) $status = 1;
            if (!$img) ajax_return(0, "广告图片不允许为空！");

            //存储
            $sql = "insert into `xyq_adv` (`name`,`type`,url,img,status) values ('$name',$type,'$url','$img',$status)";
            $this->db->query($sql);
            $res = $this->db->insert_id();

            //清除缓存
            clear_cache(TPL_COMPILE_PATH . "/");
            clear_cache(CACHE_PATH . "/");

            //返回结果
            if ($res) {
                ajax_return(1, "添加成功", "/" . $this->_controller . "/index");
            } else {
                ajax_return(0, "添加失败");
            }
        }
        $this->display();
    }

    //修改
    public function edit($id = 0)
    {
        //检查id
        $id = intval($id);
        if ($id < 0) {
            IS_AJAX && ajax_return(0, "参数有误");
            $this->error("参数有误");
        }
        //查询数据
        $sql = "select * from `xyq_adv` where id=$id";
        $res = $this->db->query($sql)->row_array();
        if (!$res) {
            IS_AJAX && ajax_return(0, "记录不存在");
            $this->error("记录不存在");
        }
        if (IS_POST) {
            //接收参数
            $name = isset($_POST["name"]) ? sql_format($_POST["name"]) : "";//广告名称
            $type = isset($_POST["type"]) ? intval($_POST["type"]) : 0;//广告类别
            $url = isset($_POST["url"]) ? sql_format($_POST["url"]) : "";//链接地址
            $img = isset($_POST["img"]) ? sql_format($_POST["img"]) : "";//广告图片
            $status = isset($_POST["status"]) ? intval($_POST["status"]) : 1;//是否启用

            // 判断
            if (!$name) ajax_return(0, "广告名称不允许为空！");
            if ($status < 0 || $status > 1) $status = 1;
            if (!$img) ajax_return(0, "广告图片不允许为空！");

            //存储
            $sql = "update `xyq_adv` set `name`='$name',`type`=$type,url='$url',img='$img',status=$status where id=$id";
            $this->db->query($sql);

            //清除缓存
            clear_cache(TPL_COMPILE_PATH . "/");
            clear_cache(CACHE_PATH . "/");

            //返回结果
            ajax_return(1, "修改成功", "/" . $this->_controller . "/index");

        }
        //分配变量
        $this->assign('res', $res);
        $this->display();
    }

    //删除
    public function del()
    {
        if (IS_AJAX) {

            //接收参数
            $id = isset($_POST["id"]) ? intval($_POST["id"]) : "";
            if (!$id) ajax_return(0, "参数ID错误！");

            //删除记录
            $sql = "delete from `xyq_adv` where id in (" . $id . ")";
            $this->db->query($sql);

            //清除缓存
            clear_cache(TPL_COMPILE_PATH . "/");
            clear_cache(CACHE_PATH . "/");

            //返回结果
            ajax_return(1, "删除成功");
        }
    }

    /**
     * 状态
     */
    public function change() {
        if (IS_AJAX) {
            //接收参数
            $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
            $status = isset($_POST["status"]) ? intval($_POST["status"]) : 0;
            if (! $id) ajax_return(0, "参数有误！");
            if ($status < 0 || $status > 1) $status = 1;

            //更改状态
            $sql = "update `xyq_adv` set status = $status where id = $id";
            $this->db->query($sql);

            //返回结果
            ajax_return(1, "修改成功");
        }
    }
}