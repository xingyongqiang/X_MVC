<?php

/**
 * swagger: api User
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends Base
{
    /**
     * get: 初始化redis地区
     * path: get_region_into_redis
     * method: get_region_into_redis
     */
    public function get_region_into_redis()
    {
        $redis = new RedisCustom($this->_redis_config);
        $sql = "select id,`name` from xyq_region";
        $list = $this->db->query($sql)->result_array();
        foreach ($list as $k => $v) {
            $redis->del('xyq_region_' . $v['id']);
            $redis->set('xyq_region_' . $v['id'], $v['name']);
        }
        ajax_return(1, '缓存成功');
    }

    /**
     * get: 用户登录
     * path: login
     * method: login
     * param:code - {string} code码
     * param:user_info - {array} 用户详情
     */
    public function login()
    {
        $code = isset($_POST["code"]) ? sql_format($_POST["code"]) : "";
        $user_info = isset($_POST["user_info"]) ? $_POST["user_info"] : "";
        $encrypted_data = isset($_POST["encrypted_data"]) ? $_POST["encrypted_data"] : "";
        $iv = isset($_POST["iv"]) ? $_POST["iv"] : "";
        $signature = isset($_POST["signature"]) ? $_POST["signature"] : "";

        $session = $this->wxlogin($code);
        $userInfo = json_decode(htmlspecialchars_decode($user_info), true);
        $user_id = $this->register($session['openid'], $userInfo);
        $this->token = $this->token($session['openid']);

        //登录信息入session()
        $cache = new Fcache();
        $cache->set($this->token, $session, 86400 * 7);

        $data['user_id'] = $user_id;
        $data['token'] = $this->token;

        ajax_return(1, '登录成功', $data);
    }

    /**
     * get: 获取用户信息
     * path: info
     * method: info
     * param:token - {string} 116ac6e5f58fcbec37e50fff6cc9a982
     */
    public function info()
    {
        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        ajax_return(1, '', self::get_user_info($token));
    }

    /**
     * get: 收货地址列表
     * path: address_lists
     * method: address_lists
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     */
    public function address_lists()
    {
        $redis = new RedisCustom($this->_redis_config);
        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        $user = self::get_user_info($token);

        $sql = "select address_id,`name`,phone,province_id,city_id,region_id,detail,create_time from xyq_user_address where user_id = " . $user['user_id'];
        $list = $this->db->query($sql)->result_array();

        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['province_id'] = $redis->get('xyq_region_' . $v['province_id']);
                $list[$k]['city_id'] = $redis->get('xyq_region_' . $v['city_id']);
                $list[$k]['region_id'] = $redis->get('xyq_region_' . $v['region_id']);
            }
        }

        $data['list'] = $list;
        $data['default_id'] = $user['address_id'];

        ajax_return(1, '', $data);
    }

    /**
     * post: 添加收货地址
     * path: address_add
     * method: address_add
     * param: wxapp_id - {int} 10001
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: name - {string} 姓名
     * param: phone - {string} 联系电话
     * param: region - {string} 省,市,区-字符串
     * param: detail - {string} 详细地址
     */
    public function address_add()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $wxapp_id = isset($_POST["wxapp_id"]) ? $_POST["wxapp_id"] : "10001";
        $name = isset($_POST["name"]) ? $_POST["name"] : "";
        $phone = isset($_POST["phone"]) ? $_POST["phone"] : "";
        $region = isset($_POST["region"]) ? $_POST["region"] : "";
        $region_array = explode(',', $region);
        $detail = isset($_POST["detail"]) ? $_POST["detail"] : "";

        $user = self::get_user_info($token);
        $user_id = $user['user_id'];

        $province_id = $this->getIdByName($region_array[0], 1, 0);
        $city_id = $this->getIdByName($region_array[1], 2, $province_id);
        $region_id = $this->getIdByName($region_array[2], 3, $city_id);

        $sql = "insert into `xyq_user_address` (`user_id`,`name`,phone,province_id,city_id,region_id,detail,wxapp_id) values ('$user_id','$name','$phone','$province_id','$city_id','$region_id','$detail','$wxapp_id')";
        $this->db->query($sql);
        $res = $this->db->insert_id();
        if ($res) {
            ajax_return(1, '添加成功');
        }
        ajax_return(0, '添加失败');
    }

    /**
     * post: 添加收货地址
     * path: address_delete
     * method: address_delete
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: address_id - {int} 收货地址
     */
    public function address_delete()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $address_id = isset($_POST["address_id"]) ? $_POST["address_id"] : "";
        $user = self::get_user_info($token);
        $user_id = $user['user_id'];

        $sql = "delete from `xyq_user_address` where address_id = '$address_id' and user_id = '$user_id'";
        $this->db->query($sql);
        ajax_return(1, '删除成功');
    }

    /**
     * post: 默认收货地址
     * path: address_default
     * method: address_default
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: address_id - {int} 收货地址
     */
    public function address_default()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $address_id = isset($_POST["address_id"]) ? $_POST["address_id"] : "";
        $user = self::get_user_info($token);
        $user_id = $user['user_id'];

        $sql = "update `xyq_user` set `address_id`='$address_id' where user_id = '$user_id'";
        $this->db->query($sql);

        ajax_return(1, '设置成功');
    }

    /**
     * get: 收货地址详情
     * path: address_detail
     * method: address_detail
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: address_id - {int} 收货地址
     */
    public function address_detail()
    {
        $redis = new RedisCustom($this->_redis_config);
        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        $address_id = isset($_GET["address_id"]) ? $_GET["address_id"] : "";
        $user = self::get_user_info($token);
        $user_id = $user['user_id'];

        $sql = "select address_id,`name`,phone,province_id,city_id,region_id,detail,create_time from xyq_user_address where user_id = '$user_id' and address_id = '$address_id'";
        $info = $this->db->query($sql)->row_array();

        if ($info) {
            $province = $redis->get('xyq_region_' . $info['province_id']);
            $city = $redis->get('xyq_region_' . $info['city_id']);
            $region = $redis->get('xyq_region_' . $info['region_id']);
            $data['region'] = $province . ',' . $city . ',' . $region;
        }
        $data['detail'] = $info;

        ajax_return(1, '', $data);
    }

    /**
     * post: 编辑地址详情
     * path: address_edit
     * method: address_edit
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: address_id - {int} 收货地址
     */
    public function address_edit()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $wxapp_id = isset($_POST["wxapp_id"]) ? $_POST["wxapp_id"] : "10001";
        $name = isset($_POST["name"]) ? $_POST["name"] : "";
        $phone = isset($_POST["phone"]) ? $_POST["phone"] : "";
        $region = isset($_POST["region"]) ? $_POST["region"] : "";
        $region_array = explode(',', $region);
        $detail = isset($_POST["detail"]) ? $_POST["detail"] : "";
        $address_id = isset($_POST["address_id"]) ? $_POST["address_id"] : "";

        $user = self::get_user_info($token);
        $user_id = $user['user_id'];

        $province_id = $this->getIdByName($region_array[0], 1, 0);
        $city_id = $this->getIdByName($region_array[1], 2, $province_id);
        $region_id = $this->getIdByName($region_array[2], 3, $city_id);

        $sql = "update `xyq_user_address` set `name`='$name',`phone`='$phone',`detail`='$detail',`region_id`='$region_id',`province_id`='$province_id',`city_id`='$city_id' where address_id = '$address_id' and user_id = '$user_id'";
        $this->db->query($sql);
        ajax_return(1, '编辑成功');
    }

    /**
     * post: 我的购物车
     * path: cart_lists
     * method: cart_lists
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     */
    public function cart_lists()
    {
        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        $user = self::get_user_info($token);

        $sql = "select cart_id,`goods_id`,goods_name,goods_img,num,market_price,shop_price,spec,spec_name from `xyq_user_cart` where status = 0 and user_id = " . $user['user_id'];
        $list = $this->db->query($sql)->result_array();

        $order_total_num = 0;
        $order_total_price = 0;
        foreach ($list as $k => $v) {
            $list[$k]['goods_img'] = img_to_http($v['goods_img'], $this->_base_url);
            $order_total_num += $v['num'];
            $order_total_price += $v['num'] * $v['shop_price'];
        }

        $data['goods_list'] = $list;
        $data['order_total_num'] = $order_total_num;
        $data['order_total_price'] = $order_total_price;

        ajax_return(1, '', $data);
    }

    /**
     * post: 加入购物车
     * path: cart_add
     * method: cart_add
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: goods_id - {string} 商品id
     * param: goods_num - {string} 数量
     * param: goods_sku_id - {string} 规格id
     */
    public function cart_add()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $goods_id = isset($_POST["goods_id"]) ? $_POST["goods_id"] : 0;
        $goods_num = isset($_POST["goods_num"]) ? $_POST["goods_num"] : 0;
        $spec_id = isset($_POST["goods_sku_id"]) ? $_POST["goods_sku_id"] : 0;
        $cart_total_num = isset($_POST["cart_total_num"]) ? $_POST["cart_total_num"] : 0;

        if (!$goods_num) ajax_return(0, '商品数量不允许为空');

        $sql = "select id,`name`,shop_id,img,`name` from `xyq_goods` where is_sale = 0 and is_del = 0 and id = " . $goods_id;
        $goods = $this->db->query($sql)->row_array();
        if (!$goods) ajax_return(0, '很抱歉，该商品已下架');

        $sql = "select id,spec_num,market_price,shop_price,spec_name from `xyq_goods_spec` where id = " . $spec_id . " and goods_id = " . $goods_id;
        $spec = $this->db->query($sql)->row_array();
        if (!$spec) ajax_return(0, '很抱歉，该商品已下架');
        if ($spec['spec_num'] < $goods_num) ajax_return(0, '很抱歉，商品库存不足');

        $user = self::get_user_info($token);
        $uid = $user['user_id'];
        $market_price = $spec['market_price'];
        $shop_price = $spec['shop_price'];
        $spec_name = $spec['spec_name'];
        $shop_id = $goods['shop_id'];
        $goods_name = $goods['name'];
        $goods_img = $goods['img'];

        $sql = "select cart_id,`num` from `xyq_user_cart` where status = 0 and goods_id = " . $goods_id . " and user_id = " . $uid . " and spec = " . $spec_id;
        $cart = $this->db->query($sql)->row_array();

        if ($cart) {
            $old_num = $goods_num + $cart['num'];
            if ($spec['spec_num'] < $old_num) ajax_return(0, '很抱歉，商品库存不足');
            $sql = "update `xyq_user_cart` set num = '$old_num',market_price = '$market_price',shop_price = '$shop_price',spec_name = '$spec_name' where status = 0 and goods_id = " . $goods_id . " and user_id = " . $uid . " and spec = " . $spec_id . " and cart_id = " . $cart['cart_id'];
            $this->db->query($sql);
            $data['cart_total_num'] = $cart_total_num + $goods_num;
            ajax_return(1, '购物车更新成功', $data);
        } else {
            $sql = "insert into `xyq_user_cart` (`user_id`,`shop_id`,goods_id,goods_name,goods_img,num,market_price,shop_price,spec,spec_name) values ('$uid','$shop_id','$goods_id','$goods_name','$goods_img','$goods_num','$market_price','$shop_price','$spec_id','$spec_name')";
            $this->db->query($sql);
            $res = $this->db->insert_id();
            if ($res) {
                $data['cart_total_num'] = $cart_total_num + $goods_num;
                ajax_return(1, '添加成功', $data);
            }
            ajax_return(0, '添加失败');
        }
    }

    /**
     * post: 购物车减一
     * path: cart_add
     * method: cart_add
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: goods_id - {string} 商品id
     * param: goods_sku_id - {string} 规格id
     */
    public function cart_sub()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $goods_id = isset($_POST["goods_id"]) ? $_POST["goods_id"] : 0;
        $spec_id = isset($_POST["goods_sku_id"]) ? $_POST["goods_sku_id"] : 0;
        $uid = self::get_user_info($token)['user_id'];

        $sql = "select id,`name`,shop_id,img,`name` from `xyq_goods` where is_sale = 0 and is_del = 0 and id = " . $goods_id;
        $goods = $this->db->query($sql)->row_array();
        if (!$goods) ajax_return(0, '很抱歉，该商品已下架');

        $sql = "select id,spec_num,market_price,shop_price,spec_name from `xyq_goods_spec` where id = " . $spec_id . " and goods_id = " . $goods_id;
        $spec = $this->db->query($sql)->row_array();
        if (!$spec) ajax_return(0, '很抱歉，该商品已下架');

        $sql = "select cart_id,`num` from `xyq_user_cart` where status = 0 and goods_id = " . $goods_id . " and user_id = " . $uid . " and spec = " . $spec_id;
        $cart = $this->db->query($sql)->row_array();
        if (!$cart) ajax_return(0, '购物车信息有误');
        if ($cart['num'] - 1 == 0) ajax_return(0, '商品数量不能再少了');

        $sql = "update xyq_user_cart set num = num - 1 where cart_id = " . $cart['cart_id'] . " and goods_id = " . $goods_id . " and user_id = " . $uid . " and spec = " . $spec_id;
        $this->db->query($sql);

        ajax_return(1, '更新成功');
    }

    /**
     * post: 删除购物车
     * path: cart_delete
     * method: cart_delete
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: goods_id - {string} 商品id
     * param: goods_sku_id - {string} 规格id
     */
    public function cart_delete()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $goods_id = isset($_POST["goods_id"]) ? $_POST["goods_id"] : 0;
        $spec_id = isset($_POST["goods_sku_id"]) ? $_POST["goods_sku_id"] : 0;
        $uid = self::get_user_info($token)['user_id'];

        $sql = "delete from `xyq_user_cart` where goods_id = " . $goods_id . " and user_id = " . $uid . " and spec = " . $spec_id;
        $this->db->query($sql);

        ajax_return(1, '删除成功');
    }

    /**
     * post: 立即购买--我的购物车信息
     * path: buy_cart_lists
     * method: buy_cart_lists
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     */
    public function buy_cart_lists()
    {
        $redis = new RedisCustom($this->_redis_config);
        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        $user = self::get_user_info($token);
        $address = $user['address_id'];

        //检查默认地址
        $sql = "select address_id,`name`,phone,province_id,city_id,region_id,detail,create_time from xyq_user_address where user_id = " . $user['user_id'] . " and address_id = " . $address;
        $info = $this->db->query($sql)->row_array();
        if ($info) {
            $info['province'] = $redis->get('xyq_region_' . $info['province_id']);
            $info['city'] = $redis->get('xyq_region_' . $info['city_id']);
            $info['region'] = $redis->get('xyq_region_' . $info['region_id']);
        }

        $sql = "select cart_id,`goods_id`,goods_name,goods_img,num,market_price,shop_price,spec,spec_name from `xyq_user_cart` where status = 0 and user_id = " . $user['user_id'];
        $list = $this->db->query($sql)->result_array();

        $order_total_num = 0;
        $order_total_price = 0;
        foreach ($list as $k => $v) {
            $list[$k]['goods_img'] = img_to_http($v['goods_img'], $this->_base_url);
            $order_total_num += $v['num'];
            $order_total_price += $v['num'] * $v['shop_price'];
        }

        //计算运费
        $express_price = $this->get_express_area_info($info, 0, 14);

        $data['address'] = $info;
        $data['goods_list'] = $list;
        $data['order_total_num'] = $order_total_num;
        $data['order_total_price'] = $order_total_price;
        $data['exist_address'] = isset($address) ? $address : false;
        $data['hasError'] = false;
        $data['has_error'] = '';
        $data['intra_region'] = true;
        $data['express_price'] = $express_price;
        $data['order_pay_price'] = $express_price + $order_total_price;

        ajax_return(1, '', $data);
    }

    /**
     * post: 立即购买--我的购物车--立即下单
     * path: cart_order_into
     * method: cart_order_into
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     */
    public function cart_order_into()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $user = self::get_user_info($token);
        $uid = $user['user_id'];
        $address = $user['address_id'];

        $sql = "select address_id,`name`,phone,province_id,city_id,region_id,detail,create_time from xyq_user_address where user_id = " . $user['user_id'] . " and address_id = " . $address;
        $info = $this->db->query($sql)->row_array();
        if (!$info) ajax_return(0, '请先设置默认地址');

        $sql = "select cart_id,`goods_id`,goods_name,goods_img,num,market_price,shop_price,spec,spec_name from `xyq_user_cart` where status = 0 and user_id = " . $user['user_id'];
        $list = $this->db->query($sql)->result_array();

        $order_total_price = 0;
        foreach ($list as $k => $v) {
            $order_total_price += $v['num'] * $v['shop_price'];
        }

        //计算运费
        $express_price = $this->get_express_area_info($info, 0, 14);
        $money = $order_total_price + $express_price;
        $order_sn = $uid . rand_code(12);
        $order_sa = rand_code(15);

        $order['order_sn'] = $order_sn;
        $order['order_sa'] = $order_sa;
        $order['user_id'] = $uid;
        $order['shop_id'] = 0;
        $order['consignee'] = $info['name'];
        $order['province'] = $info['province_id'];
        $order['city'] = $info['city_id'];
        $order['district'] = $info['region_id'];
        $order['address'] = $info['detail'];
        $order['phone'] = $info['phone'];
        $order['info'] = '';
        $order['goods_amount'] = $order_total_price;
        $order['express_free'] = $express_price;
        $order['money_paid'] = $money;
        $order['add_time'] = time();

        $sql = "update xyq_user_cart set status = 1,`order` = '$order_sn' where status = 0 and user_id = " . $user['user_id'];
        $this->db->query($sql);

        $sql = data_to_build_insert_sql($order, 'xyq_user_order');
        $this->db->query($sql);
        $user_order_id = $this->db->insert_id();
        //$user_order_id = 1;

        $data['order_id'] = $user_order_id;
        $data['payment'] = $this->wxPay($order_sa, $user['open_id'], $money);

        //$miniorder = ['out_trade_no' => $user_order_id, 'total_fee' => $money, 'body' => '小程序支付', 'openid' => $user['open_id']];
        //$data['payment'] = \Yansongda\Pay\Pay::wechat($this->wxConfig)->miniapp($miniorder)->toJson();
        //$data['payment'] = json_decode($payment);

        ajax_return(1, '', $data);
    }

    /**
     * post: 立即购买--单个商品信息
     * path: goods_buy_now
     * method: goods_buy_now
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: goods_id - {string} 商品id
     * param: goods_num - {string} 数量
     * param: goods_sku_id - {string} 规格id
     */
    public function goods_buy_now()
    {
        $redis = new RedisCustom($this->_redis_config);
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $goods_id = isset($_POST["goods_id"]) ? $_POST["goods_id"] : 0;
        $goods_num = isset($_POST["goods_num"]) ? $_POST["goods_num"] : 0;
        $spec_id = isset($_POST["goods_sku_id"]) ? $_POST["goods_sku_id"] : 0;
        $user = self::get_user_info($token);
        $address = $user['address_id'];

        if (!$goods_num) $this->setError('商品数量不允许为空');

        //检查默认地址
        $sql = "select address_id,`name`,phone,province_id,city_id,region_id,detail,create_time from xyq_user_address where user_id = " . $user['user_id'] . " and address_id = " . $address;
        $info = $this->db->query($sql)->row_array();
        if ($info) {
            $info['province'] = $redis->get('xyq_region_' . $info['province_id']);
            $info['city'] = $redis->get('xyq_region_' . $info['city_id']);
            $info['region'] = $redis->get('xyq_region_' . $info['region_id']);
        }

        $sql = "select id,`name`,shop_id,img,`name` from `xyq_goods` where is_sale = 0 and is_del = 0 and id = " . $goods_id;
        $goods = $this->db->query($sql)->row_array();
        if (!$goods) $this->setError('很抱歉，该商品已下架');

        $sql = "select id,spec_num,market_price,shop_price,spec_name from `xyq_goods_spec` where id = " . $spec_id . " and goods_id = " . $goods_id;
        $spec = $this->db->query($sql)->row_array();
        if (!$spec) $this->setError('很抱歉，该商品已下架');
        if ($spec['spec_num'] < $goods_num) $this->setError('很抱歉，商品库存不足');

        $goods['num'] = $goods_num;
        $goods['goods_img'] = img_to_http($goods['img'], $this->_base_url);
        $goods['market_price'] = $spec['market_price'];
        $goods['shop_price'] = $spec['shop_price'];
        $goods['spec'] = $spec_id;
        $goods['spec_name'] = $spec['spec_name'];
        $goods['goods_name'] = $goods['name'];
        $order_total_price = $goods_num * $spec['shop_price'];

        //计算运费
        $express_price = $this->get_express_area_info($info, 0, 14);

        $data['address'] = $info;
        $data['goods_list'][0] = $goods;
        $data['order_total_num'] = $goods_num;
        $data['order_total_price'] = $order_total_price;
        $data['exist_address'] = isset($address) ? $address : false;
        $data['error_msg'] = $this->error;
        $data['has_error'] = $this->hasError();
        $data['intra_region'] = true;
        $data['express_price'] = $express_price;
        $data['order_pay_price'] = $express_price + $order_total_price;

        ajax_return(1, '获取商品成功', $data);
    }

    /**
     * post: 立即购买--单个商品--立即下单
     * path: order_buy_now
     * method: order_buy_now
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: goods_id - {string} 商品id
     * param: goods_num - {string} 数量
     * param: goods_sku_id - {string} 规格id
     */
    public function order_buy_now()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $goods_id = isset($_POST["goods_id"]) ? $_POST["goods_id"] : 0;
        $goods_num = isset($_POST["goods_num"]) ? $_POST["goods_num"] : 0;
        $spec_id = isset($_POST["goods_sku_id"]) ? $_POST["goods_sku_id"] : 0;
        $user = self::get_user_info($token);

        $uid = $user['user_id'];
        $address = $user['address_id'];

        if (!$goods_num) ajax_return(-10, '商品数量不允许为空');

        //检查默认地址
        $sql = "select address_id,`name`,phone,province_id,city_id,region_id,detail,create_time from xyq_user_address where user_id = " . $user['user_id'] . " and address_id = " . $address;
        $info = $this->db->query($sql)->row_array();
        if (!$info) ajax_return(-10, '请先设置默认地址');

        $sql = "select id,`name`,shop_id,img,`name` from `xyq_goods` where is_sale = 0 and is_del = 0 and id = " . $goods_id;
        $goods = $this->db->query($sql)->row_array();
        if (!$goods) ajax_return(-10, '很抱歉，该商品已下架');

        $sql = "select id,spec_num,market_price,shop_price,spec_name from `xyq_goods_spec` where id = " . $spec_id . " and goods_id = " . $goods_id;
        $spec = $this->db->query($sql)->row_array();
        if (!$spec) ajax_return(-10, '很抱歉，该商品已下架');
        if ($spec['spec_num'] < $goods_num) ajax_return(-10, '很抱歉，商品库存不足');

        //计算运费
        //$express_price = $this->get_express_area_info($info, 0, 14);
        $express_price = 0;
        $order_total_price = $goods_num * $spec['shop_price'];

        $money = $order_total_price + $express_price;
        $order_sn = $uid . rand_code(12);
        $order_sa = rand_code(15);

        $cart['user_id'] = $uid;
        $cart['shop_id'] = $goods['shop_id'];
        $cart['goods_id'] = $goods_id;
        $cart['goods_name'] = $goods['name'];
        $cart['goods_img'] = $goods['img'];
        $cart['num'] = $goods_num;
        $cart['market_price'] = $spec['market_price'];
        $cart['shop_price'] = $spec['shop_price'];
        $cart['spec'] = $spec_id;
        $cart['spec_name'] = $spec['spec_name'];
        $cart['order'] = $order_sn;
        $cart['status'] = 1;

        $sql = data_to_build_insert_sql($cart, 'xyq_user_cart');
        $this->db->query($sql);

        $order['order_sn'] = $order_sn;
        $order['order_sa'] = $order_sa;
        $order['user_id'] = $uid;
        $order['shop_id'] = $goods['shop_id'];
        $order['consignee'] = $info['name'];
        $order['province'] = $info['province_id'];
        $order['city'] = $info['city_id'];
        $order['district'] = $info['region_id'];
        $order['address'] = $info['detail'];
        $order['phone'] = $info['phone'];
        $order['info'] = '';
        $order['goods_amount'] = $order_total_price;
        $order['express_free'] = $express_price;
        $order['money_paid'] = $money;
        $order['add_time'] = time();

        $sql = data_to_build_insert_sql($order, 'xyq_user_order');
        $this->db->query($sql);;
        $user_order_id = $this->db->insert_id();

        $data['order_id'] = $user_order_id;
        $data['payment'] = $this->wxPay($order_sa, $user['open_id'], $money);

        //$miniorder = ['out_trade_no' => $user_order_id, 'total_fee' => $money, 'body' => '小程序支付', 'openid' => $user['open_id']];
        //$data['payment'] = \Yansongda\Pay\Pay::wechat($this->wxConfig)->miniapp($miniorder);

        ajax_return(1, '', $data);
    }

    /**
     * get: 我的订单
     * path: order_lists
     * method: order_lists
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: dataType - {string} payment-待付款/all-全部
     */
    public function order_lists()
    {
        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        $dataType = isset($_GET["dataType"]) ? $_GET["dataType"] : '';

        $user = self::get_user_info($token);
        $uid = $user['user_id'];

        $where = "status = 0 and user_id = $uid";
        if ($dataType == 'payment') $where .= " and order_status = 1 and pay_status = 1";
        if ($dataType == 'delivery') $where .= " and order_status = 2 and pay_status = 2 and shipping_status = 1";
        if ($dataType == 'received') $where .= " and order_status = 2 and pay_status = 2 and shipping_status = 2";

        $sql = data_to_build_select_sql('xyq_user_order', 'id,order_sn,order_sa,order_status,shipping_status,pay_status,is_comment,money_paid,add_time', $where);
        $list = $this->db->query($sql)->result_array();

        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['num'] = 0;
                $tmp_where = " `order` = " . $v['order_sn'] . " and user_id = $uid";
                $tmp_sql = data_to_build_select_sql('xyq_user_cart', 'cart_id,goods_id,goods_name,goods_img,num,shop_price,spec_name', $tmp_where);
                $goods = $this->db->query($tmp_sql)->result_array();
                foreach ($goods as $key => $val) {
                    $list[$k]['num'] += $val['num'];
                    $goods[$key]['goods_img'] = img_to_http($val['goods_img'], $this->_base_url);
                }
                $list[$k]['goods'] = $goods;
                $list[$k]['order_status'] = $this->get_order_status_num($v['order_status'], $v['pay_status'], $v['shipping_status'], $v['is_comment']);
                $list[$k]['add_time'] = date('Y-m-d H:i', $v['add_time']);
            }
        }

        $data['list'] = $list;

        ajax_return(1, '', $data);
    }

    /**
     * post: 订单重新付款
     * path: order_pay
     * method: order_pay
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: order_id - {int} 订单id
     */
    public function order_pay()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $order_id = isset($_POST["order_id"]) ? $_POST["order_id"] : "";
        $user = self::get_user_info($token);
        $uid = $user['user_id'];

        $where = "status = 0 and user_id = $uid and order_status = 1 and pay_status = 1 and id = $order_id";
        $sql = data_to_build_select_sql('xyq_user_order', 'id,order_sa,money_paid', $where);
        $info = $this->db->query($sql)->row_array();
        if (!$info) ajax_return(-10, '订单信息有误，请稍后重试');

        $order = rand_code(15);
        $sql = "update xyq_user_order set order_sa = '$order' where " . $where;
        $this->db->query($sql);

        $data['order_id'] = $order_id;
        $data['payment'] = $this->wxPay($order, $user['open_id'], $info['money_paid']);

        ajax_return(1, '', $data);
    }

    /**
     * post: 订单取消
     * path: order_cancel
     * method: order_cancel
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: order_id - {int} 订单id
     */
    public function order_cancel()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $order_id = isset($_POST["order_id"]) ? $_POST["order_id"] : "";
        $user = self::get_user_info($token);
        $uid = $user['user_id'];

        //取消涉及到退款

    }

    /**
     * post: 订单确认收货
     * path: order_cancel
     * method: order_cancel
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: order_id - {int} 订单id
     */
    public function order_receipt()
    {
        $token = isset($_POST["token"]) ? $_POST["token"] : "";
        $order_id = isset($_POST["order_id"]) ? $_POST["order_id"] : "";
        $user = self::get_user_info($token);
        $uid = $user['user_id'];

        $sql = "update user_order set ok_time = time(),shipping_status = 3,order_status = 5 where id = $order_id and user_id = $uid";
        $this->db->query($sql);

        ajax_return(1, '订单收货成功');
    }

    /**
     * get: 订单详情
     * path: order_detail
     * method: order_detail
     * param: token - {string} e1044e21d5119c4647600789cfbd1663
     * param: order_id - {int} 订单id
     */
    public function order_detail()
    {
        $redis = new RedisCustom($this->_redis_config);
        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        $order_id = isset($_GET["order_id"]) ? $_GET["order_id"] : "";
        $user = self::get_user_info($token);
        $uid = $user['user_id'];

        $where = "status = 0 and user_id = $uid and id = $order_id";
        $sql = data_to_build_select_sql('xyq_user_order', '*', $where);
        $info = $this->db->query($sql)->row_array();
        if (!$info) ajax_return(0, '订单信息有误，请稍后重试');

        $info['order_status'] = $this->get_order_status_num($info['order_status'], $info['pay_status'], $info['shipping_status'], $info['is_comment']);
        $info['add_time'] = date('Y-m-d H:i', $info['add_time']);
        $info['num'] = 0;

        $tmp_where = " `order` = " . $info['order_sn'] . " and user_id = $uid";
        $tmp_sql = data_to_build_select_sql('xyq_user_cart', 'cart_id,goods_id,goods_name,goods_img,num,shop_price,spec_name', $tmp_where);
        $goods = $this->db->query($tmp_sql)->result_array();

        foreach ($goods as $key => $val) {
            $info['num'] += $val['num'];
            $goods[$key]['goods_img'] = img_to_http($val['goods_img'], $this->_base_url);
        }

        $info['province'] = $redis->get('xyq_region_' . $info['province']);
        $info['city'] = $redis->get('xyq_region_' . $info['city']);
        $info['district'] = $redis->get('xyq_region_' . $info['district']);

        $data['order'] = $info;
        $data['order']['goods'] = $goods;

        ajax_return(1, '', $data);
    }

    /**
     * 自动注册用户
     * @param $open_id
     * @param $userInfo
     * @return mixed
     * @throws BaseException
     * @throws \think\exception\DbException
     */
    private function register($open_id, $userInfo)
    {
        //检查是否存在
        $sql = "select * from `xyq_user` where open_id = '$open_id'";
        $user = $this->db->query($sql)->row_array();
        if ($user) return $user['user_id'];

        //插入数据
        $nickName = preg_replace('/[\xf0-\xf7].{3}/', '', $userInfo['nickName']);
        $avatarUrl = $userInfo['avatarUrl'];
        $gender = $userInfo['gender'];
        $country = $userInfo['country'];
        $province = $userInfo['province'];
        $city = $userInfo['city'];

        $sql = "insert into `xyq_user` (`open_id`,`nickName`,avatarUrl,gender,country,province,city,wxapp_id) values ('$open_id','$nickName','$avatarUrl',$gender,'$country','$province','$city','$this->appId')";
        $this->db->query($sql);
        return $this->db->insert_id();
    }


    /**
     * 获取用户信息
     * @param $where
     * @return null|static
     * @throws \think\exception\DbException
     */
    private function detail($token)
    {
        $cache = new Fcache();
        $openId = $cache->get($token)['openid'];
        if (!$token) {
            ajax_return(-1, '缺少必要的参数：token');
        }
        $sql = "select user_id,nickName,avatarUrl,address_id from `xyq_user` where open_id = '$openId'";
        $user = $this->db->query($sql)->row_array();
        if (!$user) {
            ajax_return(-1, '没有找到用户信息');
        }
        return $user;
    }

}