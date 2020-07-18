<?php
/**
 * 公共辅助函数
 */
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ajax_return                Ajax方式返回数据到客户端
 * @param array $data 要返回的数据
 * @param string $type AJAX返回数据格式
 * @param return    void    结果集
 */
function ajax_return($status = 1, $msg = '', $data = '')
{
    //表单令牌
    global $token;
    if ($token) {
        unset($_SESSION["token"]);
        $token = md5(date("Y-m-d H:i:s") . rand_code(6));
        if ($status == 0) {
            $_SESSION["token"] = $token;
        }
    } else {
        $token = "";
    }

    //返回数据
    $return = array(
        'status' => $status,
        'msg' => $msg,
        'data' => $data,
        'token' => $token
    );
    // 默认返回JSON数据格式到客户端 包含状态信息
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($return, JSON_UNESCAPED_UNICODE));
}


/**
 * ajax_return Ajax方式返回数据到客户端
 * @param array $data 要返回的数据
 * @param string $type AJAX返回数据格式
 * @param return    void    结果集
 */
function admin_ajax_return($status = 1, $msg = '', $data = '', $count = '')
{
    //返回数据
    $return = array(
        'code' => $status,
        'msg' => $msg,
        'data' => $data,
        'count' => $count
    );
    // 默认返回JSON数据格式到客户端 包含状态信息
    header('Content-Type:application/json; charset=utf-8');
    exit(json_encode($return, JSON_UNESCAPED_UNICODE));
}

/**
 * dump                    自定义打印变量
 * @param mixed $var 变量
 * @return    void            无返回结果
 */
function custom_dump($var)
{
    ob_start();
    var_dump($var);
    $output = ob_get_clean();
    $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
    $output = '<pre>' . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
    echo($output);
}

/**
 * redirect                    网址重定向
 * @param string $uri 要跳转的网址
 * @param string $method 跳转方法
 * @param int $code 状态码
 */
