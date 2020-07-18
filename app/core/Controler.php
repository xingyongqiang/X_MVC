<?php
/**
 * 主控制器
 *
 * @package        Hooloo framework
 * @author        Peter
 * @copyright    Hooloo Team
 * @version        1.0
 * @release        2017.04.27
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Controller
{
    protected $_controller; //控制器名
    protected $_action; //方法名
    protected $_module; //分组名
    protected $db; //mysql数据库
    protected $sys_conf; //系统配置
    protected $_data; //模板数据
    protected $_base_url = 'https://xcx.xingyongqiang.com/';

    public function __construct()
    {
        $this->_controller = strtolower($GLOBALS['controller']);
        $this->_action = strtolower($GLOBALS['action']);
        $this->_module = strtolower($GLOBALS['module']);

        //初始化数据库
        $this->init_db();

        //初始化用户
        if ($this->_module == "admin") {
            //初始化登录
            $this->init_admin();
        } else {
            //初始化配置
            //$this->init_user();
        }

        //全局变量
        $common_info = array(
            'module' => $this->_module,
            'controller' => $this->_controller,
            'action' => $this->_action
        );
        $this->assign('common_info', $common_info);
    }

    //暂时无用
    /* public function __destruct () {
        //关闭数据库连接
        if ($this->db) {
            $this->db->close();
        }
    } */

    /**
     * 初始化后台登录
     */
    private function init_admin()
    {
        if ($this->_action == "login") {
            //防止用户重复登录
            if (isset($_SESSION["admin"])) {
                //返回登录信息
                IS_AJAX && ajax_return(2, "您已经登录");
                redirect("/");
            }
        } else {
            //防止用户未登录
            if (!isset($_SESSION["admin"])) {
                //返回登录信息
                IS_AJAX && ajax_return(2, "您还未登录，请先登录");
                redirect("/user/login");
            }
        }
        //检查授权
        $this->init_auth();
        //分配菜单
        //$GLOBALS — 引用全局作用域中可用的全部变量
        if (isset($_SESSION["admin"])) {
            $this->assign("sys_admin", $_SESSION["admin"]);
        }
        $this->assign("sys_menu", $GLOBALS["menu"]);
    }

    /**
     * 授权管理
     */
    private function init_auth()
    {
        //登录，退出，清理缓存，超级管理员不进行校验
        if ($this->_action != "login" && $this->_action != "logout" && $this->_action != "clear_system_cache" && $_SESSION["admin"]["pid"] != 1) {
            //加载缓存
            $cache = new Fcache();

            //权限配置
            $sys_auth = $cache->get("sys_auth");
            if (!$sys_auth) {
                $menu = $GLOBALS["menu"];
                $sys_auth = array();
                foreach ($menu as $key => $val) {
                    $sys_auth[$val["nid"]] = $key;
                }
                //放入缓存
                $cache->set("sys_auth", $sys_auth, 86400);
            }

            //如果存在权限分配
            if (isset($sys_auth[$this->_controller])) {
                $auth_id = (string)$sys_auth[$this->_controller];
                if (strpos($_SESSION["admin"]["auth"], $auth_id) === false) {
                    IS_AJAX && ajax_return(0, "权限不足！");
                    $this->error("权限不足！");
                }
            }
        }
    }


    /**
     * 初始化前台
     */
    private function init_user()
    {
        //加载缓存
        $cache = new Fcache();

        //站点配置
        $sys_config = $cache->get("sys_config");
        if (!$sys_config) {
            $sql = "select id, `name`, `data` from xyq_setting";
            $sys = $this->db->query($sql)->result_array();
            $sys_config = array_column($sys, "data", "name");
            //放入缓存
            $cache->set("sys_config", $sys_config, 86400);
        }
        $this->sys_conf = $sys_config;

        //栏目导航
        $sys_classes = $cache->get("sys_classes");
        if (!$sys_classes) {
            //查询栏目
            $sql = "select id,`name`,url,pid,nid,sorts from `xyq_nav` where status = 1 order by sorts asc";
            $sys_classes = $this->db->query($sql)->result_array();
            $sys_classes = list_to_tree($sys_classes);

            //放入缓存
            $cache->set("sys_classes", $sys_classes, 86400);
        }
        //$this->classes = $sys_classes;

        //分配变量
        $this->assign("classes", $sys_classes);
        $this->assign("sys_conf", $this->sys_conf);
    }

    /**
     * 初始化数据库
     * @param int $server_id 服务器id    1-主服务器；2-从服务器
     */
    protected function init_db($server_id = 1)
    {
        if (!$this->db) {
            global $config;
            $db_config = $config['db_' . $server_id];
            $this->db = new Database();
            $this->db->connect($db_config['hostname'], $db_config['username'], $db_config['password'], $db_config['database']);
            $GLOBALS['db'] = $this->db;
        }
    }

    /**
     * 分配变量
     * @param string $key 标签名
     * @param mix $val 变量值
     */
    protected function assign($key, $val)
    {
        if ($key && preg_match('/^[A-z_][A-z0-9_]*$/', $key)) {
            $this->_data[$key] = $val;
        } else {
            exit('变量名不合法：' . $key);
        }
    }

    /**
     * 页面输出
     */
    protected function display($html = '')
    {
        //视图文件
        if (is_mobile() && $this->_module == "home") {
            $module = "mobile";
        } else {
            $module = $this->_module;
        }
        //$module = $this->_module;
        if (!$html) {
            $html = APPPATH . 'view/' . $module . '/' . $this->_controller . '/' . $this->_action . '.html';
        } else {
            $html = APPPATH . 'view/' . $module . '/' . $html . '.html';
        }
        //加载模板
        $tpl = new Template($html);
        $runtime = intval((microtime(true) - BEGINTIME) * 1000);
        $this->assign("runtime", $runtime);
        $tpl->display($this->_data);
        exit;
    }

    /**
     * 错误页面输出
     */
    protected function error($msg = "")
    {
        $this->assign("msg", $msg);
        $this->display("/public/error");
    }

    /**
     * 图片上传
     */
    public function ajax_upload_images()
    {
        //接收参数
        $file = array_keys($_FILES)[0];
        $file_name = "/upload/image/" . date("Ymd") . "/" . date("YmdHis") . ".jpg";

        //上传文件
        $upload = new Upload();
        $result = $upload->run_upload($file, $file_name);

        //返回上传结果
        ajax_return($result['status'], $result['msg'], $result);
    }

    /**
     * 文件上传
     */
    public function ajax_upload_file()
    {
        //接收参数
        $file = array_keys($_FILES)[0];

        $file_info = pathinfo($_FILES[$file]['name']);//解析文件路径
        $ext = '.' . strtolower($file_info['extension']);//文件后缀

        $file_name = "/upload/file/" . date("Ymd") . "/" . date("YmdHis") . $ext;

        //上传文件
        $upload = new Upload();
        $result = $upload->run_upload($file, $file_name, "file");

        //返回上传结果
        ajax_return($result['status'], $result['msg'], $result);
    }


    /**
     * 编辑器图片上传
     */
    public function ajax_editor_upload()
    {
        //接收参数
        $file = array_keys($_FILES)[0];
        $dir = empty($_GET['dir']) ? "image" : $_GET['dir'];

        //解析参数
        if ($dir == "file") {
            $file_name = "/upload/" . $dir . "/" . date("Ymd") . "/" . date("YmdHis") . $_FILES[$file]['name'];
        } else {
            if ($dir == "image") {
                $file_name = "/upload/" . $dir . "/" . date("Ymd") . "/" . date("YmdHis") . ".jpg";
            } else {
                $file_info = pathinfo($_FILES[$file]['name']);//解析文件路径
                $ext = '.' . strtolower($file_info['extension']);//文件后缀
                $file_name = "/upload/" . $dir . "/" . date("Ymd") . "/" . date("YmdHis") . $ext;
            }
        }

        //上传文件
        $upload = new Upload();
        $result = $upload->run_upload($file, $file_name, $dir);

        //返回结果集
        header('Content-type: text/html; charset=UTF-8');
        if ($result['status'] == 1) {
            echo(json_encode(array('error' => 0, 'url' => $result['full_path'])));
            exit;
        } else {
            echo(json_encode(array('error' => 1, 'message' => $result['msg']), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 编辑器空间图片
     */
    public function ajax_editor_manager()
    {
        $root_path = "upload/";
        $root_url = '/' . $root_path;
        $ext_arr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');
        $dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
        if (!in_array($dir_name, array('', 'image', 'flash', 'media', 'file'))) {
            echo "Invalid Directory name.";
            exit;
        }
        if ($dir_name !== '' && $dir_name != 'file') {
            $root_path .= $dir_name . "/";
            $root_url .= $dir_name . "/";
            if (!file_exists($root_path)) {
                @mkdir($root_path);
            }
        }

        //根据path参数，设置各路径和URL
        if (empty($_GET['path'])) {
            $current_path = str_replace("\\", "/", realpath($root_path)) . '/';
            $current_url = $root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = str_replace("\\", "/", realpath($root_path)) . '/' . $_GET['path'];
            $current_url = $root_url . $_GET['path'];
            $current_dir_path = $_GET['path'];
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }

        //排序形式，name or size or type
        $this->_order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);

        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            exit;
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            exit;
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            exit;
        }

        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') continue;
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $type = pathinfo($file);
                    $file_ext = strtolower($type['extension']);
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }
        //这个地方关闭图片按照大小排序
        //usort($file_list, array($this, '_cmp_func'));
        $result = array();
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = $current_url;
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;

        //输出JSON字符串
        header('Content-type: application/json; charset=UTF-8');
        echo json_encode($result);
        exit;
    }

    /*
     * 多维数组排序
     * 导航拼接-排序
     */
    function my_array_multi_sort($data = array(), $sort_order_field = "", $sort_order, $sort_type)
    {
        foreach ($data as $val) {
            $key_arrays[] = $val[$sort_order_field];
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $data);
        return $data;
    }

    /**
     * 获得运费---套娃递归
     * @param $consignee
     * @param $shop_id
     * @param int $express_id
     * @return int
     */
    protected function get_express_area_info($consignee, $shop_id, $express_id = 14)
    {
        $info = $this->get_express_area_info_price($express_id, $shop_id, $consignee['region_id']);
        if (!$info) {
            $info = $this->get_express_area_info_price($express_id, $shop_id, $consignee['city_id']);
            if (!$info) {
                $info = $this->get_express_area_info_price($express_id, $shop_id, $consignee['province_id']);
                if (!$info) {
                    $info = $this->get_express_area_info_price($express_id, $shop_id, 1);
                    if ($info) {
                        return $info['price'];
                    } else {
                        return 0;
                    }
                } else {
                    return $info['price'];
                }
            } else {
                return $info['price'];
            }
        } else {
            return $info['price'];
        }
    }

    /**
     * 返回运费
     * @param $express_id
     * @param $shop_id
     * @param $region_id
     * @return int
     */
    public function get_express_area_info_price($express_id, $shop_id, $region_id)
    {
        $where = "a.express_id = $express_id and a.shop_id = $shop_id and s.region_id = $region_id";
        $sql = "select a.price,a.express_id from xyq_express_area_region s join xyq_express_area a on s.express_area_id = a.express_area_id where " . $where;
        return $this->db->query($sql)->row_array();
    }
}
