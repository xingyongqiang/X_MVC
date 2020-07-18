<?php

/**
 * 账户中心
 *
 * @package		Hooloo framework
 * @author 		Bill
 * @copyright 	Hooloo Co.,Ltd
 * @version		1.0
 * @release		2017.08.21
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends Controller {
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
			$where = " where username like '%$keyword%'";
		} else {
			$where = "";
			$keyword = 0;
		}
		
		//数据分页
		$p = (int)$p <= 0 ? 1 : (int)$p;
		$size = 15;
		$start = ($p - 1) * $size;
		
		//统计总数
		$sql = "select count(*) as c from `xyq_admin`" . $where;
		$total = $this->db->query($sql)->row_array();
		$total = $total["c"];
		
		//查询记录
		$sql = "select * from `xyq_admin`" . $where . " order by id desc limit $start, $size";
		$list = $this->db->query($sql)->result_array();
		
		//分页展示
		$page = page_format($total, $size, $p, $keyword);
		
		//查询角色
		$sql = "select * from `xyq_role`";
		$res = $this->db->query($sql)->result_array();
		$role = array_column($res, "name", "id");
		
		//分配变量
		$this->assign("role", $role);
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
			$username = isset($_POST["username"]) ? substr(sql_format($_POST["username"]), 0, 10) : "";
			$password = isset($_POST["password"]) ? $_POST["password"] : "";
			$repassword = isset($_POST["repassword"]) ? $_POST["repassword"] : "";
			$nickname = isset($_POST["nickname"]) ? substr(sql_format($_POST["nickname"]), 0, 12) : "";
			$status = isset($_POST["status"]) ? (int)$_POST["status"] : 0;
			$pid = isset($_POST["pid"]) ? (int)$_POST["pid"] : 0;
			
			//校验参数
			if (! $username) {
				ajax_return(0, "帐号不能为空");
			} elseif (strlen($username) < 4 || strlen($username) > 10) {
				ajax_return(0, "帐号长度4至10位字符");
			}
			if (! $password) {
				ajax_return(0, "密码不能为空");
			} elseif (strlen($password) < 6 || strlen($password) > 12) {
				ajax_return(0, "密码长度6至12位字符");
			} elseif ($password != $repassword) {
				ajax_return(0, "两次密码不一致");
			} else {
				$password = md5($password);
			}
			if (! $nickname) {
				ajax_return(0, "名称不能为空");
			}
			
			//判断是否存在
			$sql = "select username from xyq_admin where username = '$username' or nickname = '$nickname'";
			$res = $this->db->query($sql)->row_array();
			if ($res) ajax_return(0, "帐号已存在，请更换其他帐号");

			//写入记录
			$sql = "insert into xyq_admin (username, password, nickname, status, pid) values ('$username', '$password', '$nickname', $status, '$pid')";
			$this->db->query($sql);
			$res =$this->db->insert_id();
			
			//返回结果
			if (! $res) {
				ajax_return(0, "添加失败");
			} else {
				ajax_return(1, "添加成功", "/" . $this->_controller . "/index");
			}
		}
		
		//查询角色
		$sql = "select * from `xyq_role` where status = 1";
		$role = $this->db->query($sql)->result_array();
		
		//分配变量
		$this->assign("role", $role);
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
		$sql = "select * from `xyq_admin` where id = $id";
		$res = $this->db->query($sql)->row_array();
		if (! $res) {
			IS_AJAX && ajax_return(0, "记录不存在");
			$this->error("记录不存在");
		}
		
		if (IS_POST) {
			//接收参数
			$username = isset($_POST["username"]) ? substr(sql_format($_POST["username"]), 0, 10) : "";
			$password = isset($_POST["password"]) ? $_POST["password"] : "";
			$repassword = isset($_POST["repassword"]) ? $_POST["repassword"] : "";
			$nickname = isset($_POST["nickname"]) ? mb_substr(sql_format($_POST["nickname"]), 0, 12) : "";
			$status = isset($_POST["status"]) ? (int)$_POST["status"] : 0;
			$pid = isset($_POST["pid"]) ? (int)$_POST["pid"] : 0;
			
			//设置参数
			if (! $username) {
				ajax_return(0, "帐号不能为空");
			} elseif (strlen($username) < 4 || strlen($username) > 10) {
				ajax_return(0, "帐号长度4至10位字符");
			}
			if (! $nickname) {
				ajax_return(0, "名称不能为空");
			}
			
			//判断帐号重复
			$sql = "select id from `xyq_admin` where username = '$username' and id != $id";
			$res = $this->db->query($sql)->row_array();
			if ($res) ajax_return(0, "帐号已存在");

			//判断名称重复
			$sql = "select id from xyq_admin where nickname = '$nickname' and id != $id";
			$res = $this->db->query($sql)->row_array();
			if ($res) ajax_return(0, "名称已存在");
			
			//更新记录
			if ($password) {
				if (strlen($password) < 6 || strlen($password) > 12) {
					ajax_return(0, "密码长度6至12位字符");
				} elseif ($password != $repassword) {
					ajax_return(0, "两次密码不一致");
				}
				$password = md5($password);
				$sql = "update xyq_admin set username = '$username', nickname = '$nickname', password = '$password', pid = $pid, status = $status where id = $id";
			} else {
				$sql = "update xyq_admin set username = '$username', nickname = '$nickname', pid = $pid, status = $status where id = $id";
			}
			$this->db->query($sql);
			
			//返回结果
			ajax_return(1, "修改成功", "/" . $this->_controller . "/index");
		}

		//查询角色
		$sql = "select * from `xyq_role` where status = 1";
		$role = $this->db->query($sql)->result_array();
		
		//分配变量
        $this->assign('res', $res);
        $this->assign('role', $role);
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
			$sql = "delete from `xyq_admin` where id in (" . $id . ")";
			$this->db->query($sql);
			
			//返回结果
			ajax_return(1, "删除成功");
		}
	}
	
	/**
     * 管理员登录
     * @param	string	$username	用户名
     * @param	string	$password 	密码
     * @param	array	$result 	返回结果
     */
	public function login() {
		//执行登录
		if (IS_POST) {
			//接收参数
			$username = isset($_POST["username"]) ? substr(sql_format($_POST["username"]), 0, 20) : "";
			$password = isset($_POST["password"]) ? $_POST["password"] : "";
			
			//校验参数
			if (! $username || ! $password) {
				ajax_return(0, "用户名或者密码不能为空");
			}

			//查找用户
			$sql = "select a.*, b.auth from xyq_admin a inner join xyq_role b on a.pid = b.id where a.username = '$username'";
			$res = $this->db->query($sql)->row_array();
			if (! $res) ajax_return(0, "您输入的管理帐号不存在");
			if (! $res["status"] == 1) ajax_return(0, "您的帐号已被禁用！");
			
			//判断登录次数
			if ($res["try_times"] > 10 && time() - strtotime($res["last_try"]) < 600) {
				ajax_return(0, "您登录次数太多，请10分钟后重试！");
			}

			
			//判断密码
			if (DEVELOPMENT_ENVIRONMENT == false && $res["password"] != md5($password)) {
				//更新输错次数
				$sql = "update xyq_admin set try_times = try_times + 1, last_try = CURRENT_TIMESTAMP, ip = '" . get_client_ip() . "' where username = '$username'";
				$this->db->query($sql);
				ajax_return(0, "您输入密码不正确，请重新输入");
			} else {
				//更新最后登录时间,输错次数为0
				$sql = "update xyq_admin set try_times = 0, last_try = CURRENT_TIMESTAMP, login_times = login_times + 1, last_login = CURRENT_TIMESTAMP, ip = " . get_client_ip() . " where username = '$username'";
				$this->db->query($sql);
				$res["last_login"] = date("Y-m-d H:i:s");

				//登录信息入session()
				$_SESSION["admin"] = $res;
			}
			
			//返回登录信息
			ajax_return(1, "登录成功");
		} else {
			$this->display();
		}
	}
	
	/**
     * 退出登录
     */
	public function logout() {
		//删除session
		if (isset($_SESSION["admin"])) {
			unset($_SESSION["admin"]);
		}
		
		//返回结果集
		IS_AJAX && ajax_return(1, "退出成功");
		redirect("/index/login");
	}
	
	/**
	 * 清除系统缓存
	 * @return 	boolean	 $result 	结果集：true-成功，false-失败
	 */
	public function clear_system_cache() {
		if (IS_AJAX) {
			//清除缓存
			clear_cache(TPL_COMPILE_PATH . "/");
			clear_cache(CACHE_PATH . "/");
			
			//返回结果集
			ajax_return(1, "缓存更新成功");
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
			$sql = "update `xyq_admin` set status = $status where id = $id";
			$this->db->query($sql);
			
			//返回结果
			ajax_return(1, "修改成功");
		}
	}
}