function redirect($uri = '', $method = 'auto', $code = null)
{
    if (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== FALSE) {
        $method = 'refresh';
    } elseif ($method !== 'refresh' && (empty($code) || !is_numeric($code))) {
        if (isset($_SERVER['SERVER_PROTOCOL'], $_SERVER['REQUEST_METHOD']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.1') {
            $code = ($_SERVER['REQUEST_METHOD'] !== 'GET')
                ? 303    // reference: http://en.wikipedia.org/wiki/Post/Redirect/Get
                : 307;
        } else {
            $code = 302;
        }
    }
    if ($GLOBALS['module'] != DEFAULT_MODULE) {
        $uri = "/" . $GLOBALS['module'] . $uri;
    }
    if ('refresh' == $method) {
        header('Refresh:0;url=' . $uri);
    } else {
        header('Location: ' . $uri, true, $code);
    }
    exit;
}

/**
 * rand_code                    生成随机码
 * @param int $len 随机码长度
 * @param string $type 随机码类型：num-数字，str-小写字母，astr-大写字母，both-小写字母和数字，all-全部字符
 * @return string    $result    返回随机码
 */
function rand_code($len = 6, $type = 'num')
{
    $result = '';
    $num = '0123456789';
    $str = 'abcdefghijklmnopqrstuvwxyz';
    $astr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $typelist = array(
        'num' => $num,
        'str' => $str,
        'astr' => $astr,
        'both' => $str . $num,
        'all' => $astr . $str . $num
    );
    for ($i = 0; $i < $len; $i++) {
        $result .= substr($typelist[$type], mt_rand(0, strlen($typelist[$type]) - 1), 1);
    }
    return $result;
}

/**
 * clear_cache            更新缓存（仅删除runtime目录下所有文件。保留runtime目录）
 * @return    void        无返回内容：cache-系统缓存目录，logs-系统错误目录，runtime-编译目录，session-session目录
 */
function clear_cache($directory = "")
{
    if (is_dir($directory) == false) {
        return false;
    }
    $handle = opendir($directory);
    while (($file = readdir($handle)) !== false) {
        if ($file != "." && $file != "..") {
            is_dir("$directory/$file") ? clear_cache("$directory/$file") : unlink("$directory/$file");
        }
    }
    if (readdir($handle) == false) {
        closedir($handle);
        //rmdir($directory);
    }
    return true;
}

/**
 * get_client_ip            获取客户端IP地址
 * @param integer $type 返回类型：0-返回IP地址，1-返回IPV4地址数字
 * @return string            返回结果集
 */
function get_client_ip($type = 1)
{
    $ip = "";
    if (isset($_SERVER['HTTP_X_CLIENTIP'])) {
        $ip = $_SERVER['HTTP_X_CLIENTIP'];
    } elseif (isset ($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER ['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
            unset ($arr [$pos]);
        $ip = trim($arr [0]);
    } elseif (isset ($_SERVER ['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER ['HTTP_CLIENT_IP'];
    } elseif (isset ($_SERVER ['REMOTE_ADDR'])) {
        $ip = $_SERVER ['REMOTE_ADDR'];
    } elseif (isset($_SERVER['HTTP_X_CLIENTIP']) && isset ($_SERVER ['REMOTE_ADDR'])) {
        $ip = $_SERVER ['HTTP_X_CLIENTIP'];
    } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && isset ($_SERVER ['HTTP_X_CLIENTIP'])) {
        $ip = $_SERVER ['HTTP_X_REAL_IP'];
    }
    if ($ip == "::1") $ip = "127.0.0.1";
    if ($type == 1) {
        return ip2long($ip);
    } else {
        return $ip;
    }
}

/**
 * msubstr                        截取字符串长度函数
 * @param string $string 需要处理字符串
 * @param int $length 处理长度
 * @param string $ellipsis 隐藏的省略样式
 * @return    void|string            返回结果集
 */
function msubstr($string, $length = 0, $ellipsis = '...', $strip = 0)
{
    $string = htmlspecialchars_decode($string);
    $string = str_ireplace('<br />', PHP_EOL, $string);
    $string = str_ireplace('<br/>', PHP_EOL, $string);
    $string = str_ireplace('<br>', PHP_EOL, $string);
    //剥去字符串中的 HTML、XML 以及 PHP 的标签。
    $string = strip_tags($string);
    if ($strip == 1) {
        //换行替换为空格
        $string = str_replace(PHP_EOL, ' ', $string);
    } else {
        $string = str_replace(PHP_EOL, '<br />', $string);
    }

    $len = 0;
    $res = '';
    for ($i = 0; $i < mb_strlen($string); $i++) {
        $word = mb_substr($string, $i, 1, 'utf-8');
        if (strlen($word) == 1) {
            $len += strlen($word);
        } else {
            $len += strlen($word) - 1;
        }
        if ($len < $length) {
            $res .= $word;
        } else {
            break;
        }
    }
    //过滤不完整的标签
    $end = strrpos($res, "<");
    if ($end) {
        if (strpos($res, ">", $end) === false) {
            $res = substr($res, 0, $end);
        }
    }
    //如果没有完整输出则加上 ...
    if ($length < strlen($string)) {
        $res .= $ellipsis;
    }
    return $res;
}

/**
 * format_time                    日期格式化   判断时间是否是今天，如果是今天只返回时间，不返回日期
 * @param date $time 格式要求Y-m-d H:i:s 或者时间戳格式
 * @param int $date_type 输出时间格式：1-显示短日期：“8-21”；“9：00”； 2-时间差格式：“3天前”；“2小时前”
 * @param string $result 返回的格式 Y   m-d   H:i
 **/
function time_format($time, $date_type = 1)
{
    $result = '';
    if ($time) {
        //转换为时间戳
        $time_ux = is_numeric($time) ? $time : strtotime($time);
        if ($date_type == 1) {
            // 转换为 YYYY-MM-DD 格式
            $time_day = date('Y-m-d', $time_ux);
            $time_month = date('m', $time_ux);
            $time_year = date('Y', $time_ux);
            // 获取今天的 YYYY-MM-DD 格式
            if ($time_day == date('Y-m-d')) {
                //当天
                $result = date('H:i', $time_ux);
            } elseif ($time_year == date('Y')) {
                //本年
                $result = date('m-d', $time_ux);
            } else {
                //其他年份
                $result = date('Y', $time_ux);
            }
        } elseif ($date_type == 2) {
            if (date('Y-m-d', $time_ux) == date('Y-m-d')) {
                //今天
                $timediff = floor((time() - $time_ux) / 60); //时间差：分钟
                if ($timediff > 59) {
                    $result = floor($timediff / 60) . "小时前";
                } elseif ($timediff > 0) {
                    $result = $timediff . "分钟前";
                } else {
                    $result = "刚刚";
                }
            } elseif (date('Y-m-d', $time_ux + 86400) == date('Y-m-d')) {
                $result = "昨天";
            } elseif (date('Y-m-d', $time_ux + 86400 * 2) == date('Y-m-d')) {
                $result = "前天";
            } else {
                $timediff = floor((time() - $time_ux) / 86400); //时间差：天
                if ($timediff > 365) {
                    $result = floor($timediff / 365) . "年前";
                } elseif ($timediff > 30) {
                    $result = floor($timediff / 30) . "个月前";
                } else {
                    $result = $timediff . "天前";
                }
            }
        } elseif ($date_type == 3) {
            $result = date('Y-m-d', $time_ux);
        } elseif ($date_type == 4) {
            $result = "<span class='day'>" . date('d', $time_ux) . "</span><br /><span class='years'>" . date('Y-m', $time_ux) . "</span>";
        }
    }
    return $result;
}

/**
 *判断是PC端访问还是移动端访问
 */
function is_mobile()
{
    $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $mobile_type = array('iphone', 'ipad', 'ipod', 'windows mobile', 'windows phone', 'windows ce', 'android', 'blackberry', 'bb10', 'maemo');
    $is_mobile = false;
    foreach ($mobile_type as $m) {
        if (false !== strpos($user_agent, $m)) {
            $is_mobile = true;
            break;
        }
    }
    if ($is_mobile) {
        return true;
    } else {
        return false;
    }
}

/**
 * sql_format                格式化插入数据库的短字符串（用于用户名、usercode、邮箱等）
 * @param string $str 要转换的字符串
 * @return    string $str        返回结果集
 */
function sql_format($str)
{
    //过滤用户输入
    $str = urldecode($str);
    $str = str_format_filter($str);
    //删除非法字符
    $str = str_replace("'", "", $str);
    $str = str_replace("&", "", $str);
    $str = str_replace("=", "", $str);
    $str = str_replace("\"", "", $str);
    $str = str_replace("\\", "", $str);

    return $str;
}

/**
 * str_format_filter        字符串过滤函数
 * @param string $str 要过滤的字符串
 * @return    string $str        返回结果集
 */
function str_format_filter($str)
{
    //转换空格
    $str = str_replace("　", " ", $str); //全角空格
    $str = str_replace(" ", " ", $str); //一个奇怪的空格符
    $str = str_replace(chr(9), " ", $str); //制表符
    //特殊符号
    $arr = array(
        '＜' => '《',
        '＞' => '》',
        '〝' => '“',
        '〞' => '”',
        '′' => "'",
        '﹙' => '（',
        '﹚' => '）',
        '\\' => '' //过滤转义字符
    );
    $str = strtr($str, $arr);

    //html转义字符
    $str = str_ireplace("&amp;", "&", $str);
    $str = str_ireplace("&nbsp;", " ", $str);
    $str = str_ireplace("&quot;", "\"", $str);
    $str = str_ireplace("&lt;", "<", $str);
    $str = str_ireplace("&gt;", ">", $str);
    $str = str_ireplace("&#8206;", "", $str);

    //删除多余空格
    $str = preg_replace('/\s+/', ' ', $str);

    //删除多余单引号
    $str = preg_replace("/\'+/", "'", $str);

    //过滤字符串首尾空格
    $str = trim($str);

    return $str;
}

/**
 * @access chk_phone            验证手机号
 * @param int $mobile 手机号码
 * @param return boolean        结果集：true-正确，false-错误
 */
function chk_phone($mobile = "")
{
    if (preg_match("/^1[34578]{1}\d{9}$/", $mobile)) {
        return true;
    } else {
        return false;
    }
}


/**
 * 后台分页页码显示
 * @access page_format            数据分页
 * @param int $total 记录总数，
 * @param int $pagesize 每页条数。
 * @param int $p 当前页码
 * @param string $url 跳转url
 */
function page_format($total = 0, $pagesize = 20, $p = 1, $url = "")
{
    $url = "/" . $GLOBALS['module'] . "/" . $GLOBALS['controller'] . "/" . $GLOBALS['action'] . "/" . $url;
    $url = str_replace(" ", "", $url);
    $page_show = "";
    $p = (int)$p; //当前页
    $total = (int)$total;
    $pagesize = (int)$pagesize;
    if ($p < 1) $p = 1;
    $pend = ceil($total / $pagesize); //尾页
    if ($pend > 1) {
        $page_show .= "<div class=\"layui-box layui-laypage layui-laypage-default\">";
        if ($p > $pend) $p = $pend;
        $p1 = $p - 4; //开始页码
        if ($p1 < 1) $p1 = 1;
        $p2 = $p + 4; //结束页码
        if ($p2 > $pend) $p2 = $pend;
        if ($p > 1) {
            $page_show .= " <a href=\"" . $url . ".html\">首页</a><a href=\"" . $url . "/" . ($p - 1) . ".html\">上一页</a>";//上部分
        }
        for ($i = $p1; $i <= $p2; $i++) {
            if ($i != $p) {
                $page_show .= " <a href=\"" . $url . "/" . $i . ".html\">" . $i . "</a>";//其他页
            } else {
                $page_show .= " <span class=\"layui-laypage-curr\"><em class=\"layui-laypage-em\"></em><em>" . $i . "</em></span>";//当前页
            }
        }
        if ($p < $pend) {
            $page_show .= " <a href=\"" . $url . "/" . ($p + 1) . ".html\">下一页</a><a href=\"" . $url . "/" . $pend . ".html\">尾页</a>";//下部分
        }
        $page_show .= "</div>";
    }
    return $page_show;
}

/**
 * 前台分页页码显示
 * @access  forPage                数据分页
 * @param int $total 记录总数，
 * @param int $pagesize 每页条数。
 * @param int $p 当前页码
 * @param string $url 跳转url
 * $url      0-新闻案例首页 其他为列表页
 */
function forListPage($total = 0, $pagesize = 20, $p = 1, $url = "")
{
    if ($url == "0") {
        $url = "/" . $GLOBALS['module'] . "/" . $GLOBALS['controller'] . "/" . $GLOBALS['action'];
    } else {
        $url = "/" . $GLOBALS['module'] . "/" . $GLOBALS['controller'] . "/" . $GLOBALS['action'] . "/" . $url;
    }
    $url = str_replace(" ", "", $url);
    $page_show = "";
    $p = (int)$p; //当前页
    $total = (int)$total;
    $pagesize = (int)$pagesize;
    if ($p < 1) $p = 1;
    $pend = ceil($total / $pagesize); //尾页
    if ($pend > 1) {
        if ($p > $pend) $p = $pend;
        $p1 = $p - 4; //开始页码
        if ($p1 < 1) $p1 = 1;
        $p2 = $p + 4; //结束页码
        if ($p2 > $pend) $p2 = $pend;
        if ($p > 1) {
            $page_show .= " <a href=\"" . $url . ".html\">首页</a><a href=\"" . $url . "/" . ($p - 1) . ".html\">上一页</a>";//上部分
        }
        for ($i = $p1; $i <= $p2; $i++) {
            if ($i != $p) {
                $page_show .= " <a href=\"" . $url . "/" . $i . ".html\">" . $i . "</a>";//其他页
            } else {
                $page_show .= "<a class=\"a_active\" href=\"javascript:;\">" . $i . "</a>";//当前页
            }
        }
        if ($p < $pend) {
            $page_show .= "<a href=\"" . $url . "/" . ($p + 1) . ".html\">下一页</a><a href=\"" . $url . "/" . $pend . ".html\">尾页</a>";//下部分
        }
    }
    return $page_show;
}

/**
 * list_to_tree                把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pk 首字段
 * @param string $pid 父标记字段
 * @param string $child 子标记字段
 * @return array
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        //p=&a 表明 把a的地址 赋值给p (p是指针)
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parent_id = $data[$pid];
            if ($root == $parent_id) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parent_id])) {
                    $parent =& $refer[$parent_id];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * send_post                    模拟post请求(file_get_contents方式)
 * @param string $url 请求地址
 * @param array $post_data post键值对数据
 * @return    string    $result        返回结果集
 */
function send_post($url, $post_data = array())
{
    $postdata = http_build_query($post_data);
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $postdata,
            'timeout' => 300 // 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
}

/**
 * send_curl  模拟post请求(CURL方式)
 * @param string $url 请求地址
 * @param array $params post键值对数据
 * @return    string    $result        返回结果集
 */
function send_curl($url, $params = array())
{
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址  
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回  
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容 
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 设置为 1 是检查服务器SSL证书中是否存在一个公用名(common name)。译者注：公用名(Common Name)一般来讲就是填写你将要申请SSL证书的域名 (domain)或子域名(sub domain)。 设置成 2，会检查公用名是否存在，并且是否与提供的主机名匹配。 0 为不检查名称。 在生产环境中，这个值应该是 2（默认值）
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'); // 模拟用户使用的浏览器  
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转  
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer 
    if ($params) {
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        if (is_array($params)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params); // Post提交的数据包
        }
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 设置超时限制防止死循环  
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
    $res = curl_exec($curl); // 执行操作
    //检查是否404（网页找不到）
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl); // 关闭CURL会话
    if ($httpcode == 404 || $httpcode == 403) {
        return false;
    } else {
        return $res;
    }
}

/**
 * send_sms                    发送短信(目前使用聚合短信)
 * @param int $mobile 手机号
 * @param var $content 短信内容
 * @return    array    $result        状态：0-失败，1-成功
 * 模版id:33599-温馨提示#code#。如非本人操作，请忽略本短信
 * 模版id:33592-您的验证码是#code#。如非本人操作，请忽略本短信
 */
function send_sms($mobile, $content = "")
{
    if (!$content) {
        $content = rand_code();
        $tpl_id = 33592;
    } else {
        $tpl_id = 33599;
    }

    //判断手机号是否为空
    if (!$mobile) {
        $return["status"] = 0;
        $return["msg"] = "手机号码为空";
        return $return;
    }

    //验证手机号
    if (!chk_phone($mobile)) {
        $return["status"] = 0;
        $return["msg"] = "号码不正确";
        return $return;
    }
    //短信接口地址
    $url = 'http://v.juhe.cn/sms/send?mobile=' . urlencode($mobile) . '&tpl_id=' . $tpl_id . '&tpl_value=' . urlencode('#code#=' . $content) . '&key=1cce99c8d59b536c8792a54074fdf538';
    $res = send_curl($url);
    $res = json_decode($res, true);
    if (!$res) {
        $return['status'] = 0;
        $return['msg'] = "短信发送失败";
        return $return;
    } else {
        if ($res['error_code'] == 0) {
            //状态为0，说明短信发送成功
            $return['status'] = 1;
            $return['msg'] = "验证码已发送到您的手机，请查看短信";
        } else {
            //错误标识
            $error_code = array(
                "205401" => "错误的手机号码",
                "205402" => "错误的短信模板ID",
                "205403" => "网络错误,请重试",
                "205404" => "发送失败，具体原因请参考返回reason",
                "205405" => "号码异常/同一号码发送次数过于频繁",
                "205406" => "不被支持的模板",
                "10001" => "错误的请求KEY",
                "10002" => "该KEY无请求权限",
                "10003" => "KEY过期",
                "10004" => "错误的OPENID",
                "10005" => "应用未审核超时，请提交认证",
                "10007" => "未知的请求源",
                "10008" => "被禁止的IP",
                "10009" => "被禁止的KEY",
                "10011" => "当前IP请求超过限制",
                "10012" => "请求超过次数限制",
                "10013" => "测试KEY超过请求限制",
                "10014" => "系统内部异常(调用充值类业务时，请务必联系客服或通过订单查询接口检测订单，避免造成损失)",
                "10020" => "接口维护",
                "10021" => "接口停用"
            );
            //状态非0，说明失败
            $return['status'] = 0;
            $return['msg'] = "错误提示:" . $res['error_code'] . "-" . $error_code[$res['error_code']];
        }
        return $return;
    }
}

/**
 * 字符串加密
 */
function encrypt($str = "")
{
    $hex = '';
    for ($i = 0, $length = mb_strlen($str); $i < $length; $i++) {
        $hex .= dechex(ord($str{$i}));
    }
    return $hex;
}

/**
 * 字符串加密
 */
function decrypt($hex = "")
{
    $str = '';
    $arr = str_split($hex, 2);
    foreach ($arr as $bit) {
        $str .= chr(hexdec($bit));
    }
    return $str;
}

/**
 * 简单校验时间
 */
function chk_time($datetime = "")
{
    if (preg_match('/^([12]\d\d\d)-(0?[1-9]|1[0-2])-(0?[1-9]|[12]\d|3[0-1]) ([0-1]\d|2[0-4]):([0-5]\d)(:[0-5]\d)?$/', $datetime)) {
        echo true;
    } else {
        echo false;
    }
}

/**
 * 获取全局唯一标识符
 * @param bool $trim
 * @return string
 */
function getGuidV4($trim = true)
{
    // Windows
    if (function_exists('com_create_guid') === true) {
        $charid = com_create_guid();
        return $trim == true ? trim($charid, '{}') : $charid;
    }
    // OSX/Linux
    if (function_exists('openssl_random_pseudo_bytes') === true) {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    // Fallback (PHP 4.2+)
    mt_srand((double)microtime() * 10000);
    $charid = strtolower(md5(uniqid(rand(), true)));
    $hyphen = chr(45);                  // "-"
    $lbrace = $trim ? "" : chr(123);    // "{"
    $rbrace = $trim ? "" : chr(125);    // "}"
    $guidv4 = $lbrace .
        substr($charid, 0, 8) . $hyphen .
        substr($charid, 8, 4) . $hyphen .
        substr($charid, 12, 4) . $hyphen .
        substr($charid, 16, 4) . $hyphen .
        substr($charid, 20, 12) .
        $rbrace;
    return $guidv4;
}

/**
 * 是否为一个合法的url
 * @param string $url
 * @return boolean
 */
function is_url($url)
{
    if (filter_var($url, FILTER_VALIDATE_URL)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 简单转换路径
 * @param $img
 * @return string
 */
function img_to_http($img, $url)
{
    return $img && !is_url($img) ? $url . $img : $img;
}

/**
 * 字符串图片转换数组
 * @param $img
 * @param $imgurl
 * @param int $type
 * @return array
 */
function string_img_to_http($img, $imgurl, $type = 1)
{
    if ($type) $img = explode(',', $img);
    return array_map(function ($v) use ($imgurl) {
        return (img_to_http($v, $imgurl));
    }, $img);
}

/**
 * 写入日志
 * @param string|array $values
 * @param string $dir
 * @return bool|int
 */
function write_log($values, $dir)
{
    if (is_array($values))
        $values = print_r($values, true);
    // 日志内容
    $content = '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $values . PHP_EOL . PHP_EOL;
    try {
        // 文件路径
        $filePath = $dir . '/logs/';
        // 路径不存在则创建
        !is_dir($filePath) && mkdir($filePath, 0755, true);
        // 写入文件
        return file_put_contents($filePath . date('Ymd') . '.log', $content, FILE_APPEND);
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * INSERT SQL表达式
 * @param $data
 * @param $table
 * @return string|string[]
 */
function data_to_build_insert_sql($data, $table)
{
    $insertSql = '%INSERT% INTO %TABLE% (%FIELD%) VALUES (%DATA%)';
    $fields = array_keys($data);
    $values = array_values($data);
    return str_replace(
        ['%INSERT%', '%TABLE%', '%FIELD%', '%DATA%'],
        [
            'INSERT', $table,
            "`" . implode("`,`", $fields) . "`",
            "'" . implode("','", $values) . "'"
        ],
        $insertSql);
}

/**
 * SELECT SQL表达式
 * @param $table
 * @param $field
 * @param $where
 * @param string $group
 * @param string $limit
 * @return string|string[]
 */
function data_to_build_select_sql($table, $field, $where, $group = '', $limit = '')
{
    //return "SELECT $field FROM $table WHERE $where $group $limit";
    $selectSql = 'SELECT %FIELD% FROM %TABLE% where %WHERE% %GROUP% %LIMIT%';
    return str_replace(['%TABLE%', '%FIELD%', '%WHERE%', '%GROUP%', '%LIMIT%'], [$table, $field, $where, $group, $limit], $selectSql);
}

/**
 * UPDATE SQL表达式
 * @param $data
 * @param $table
 * @param $where
 * @return string|string[]
 */
function data_to_build_update_sql($data, $table, $where)
{
    $set = [];
    foreach ($data as $key => $val) {
        $set[] = '`' . $key . '` = "' . $val . '"';
    }
    $updateSql = '%UPDATE%  %TABLE% SET %DATA% WHERE %WHERE%';
    return str_replace(
        ['%UPDATE%', '%TABLE%', '%DATA%', '%WHERE%'],
        ['UPDATE', $table, implode(' , ', $set), $where],
        $updateSql);
}