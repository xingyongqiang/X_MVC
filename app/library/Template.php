<?php
/**
 * 模板引擎类
 *
 * 进行模板文件编译，编译规则可进一步改进
 * 配置项在config文件中定义
 *
 * @package		Hooloo framework
 * @author 		Peter
 * @copyright 	Hooloo Team
 * @version		1.0
 * @release		2017.04.27
 */
if (!defined('BASEPATH')) EXIT('No direct script asscess allowed');

class Template {
	
	private $tpl_html; //视图源文件名
	private $tpl_compile; //编译文件名
	private $tpl_update; //编译时间
	private $module;//分组名称
	
	public function __construct($html = '') {
		if ($html) {
			//源文件
			if (file_exists($html)) {
				$this->tpl_html = $html;
			} else {
				exit('视图文件不存在：' . $html);
			}
			
			//解析分组
			$module = str_ireplace(APPPATH . 'view/', "", $this->tpl_html);
			$len = strpos($module, "/");
			$this->module = substr($module, 0, $len);
			
			//编译文件
			$file_path = TPL_COMPILE_PATH . '/' . $this->module . '/';
			$this->tpl_compile = $file_path . md5($html) . '.php';
			if (DEVELOPMENT_ENVIRONMENT == false && file_exists($this->tpl_compile)) {
				$this->tpl_update = filemtime($this->tpl_compile);
			} else {
				if (! file_exists($file_path)) {
					mkdir($file_path);
				}
				$this->tpl_update = 0;
			}
		} else {
			exit('视图文件不存在：null');
		}
	}
	
	//显示页面
	public function display($data = array()) {
		//编译文件是否已过期
		if (filemtime($this->tpl_html) > $this->tpl_update) {
			//重新编译文件
			$this->compile();
		}
		//分配变量
		if ($data) {
			extract($data);
		}
		//加载编译文件
		include($this->tpl_compile);
	}
	
