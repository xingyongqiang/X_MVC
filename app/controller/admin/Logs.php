<?php

/**
 * 日志管理
 *
 * @package		Hooloo framework
 * @author 		Bill
 * @copyright 	Hooloo Co.,Ltd
 * @version		1.0
 * @release		2017.08.21
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Logs extends Controller {
	public function __construct() {
		parent::__construct();
	}
	
	/**
     * 列表
     */
	public function index($keyword = "", $p = 1) {
		$this->error("开发中！");
		//通用搜索
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
		$sql = "select count(*) as c from `cms_tags`" . $where;
		$total = $this->db->query($sql)->row_array();
		$total = $total["c"];
		
		//查询记录
		$sql = "select * from `cms_tags`" . $where . " order by id desc limit $start, $size";
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
     * 删除
     */
	public function del() {
		if (IS_AJAX) {
			//接收参数
			$id = isset($_POST["id"]) ? ($_POST["id"]) : "";
			if (! $id) ajax_return(0, "参数有误！");
			
			//删除记录
			$sql = "delete from `cms_tags` where id in (" . $id . ")";
			$this->db->query($sql);
			
			//返回结果
			ajax_return(1, "删除成功");
		}
	}
}
