<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Base extends Controller
{
    protected $token;
    protected $appId = 'wx84bd1db46c804a17';
    protected $mchid = '1539043471';//商户号
    protected $appSecret = '462323bf900a1618d6b88a5db46699b9';
    protected $_redis_config = ['host' => '127.0.0.1', 'port' => '6379', 'auth' => 'xing'];
    protected $error = '';

    protected $wxConfig = [
        'miniapp_id' => 'wx84bd1db46c804a17',
        'mch_id' => '1539043471',
        'key' => '462323bf900a1618d6b88a5db46699b9',
        'notify_url' => 'https://xcx.xingyongqiang.com/api/base/notify',
        'cert_client' => '../cert/apiclient_cert.pem', //optional，退款等情况时用到
        'cert_key' => '../cert/apiclient_key.pem',//optional，退款等情况时用到
        'log' => ['file' => '../data/logs/wechat.log', 'level' => 'info', 'type' => 'single', 'max_file' => 30,],
        'http' => ['timeout' => 5.0, 'connect_timeout' => 5.0,],
        'mode' => 'dev'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    //微信支付回调
    public function notify()
    {

    }

    /**
     * 获取用户信息
     * @param $token
     * @return mixed
     */
    protected function get_user_info($token)
    {
        $cache = new Fcache();
        $openId = $cache->get($token)['openid'];
        if (!$token) {
            ajax_return(-1, '缺少必要的参数：token');
        }
        $sql = "select user_id,nickName,avatarUrl,address_id,open_id from `xyq_user` where open_id = '$openId'";
        $user = $this->db->query($sql)->row_array();
        if (!$user) {
            ajax_return(-1, '没有找到用户信息');
        }
        return $user;
    }

    /**
     * 微信登录
     * @param $code
     * @return array|mixed
     * @throws BaseException
     */
    protected function wxlogin($code)
    {
        // 微信登录 (获取session_key)
        $WxUser = new WxUser($this->appId, $this->appSecret);
        if (!$session = $WxUser->sessionKey($code)) {
            ajax_return(0, $WxUser->getError());
        }
        return $session;
    }

    /**
     * 生成用户认证的token
     * @param $openid
     * @return string
     */
    protected function token($openid)
    {
        $wxapp_id = $this->appId;
        // 生成一个不会重复的随机字符串
        $guid = getGuidV4();
        // 当前时间戳 (精确到毫秒)
        $timeStamp = microtime(true);
        // 自定义一个盐
        $salt = 'token_salt';
        return md5("{$wxapp_id}_{$timeStamp}_{$openid}_{$guid}_{$salt}");
    }

    /**
     * 根据名称获取地区id
     * @param $name
     * @param $level
     * @param $pid
     * @return mixed
     */
    protected function getIdByName($name, $level, $pid)
    {
        $sql = "select id from xyq_region where `name` = '$name' and `level` = '$level' and pid = '$pid'";
        return $this->db->query($sql)->row_value('id', 0);
    }

    /**
     * 根据id获取地区名称
     * @param $id
     * @return bool|mixed|string
     */
    protected function getNameById($id)
    {
        $redis = new RedisCustom($this->_redis_config);
        return $redis->get('xyq_region_' . $id);
    }

    /**
     * 设置错误信息
     * @param $error
     */
    protected function setError($error)
    {
        empty($this->error) && $this->error = $error;
    }

    /**
     * 是否存在错误
     * @return bool
     */
    protected function hasError()
    {
        return !empty($this->error);
    }

    /**
     * 构建微信支付
     * @param $order_no
     * @param $open_id
     * @param $pay_price
     * @return array
     * @throws BaseException
     */
    protected function wxPay($order_no, $open_id, $pay_price)
    {
        $wx_Config['app_id'] = $this->appId;
        $wx_Config['mchid'] = $this->mchid;
        $wx_Config['apikey'] = $this->appSecret;
        $WxPay = new WxPay($wx_Config);
        return $WxPay->unifiedorder($order_no, $open_id, $pay_price);
    }

    /**
     * 订单列表页--订单状态
     * @param $order_status
     * @param $pay_status
     * @param $shipping_status
     * @param $is_comment
     * @return mixed
     */
    protected function get_order_status_num($order_status, $pay_status, $shipping_status, $is_comment)
    {
        //订单状态
        if ($order_status == 1 && $pay_status == 1) {
            $info['status'] = "待付款";
            $info['btn'] = '立即支付';
            $info['status_num'] = 1;
        } elseif ($order_status == 2 && $pay_status == 2 && $shipping_status == 1) {
            $info['status'] = "待发货";
            $info['btn'] = "取消订单";
            $info['status_num'] = 2;
        } elseif ($order_status == 2 && $pay_status == 2 && $shipping_status == 2) {
            $info['status'] = "待收货";
            $info['btn'] = '确认收货';
            $info['status_num'] = 3;
        } elseif ($order_status == 2 && $pay_status == 2 && $shipping_status == 3) {
            $info['status'] = "待评价";
            $info['btn'] = '去评价';
            $info['status_num'] = 4;
        } elseif ($order_status == 3 && $pay_status == 2) {
            $info['status'] = "退款中";
            $info['btn'] = '部分商品退货';
            $info['status_num'] = 0;
        } elseif ($order_status == 4 && $pay_status == 2) {
            $info['status'] = "退款成功";
            $info['btn'] = '';
            $info['status_num'] = 0;
        } elseif ($order_status == 5 && !$is_comment) {
            $info['status'] = "待评价";
            $info['btn'] = "去评价";
            $info['status_num'] = 4;
        } elseif ($order_status == 5 && $is_comment) {
            $info['status'] = "已评价";
            $info['btn'] = "查看评价";
            $info['status_num'] = 5;
        } else {
            $info['status'] = "交易完成";
            $info['btn'] = "";
            $info['status_num'] = 0;
        }
        return $info;
    }
}