<?php

/**
 * 栏目管理
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Classes extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 列表
     */
    public function index($p = 1)
    {
        //页码
        $p = (int)$p <= 0 ? 1 : (int)$p;
        $size = 10;
        $start = ($p - 1) * $size;

        //总数
        $sql = "select count(*) as c from `xyq_goods_cate`";
        $total = $this->db->query($sql)->row_array();
        $total = $total["c"];

        //查询记录
        $sql = "select * from `xyq_goods_cate` order by id desc limit $start,$size";
        $list = $this->db->query($sql)->result_array();

        //分页
        $page = page_format($total, $size, $p, "");

        //分配变量
        $this->assign('list', $list);
        $this->assign("page", $page);
        $this->display();
    }

    /**
     * 新增
     */
    public function add($id = 0)
    {
        if (IS_POST) {
            //接收参数
            $name = isset($_POST["name"]) ? sql_format($_POST["name"]) : "";
            $pid = isset($_POST["pid"]) ? (int)$_POST["pid"] : 0;
            $img = isset($_POST["icon"]) ? $_POST["icon"] : "";

            //校验参数
            if (!$name) ajax_return(0, "栏目不能为空");

            $data['pid'] = $pid;
            $data['name'] = $name;
            $data['icon'] = $img;
            $sql = data_to_build_insert_sql($data, 'xyq_goods_cate');
            $this->db->query($sql);
            $res = $this->db->insert_id();

            //返回结果
            if (!$res) {
                ajax_return(0, "添加失败");
            } else {
                ajax_return(1, "添加成功", "/" . $this->_controller . "/index");
            }
        }

        //查询栏目
        $sql = "select * from `xyq_goods_cate` where pid = 0";
        $role = $this->db->query($sql)->result_array();

        //分配变量
        $this->assign("role", $role);
        $this->assign("id", $id);
        $this->display();
    }

    /**
     * 编辑
     */
    public function edit($id = 0)
    {
        //校验参数
        $id = intval($id);
        if ($id < 0) {
            IS_AJAX && ajax_return(0, "参数有误");
            $this->error("参数有误");
        }

        //查询记录
        $sql = "select * from `xyq_goods_cate` where id = $id";
        $res = $this->db->query($sql)->row_array();
        if (!$res) {
            IS_AJAX && ajax_return(0, "记录不存在");
            $this->error("记录不存在");
        }

        if (IS_POST) {
            //接收参数
            $name = isset($_POST["name"]) ? sql_format($_POST["name"]) : "";
            $pid = isset($_POST["pid"]) ? (int)$_POST["pid"] : 0;
            $img = isset($_POST["icon"]) ? $_POST["icon"] : "";

            //校验参数
            if (!$name) ajax_return(0, "栏目不能为空");

            $data['pid'] = $pid;
            $data['name'] = $name;
            $data['icon'] = $img;
            $sql = data_to_build_update_sql($data, 'xyq_goods_cate',"id = $id");
            $this->db->query($sql);

            //返回结果
            ajax_return(1, "修改成功", "/" . $this->_controller . "/index");
        }

        //查询栏目
        $sql = "select * from `xyq_goods_cate` where pid = 0";
        $role = $this->db->query($sql)->result_array();

        //分配变量
        $this->assign("role", $role);
        $this->assign("res", $res);
        $this->display();
    }

    /**
     * 删除
     */
    public function del()
    {
        if (IS_AJAX) {
            //接收参数
            $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
            if (!$id) ajax_return(0, "参数有误！");

            //查询记录
            $sql = "select pid from `xyq_goods_cate` where id = $id";
            $res = $this->db->query($sql)->row_array();
            if (!$res) ajax_return(0, "记录不存在！");

            //查询子栏目
            $sql = "select id from `xyq_goods_cate` where pid = $id";
            $res = $this->db->query($sql)->row_array();
            if ($res) ajax_return(0, "存在子栏目，不允许删除！");

            //查询文章
            $sql = "select id from `xyq_goods` where cate_top_id = $id or cate_son_id = $id";
            $res = $this->db->query($sql)->row_array();
            if ($res) ajax_return(0, "存在商品，不允许删除！");

            //删除记录
            $sql = "delete from xyq_goods_cate where id = $id";
            $this->db->query($sql);

            //返回结果
            ajax_return(1, "删除成功");
        }
    }

    /**
     * 状态
     */
    public function change()
    {
        if (IS_AJAX) {
            //接收参数
            $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
            $status = isset($_POST["status"]) ? intval($_POST["status"]) : 0;
            if (!$id) ajax_return(0, "参数有误！");
            if ($status < 0 || $status > 1) $status = 1;
            //更改状态
            $sql = "update `xyq_classes` set status = $status where id = $id";
            $this->db->query($sql);

            //返回结果
            ajax_return(1, "修改成功");
        }
    }
}
