<?php

/**
 * 站点设置
 *
 * @package		Hooloo framework
 * @author 		Bill
 * @copyright 	Hooloo Co.,Ltd
 * @version		1.0
 * @release		2018.08.21
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends Controller {
	public function __construct() {
		parent::__construct();
	}
	
	/**
     * 列表
     */
	public function index() {
		if (IS_POST) {
			//更新记录
			foreach ($_POST as $key => $val) {
//				if ($key != "sys_footer" && $key != "sys_js" && $key != "sys_logo" && $key != "sys_status") {
//					$val = sql_format($val);
//				} elseif ($key == "sys_status") {
//					$val = intval($val);
//				}

				$sql = "update xyq_setting set data = '$val' where name = '$key'";
				$this->db->query($sql);
			}

			//清除缓存
			clear_cache(TPL_COMPILE_PATH . "/");
			clear_cache(CACHE_PATH . "/");
		
			//返回结果集
			ajax_return(1, "保存成功", "/" . $this->_controller);
		}
		
		//查询记录
		$sql = "select * from xyq_setting";
		$list = $this->db->query($sql)->result_array();
		
		//分配变量
        $this->assign('list', $list);
		$this->display();
	}
}
