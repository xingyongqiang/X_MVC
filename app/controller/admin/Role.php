<?php

/**
 * 角色管理
 *
 * @package		Hooloo framework
 * @author 		Bill
 * @copyright 	Hooloo Co.,Ltd
 * @version		1.0
 * @release		2017.08.21
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Role extends Controller {
	public function __construct() {
		parent::__construct();
	}
	
	/**
     * 列表
     */
	public function index($keyword = "", $p = 1) {
		//通用搜索
		$keyword = sql_format($keyword);
		if ($keyword) {
			$where = " where name like '%$keyword%'";
		} else {
			$where = "";
			$keyword = 0;
		}
		
		//数据分页
		$p = (int)$p <= 0 ? 1 : (int)$p;
		$size = 15;
		$start = ($p - 1) * $size;
		
		//统计总数
		$sql = "select count(*) as c from `xyq_role`" . $where;
		$total = $this->db->query($sql)->row_array();
		$total = $total["c"];
		
		//查询记录
		$sql = "select * from `xyq_role`" . $where . " order by id desc limit $start, $size";
		$list = $this->db->query($sql)->result_array();
		
		//分页展示
		$page = page_format($total, $size, $p, $keyword);
		
		//分配变量
        $this->assign('list', $list);
        $this->assign('page', $page);
        $this->assign('total', $total);
        $this->assign('keyword', $keyword);
		$this->display();
	}
	
	/**
     * 新增
     */
	public function add() {
		if (IS_POST) {
			//接收参数
			$name = isset($_POST["name"]) ? sql_format($_POST["name"]) : "";//名称
			$remark = isset($_POST["remark"]) ? sql_format($_POST["remark"]) : "";
			$status = isset($_POST["status"]) ? intval($_POST["status"]) : 1;//状态
			
			//设置参数
			if (! $name) ajax_return(0, "链接名称不能为空！");
			if ($status < 0 || $status > 1) $status = 1;

			//判断是否存在
			$sql = "select id from `xyq_role` where name = '$name'";
			$res = $this->db->query($sql)->row_array();
			if ($res) ajax_return(0, "友情链接已存在！");
			
			//写入记录
			$sql = "insert into `xyq_role` (name, remark, status) values ('$name', '$remark', $status)";
			$this->db->query($sql);
			$res =$this->db->insert_id();
			
			//返回结果
			if (! $res) {
				ajax_return(0, "添加失败");
			} else {
				ajax_return(1, "添加成功", "/" . $this->_controller . "/index");
			}
		}
		$this->display();
	}
	
	/**
     * 编辑
     */
	public function edit($id = 0) {
		//校验参数
		$id = intval($id);
		if ($id < 0) {
			IS_AJAX && ajax_return(0, "参数有误");
			$this->error("参数有误");
		}
		
		//查询记录
		$sql = "select * from `xyq_role` where id = $id";
		$res = $this->db->query($sql)->row_array();
		if (! $res) {
			IS_AJAX && ajax_return(0, "记录不存在");
			$this->error("记录不存在");
		}
		
		if (IS_POST) {
			//接收参数
			$name = isset($_POST["name"]) ? sql_format($_POST["name"]) : "";//名称
			$remark = isset($_POST["remark"]) ? sql_format($_POST["remark"]) : "";
			$status = isset($_POST["status"]) ? intval($_POST["status"]) : 1;//状态
			
			//设置参数
			if (! $name) ajax_return(0, "链接名称不能为空！");
			if ($status < 0 || $status > 1) $status = 1;
			
			//判断是否存在
			$sql = "select id from `xyq_role` where name = '$name' and id != $id";
			$res = $this->db->query($sql)->row_array();
			if ($res) ajax_return(0, "友情链接名称已存在！");
			
			//更新记录
			$sql = "update `xyq_role` set name = '$name', remark = '$remark', status = $status where id = $id";
			$this->db->query($sql);
			
			//返回结果
			ajax_return(1, "修改成功", "/" . $this->_controller . "/index");
		}

		//分配变量
        $this->assign('res', $res);
		$this->display();
	}
	
	/**
     * 删除
     */
	public function del() {
		if (IS_AJAX) {
			//接收参数
			$id = isset($_POST["id"]) ? ($_POST["id"]) : "";
			if (! $id) ajax_return(0, "参数有误！");
			
			//删除记录
			$sql = "delete from `xyq_role` where id in (" . $id . ")";
			$this->db->query($sql);
			
			//返回结果
			ajax_return(1, "删除成功");
		}
	}
	
	/**
     * 授权
     */
	public function auth($id = 0) {
		//校验参数
		$id = intval($id);
		if ($id < 2) {
			IS_AJAX && ajax_return(0, "参数有误");
			$this->error("参数有误");
		}
		
		//查询记录
		$sql = "select * from `xyq_role` where id = $id";
		$res = $this->db->query($sql)->row_array();
		if (! $res) {
			IS_AJAX && ajax_return(0, "记录不存在");
			$this->error("记录不存在");
		}
		
		if (IS_AJAX) {
			//接收参数
			//101,102,103,106,201,202,301,302,303,304,305
			//update `xyq_role` set auth = '101,102,103,106,201,202,301,302,303,304,305' where id = 2
			$auth = isset($_POST["auth"]) ? $_POST["auth"] : "";
			if (! $auth) ajax_return(0, "参数有误！");
			
			//更新授权
			$sql = "update `xyq_role` set auth = '$auth' where id = $id";
			$this->db->query($sql);

			//清除缓存
			clear_cache(TPL_COMPILE_PATH . "/");
			clear_cache(CACHE_PATH . "/");
			
			//返回结果
			ajax_return(1, "保存成功");
		}
	
		//分配变量
		$this->assign('res', $res);
		$this->display();
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
			$sql = "update `xyq_role` set status = $status where id = $id";
			$this->db->query($sql);
			
			//返回结果
			ajax_return(1, "修改成功");
		}
	}
}
