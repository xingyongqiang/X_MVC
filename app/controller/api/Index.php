<?php

/**
 * swagger: api Index
 */
class Index extends Base
{
    /**
     * get: 商品分类
     * path: lists
     * method: lists
     */
    public function lists()
    {
        if (IS_GET) {
            $sql = "select id,`pid`,`name`,icon from `xyq_goods_cate`";
            $sys_classes = $this->db->query($sql)->result_array();

            foreach ($sys_classes as $k => $v) {
                $sys_classes[$k]['icon'] = img_to_http($v['icon'], $this->_base_url);
            }

            $data['list'] = list_to_tree($sys_classes);
            ajax_return(1, '', $data);
        }
    }

    /**
     * get: 根据分类获取商品列表
     * path: detail
     * method: detail
     * param: sortType - {string} 排序
     * param: sortPrice - {string} 排序
     * param: category_id - {int} 分类id
     * param: search - {string} 搜索关键字
     * param: page - {int} 翻页
     */
    public function cate_goods_lists()
    {
        $sortType = isset($_GET['sortType']) ? $_GET['sortType'] : 'all';//all-sales
        $sortPrice = isset($_GET['sortPrice']) ? $_GET['sortPrice'] : 0;
        $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : 0;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $p = isset($_GET['page']) ? $_GET['page'] : 1;

        $p = (int)$p <= 0 ? 1 : (int)$p;
        $size = 10;
        $start = ($p - 1) * $size;

        $where = 'is_del = 0 and is_sale = 0';
        $order = 'id desc';
        if ($category_id) $where .= ' and cate_son_id = ' . $category_id;
        if ($search) $where .= " and name like '%$search%'";
        if ($sortType == 'sales') $order = 'sales desc';
        if ($sortType == 'price') $order = 'rate desc';

        $sql = "select id,`name`,img from `xyq_goods` where " . $where . " order by " . $order . " limit $start,$size";
        $list = $this->db->query($sql)->result_array();

        foreach ($list as $k => $v) {
            $sql = "select shop_price from `xyq_goods_spec` where goods_id = " . $v['id'];
            $list[$k]['shop_price'] = $this->db->query($sql)->row_value('shop_price', 0);
            $list[$k]['img'] = img_to_http($v['img'], $this->_base_url);
        }

        $data['list'] = $list;

        ajax_return(1, '', $data);
    }

    /**
     * get: 商品详情
     * path: detail
     * method: detail
     * param: goods_id - {int} 商品id
     */
    public function detail()
    {
        $url = 'https://xcx.xingyongqiang.com/';
        if (IS_GET) {
            $id = isset($_GET['goods_id']) ? $_GET['goods_id'] : 0;
            $token = isset($_GET["token"]) ? $_GET["token"] : 0;

            $sql = "select id,`name`,shop_id,images,info,sales,`second`, rolex,video,points,review,rate from `xyq_goods` where is_sale = 0 and is_del = 0 and id = " . $id;
            $detail = $this->db->query($sql)->row_array();
            if ($detail['images']) {
                $detail['images'] = string_img_to_http($detail['images'], $url);
            }

            $sql = "select id,spec_name,market_price,shop_price,spec_img,spec_num from `xyq_goods_spec` where goods_id = " . $id;
            $spec = $this->db->query($sql)->result_array();

            foreach ($spec as $k => $v) {
                $spec[$k]['spec_img'] = img_to_http($v['spec_img'], $url);
            }

            $data['detail'] = $detail;
            $data['detail']['spec'] = $spec;
            $data['goods_num'] = 1;//已加入购物车
            $data['stock_num'] = 0;//库存数量
            $data['cart_total_num'] = 0;// 购物车商品总数量

            if (isset($token) && $token !== 0) {
                $uid = self::get_user_info($token)['user_id'];
                $sql = data_to_build_select_sql('xyq_user_cart', 'sum(num) as num', "status = 0 and user_id = $uid");
                $info = $this->db->query($sql)->row_array();
                $data['cart_total_num'] = isset($info['num']) ? $info['num'] : 0;
            }

            ajax_return(1, '', $data);
        }
    }

    /**
     * get: 获取帮助列表
     * path: help
     * method: help
     */
    public function help()
    {
        $sql = "SELECT id,title,content FROM xyq_article where cid = 1 and isdel = 0 order by sorts desc";
        $list = $this->db->query($sql)->result_array();
        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['title'] = msubstr($v['title'], 40, '...', 1);
                $list[$k]['content'] = msubstr($v['content'], 100, '...', 1);
            }
        }
        $data['list'] = $list;
        ajax_return(1, '', $data);
    }
}