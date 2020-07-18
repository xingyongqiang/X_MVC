<?php

/**
 * 作者管理
 *
 * @package		Hooloo framework
 * @author 		Bill
 * @copyright 	Hooloo Co.,Ltd
 * @version		1.0
 * @release		2017.08.21
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Author extends Controller {
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
		$sql = "select count(*) as c from `cms_author`" . $where;
		$total = $this->db->query($sql)->row_array();
		$total = $total["c"];
		
		//查询记录
		$sql = "select * from `cms_author`" . $where . " order by hits desc, sorts asc limit $start, $size";
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
			$sorts = isset($_POST["sorts"]) ? intval($_POST["sorts"]) : 255;
			$status = isset($_POST["status"]) ? intval($_POST["status"]) : 1;//状态
			
			//设置参数
			if (! $name) ajax_return(0, "名称不能为空！");
			if ($status < 0 || $status > 1) $status = 1;
			
			
			//判断是否存在
			$sql = "select id from `cms_author` where name = '$name'";
			$res = $this->db->query($sql)->row_array();
			if ($res) ajax_return(0, "名称已存在！");
			
			//写入记录
			$sql = "insert into `cms_author` (name, sorts, status) values ('$name', '$sorts', $status)";
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
		$sql = "select * from `cms_author` where id = $id";
		$res = $this->db->query($sql)->row_array();
		if (! $res) {
			IS_AJAX && ajax_return(0, "记录不存在");
			$this->error("记录不存在");
		}
		
		if (IS_POST) {
			//接收参数
			$name = isset($_POST["name"]) ? sql_format($_POST["name"]) : "";//名称
			$sorts = isset($_POST["sorts"]) ? intval($_POST["sorts"]) : 255;
			$status = isset($_POST["status"]) ? intval($_POST["status"]) : 1;//状态
			
			//设置参数
			if (! $name) ajax_return(0, "名称不能为空！");
			if ($status < 0 || $status > 1) $status = 1;
			
			//判断是否存在
			$sql = "select id from `cms_author` where name = '$name' and id != $id";
			$res = $this->db->query($sql)->row_array();
			if ($res) ajax_return(0, "名称已存在！");
			
			//更新记录
			$sql = "update `cms_author` set name = '$name', sorts = $sorts, status = $status where id = $id";
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
			$id = isset($_POST["id"]) ? $_POST["id"] : "";
			if (! $id) ajax_return(0, "参数有误！");
			
			//删除记录
			$sql = "delete from `cms_author` where id in (" . $id . ")";
			$this->db->query($sql);
			
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
			$sql = "update `cms_author` set status = $status where id = $id";
			$this->db->query($sql);
			
			//返回结果
			ajax_return(1, "修改成功");
		}
	}
}