	//编译文件
	private function compile() {
		
		//处理界定符
		$a = TPL_LEFT_SEPERATOR;
		$left_sep = '/';
		for ($i = 0; $i < strlen($a); $i++) {
			$left_sep .= '\\' . $a[$i];
		}
		$left_sep .= '\s*';
		
		$a = TPL_RIGHT_SEPERATOR;
		$right_sep = '\s*';
		for ($i = 0; $i < strlen($a); $i++) {
			$right_sep .= '\\' . $a[$i];
		}
		$right_sep .= '/i';
		
		//读取源文件
		$a = file_get_contents($this->tpl_html);
		
		/**
		 * 编译过程
		 * 需按次序逐步进行
		 *
		 * 1.处理包含文件
		 * {include file="dir/file.html"} => 导入文件
		 */
		if (preg_match_all($left_sep . 'include\s+file\s*=\s*[\"\']?(.+?)[\"\']?' . $right_sep, $a, $res, PREG_SET_ORDER)) {
			foreach ($res as $v) {
				$html = APPPATH . 'view/' . $this->module . "/" . $v[1];
				if (file_exists($html)) {
					$include_file = file_get_contents($html);
					$a = str_replace($v[0], $include_file, $a);
				} else {
					exit('包含文件不存在：' . $v[1]);
				}
			}
		}
		
		/**
		 * 2.格式化变量
		 * 最大支持三维数组，数组写法可用：$a['b'], $a["b"], $a[b], $a.b => $a['b']
		 */
		$left = '/(\{|\(|\[|\=|\s)';
		$var_name = '(\$[A-z_][A-z0-9_]*)';
		$var_index = '(?:(?:\[[\'\"]?|\.)([A-z0-9_]+)(?:[\'\"]?\])?)';
		$right = '(\,|\s|\=|\)|\})/';
		$a = preg_replace($left . $var_name . $var_index . $var_index . $var_index . $right, "\\1\\2['\\3']['\\4']['\\5']\\6", $a);
		$a = preg_replace($left . $var_name . $var_index . $var_index . $right, "\\1\\2['\\3']['\\4']\\5", $a);
		$a = preg_replace($left . $var_name . $var_index . $right, "\\1\\2['\\3']\\4", $a);
		$a = preg_replace($left . $var_name . $right, "\\1\\2\\3", $a);
		
		/**
		 * 3.格式化循环语句
		 * {for $i = 1 to 10 step 2} => <?php for ($i = 1; $i <= 10; $i += 2) { ?>
		 * {/for} => <?php } ?>
		 * {foreach $aa as $k to $v} => <?php foreach ($aa as $k => $v) { ?>
		 * {foreach $aa as $v} => <?php foreach ($aa as $v) { ?>
		 * {/foreach} => <?php } ?>
		 */
		$var_name = '(\$[A-z_][A-z0-9_]*(?:\[(?:(?:\'[A-z0-9_]+\')|(?:\$[A-z_][A-z0-9_]*))\]){0,3})';
		$a = preg_replace($left_sep . 'foreach[\s\(]+' . $var_name . '\s+as\s+' . $var_name . '\s+to\s+' . $var_name . '[\s\)]*' . $right_sep, '<?php foreach (\\1 as \\2 => \\3) { ?>', $a);
		$a = preg_replace($left_sep . 'foreach[\s\(]+' . $var_name . '\s+as\s+' . $var_name . '[\s\)]*' . $right_sep, '<?php foreach (\\1 as \\2) { ?>', $a);
		$a = preg_replace($left_sep . 'for[\s\(]+' . $var_name . '\s*\=\s*(\d+)\s*to\s*(\d+)\s*step\s*(\-?\d+)[\s\)]*' . $right_sep, '<?php for (\\1 = \\2; \\1 <= \\3; \\1 += \\4) { ?>', $a);
		$a = preg_replace($left_sep . 'for[\s\(]+' . $var_name . '\s*\=\s*(\d+)\s*to\s*(\d+)[\s\)]*' . $right_sep, '<?php for (\\1 = \\2; \\1 <= \\3; \\1++) { ?>', $a);
		$a = preg_replace($left_sep . '\/(foreach|for|if)' . $right_sep, '<?php } ?>', $a);
		
		/**
		 * 4.格式化判断语句
		 * {if 1 == $a} => <?php if (1 == $a) { ?>
		 * {elseif 2 == $a} => <?php } elseif (2 == $a) { ?>
		 * {else} => <?php } else { ?>
		 * {/if} => <?php } ?>
		 */
		$a = preg_replace($left_sep . 'if\s+([^\}]+)' . $right_sep, '<?php if (\\1) { ?>', $a);
		$a = preg_replace($left_sep . 'else\s*if\s+([^\}]+)' . $right_sep, '<?php } elseif (\\1) { ?>', $a);
		$a = preg_replace($left_sep . 'else' . $right_sep, '<?php } else { ?>', $a);
		//封闭标签放在循环里处理
		
		/**
		 * 5.格式化变量输出
		 */
		$a = preg_replace($left_sep . $var_name . $right_sep, '<?php echo \\1; ?>', $a);
		
		/**
		 * 6.格式化注释
		 * 注释语法 {// 。。。} 或 {/* 。。。 * /}，{# 。。。 #}
		 * 编译时删除注释
		 */
		$a = preg_replace($left_sep . '\/\/.*' . $right_sep, '', $a);
		$a = preg_replace($left_sep . '\#.*\#' . $right_sep, '', $a);
		$a = preg_replace($left_sep . '\/\*[^\/]*\*\/' . $right_sep, '', $a);
		
		//格式化其他输出
		$a = preg_replace($left_sep . '([^\}]+)' . $right_sep, '<?php echo \\1; ?>', $a);
		
		//压缩空格
		// $a = preg_replace('/\s+/', ' ', $a); //先注释便于调试
		$a = str_replace('> <', '><', $a);
		
		//删除js多行注释
		$a = preg_replace('/\/\*(.+?)\*\//', '', $a);
		//删除html注释
		$a = preg_replace('/<!--(.+?)\/\/-->/', '', $a);
		
		//合并php标签
		$a = preg_replace('/;\s?;/', ';', $a);
		$a = str_replace('?><?php ', ' ', $a);
		
		//判断编译目录是否存在
		if (! file_exists(TPL_COMPILE_PATH)) {
			mkdir(TPL_COMPILE_PATH);
		}
		
		//写入编译文件
		file_put_contents($this->tpl_compile, $a);
	}
}
  