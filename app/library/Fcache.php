<?php
/**
 * 文件缓存类
 *
 * 功能：利用硬盘上的文件实现缓存
 * 使用示例：
 * 		$fc = new Fcache(); //必须先实例化类
 * 		$fc->set($key, $val, $expire); //写入缓存，$key为任意可打印字符，$val为字符串或数组，$expire为过期时间，单位秒，默认86400
 * 		$res = $fc->get($key); //读取缓存
 *
 * @package		Hooloo framework
 * @author 		Peter
 * @copyright 	Hooloo Team
 * @version		1.1
 * @release		2017.06.16
 */
if (!defined('BASEPATH')) EXIT('No direct script asscess allowed');

class Fcache {
	
	public function __construct() {
	}
	
	//写入缓存
	public function set($key = '', $val = '', $exp = 86400) {
		if ($key && $val && $exp > 0) {
			$f = $this->get_filename($key, 1);
			if (is_array($val)) $val = json_encode($val);
			if (@file_put_contents($f, $val)) {
				if (@touch($f, time() + $exp)) {
					return true;
				}
			}
		}
		return false;
	}
	
	//读取缓存
	public function get($key = '') {
		if ($key) {
			$f = $this->get_filename($key);
			if (file_exists($f) && filemtime($f) > time()) {
				$val = @file_get_contents($f);
				if (substr($val, 0, 1) == '{' || substr($val, 0, 2) == '[{') $val = json_decode($val, true);
				return $val;
			}
		}
		return false;
	}
	
	//获取文件名
	private function get_filename($key, $type = 0) {
		$fn = md5($key) . '_' . strtolower(substr(urlencode($key), 0, 200));
		$fp = CACHE_PATH . '/' . substr($fn, 0, 2) . '/' . substr($fn, 2, 2);
		$f = $fp . '/' . $fn;
		if ($type && ! file_exists($fp)) @mkdir($fp, 0777, true);
		return $f;
	}
	
}
  