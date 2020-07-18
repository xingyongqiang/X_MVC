<?php

/**
 * 回收站
 *
 * @package		Hooloo framework
 * @author 		Bill
 * @copyright 	Hooloo Co.,Ltd
 * @version		1.0
 * @release		2017.08.21
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Recycle extends Controller {
	public function __construct() {
		parent::__construct();
	}
	
	/**
     * 列表
     */
	public function index($aid = 0, $cid = 0, $status = 99, $keyword = "", $p = 1) {
		//通用搜索
		$where = " where isdel = 1";
		$keyword = sql_format($keyword);
		if ($aid) $where .= " and aid = $aid";//专题
		if ($cid) $where .= " and cid = $cid";//栏目
		if ($status != 99) $where .= " and status = $status";//状态
		//搜索条件
		if ($keyword) {
			$where .= " and title like '%$keyword%'";
		} else {
			$keyword = 0;
		}
		
		//数据分页
		$p = (int)$p <= 0 ? 1 : (int)$p;
		$size = 15;
		$start = ($p - 1) * $size;
		
		//统计总数
		$sql = "select count(*) as c from `xyq_article`" . $where;
		$total = $this->db->query($sql)->row_array();
		$total = $total["c"];
		
		//查询记录
		$sql = "select * from `xyq_article`" . $where . " order by id desc limit $start, $size";
		$list = $this->db->query($sql)->result_array();

		//分页展示
		$page = page_format($total, $size, $p, $aid . "/" . $cid . "/" . $status . "/" . $keyword);
		
		//查询栏目
		$sql = "select * from `xyq_classes` order by sorts asc, id desc";
		$res = $this->db->query($sql)->result_array();
		$res_c = array_column($res, "name", "id");
		$classes = list_to_tree($res);
		
		//查询专题
		$sql = "select * from xyq_album where status = 1";
		$album = $this->db->query($sql)->result_array();
		$res_a = array_column($album, "name", "id");
		
		//分配变量
		$this->assign('classes', $classes);
        $this->assign('album', $album);
        $this->assign('list', $list);
        $this->assign('res_c', $res_c);
        $this->assign('res_a', $res_a);
        $this->assign('total', $total);
		
        $this->assign('aid', $aid);
        $this->assign('cid', $cid);
        $this->assign('keyword', $keyword);
        $this->assign('page', $page);
		$this->display();
	}
	
	/**
     * 还原
     */
	public function back() {
		if (IS_AJAX) {
			//接收参数
			$id = isset($_POST["id"]) ? strval($_POST["id"]) : "";
			if (! $id) ajax_return(0, "参数有误！");
			
			//删除记录
			$sql = "update `xyq_article` set isdel = 0 where id in (" . $id . ")";
			$this->db->query($sql);
			
			//返回结果
			ajax_return(1, "还原成功");
		}
	}

	/**
	 * 清空
	 */
	public function del_all() {
		if (IS_AJAX) {

			//删除记录
			$sql = "DELETE from xyq_article where isdel=1";
			$this->db->query($sql);

			//返回结果
			ajax_return(1, "清除成功");
		}
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
			$sql = "delete from `xyq_article` where id = $id";
			$this->db->query($sql);

			//返回结果
			ajax_return(1, "删除成功");
		}
	}
}
