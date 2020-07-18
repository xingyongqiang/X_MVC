<?php

use QL\QueryList;
use GuzzleHttp\Client;

/**
 * swagger: api Home
 */
class Home extends Base
{
    /**
     * get: 小程序首页数据
     * path: page
     * method: page
     */
    public function page()
    {
        if (IS_GET) {
            $sql = "select id,`name`,img,url from xyq_adv where status = 1";
            $adv = $this->db->query($sql)->result_array();
            foreach ($adv as $k => $v) {
                $adv[$k]['img'] = $this->_base_url . $v['img'];
            }

            $sql = "select id,`name`,img from xyq_goods where is_del = 0 and is_sale = 0 limit 10";
            $grass = $this->db->query($sql)->result_array();

            foreach ($grass as $k => $v) {
                $sql = "select shop_price from `xyq_goods_spec` where goods_id = " . $v['id'];
                $grass[$k]['shop_price'] = $this->db->query($sql)->row_value('shop_price', 0);
                $grass[$k]['img'] = $this->_base_url . $v['img'];
            }

            $data['items']['adv'] = $adv;
            $data['newest'] = $grass;
            $data['best'] = $grass;

            ajax_return(1, '', $data);
        }
    }

    /**
     * get: 预处理sql语句
     * path: text_sql
     * method: text_sql
     */
    public function text_sql()
    {
//        $sql = "insert into designer(`name`, email,`number`) values (?, ?,?)";
//        $stmt = $this->db->execute($sql);
//        //给占位符号每个?号传值（绑定参数） i  d  s  b，第一个参数为格式化字符，ss代表两个字符串，d代表数字
//        $stmt->bind_param("ssd", $name, $email, $number);
//        //为变量赋值
//        $name = "Mike";
//        $email = "mike@live.cn";
//        $number = 1;
//        //执行
//        $stmt->execute();
//        //为变量赋值
//        $name = "Larry";
//        $email = "larry@live.cn";
//        $number = 2;
//        //执行
//        $stmt->execute();
//        //为变量赋值
//        $name = "Liao";
//        $email = "Liao@live.cn";
//        $number = 3;
//        //执行
//        $stmt->execute();
//        //最后输出
//        echo "最后ID" . $stmt->insert_id . "<br>";
//        echo "影响了" . $stmt->affected_rows . "行<br>";
    }

    /**
     * get: 测试接口
     * path: index
     * method: index
     */
    public function index()
    {
//        $config = ['host' => '127.0.0.1', 'port' => '6379', 'auth' => 'xing'];
//        $redis = new RedisCustom($config);
//        $redis->set('abc', '12');
//        $redis->del('abc');
//        dump($redis->get('abc'));


//        $client = new Client();
//        $res = $client->request('GET', 'https://item.jd.com/100013362104.html');
//        $html = (string)$res->getBody();
//        $data = QueryList::html($html)->find('.sku-name')->text();
//        var_dump($data);

//        $url = 'https://item.taobao.com/item.htm?id=618102598838';
//        $html = iconv('GBK','UTF-8',file_get_contents($url));
//        var_dump(file_get_contents($url));
//        $data = QueryList::html($html)->rules([
//            "text" => [".tb-title","text"]
//        ])->query()->getData();
//        print_r($data);

//        $url = 'https://list.jd.com/list.html?cat=9987,653,655&page=1';
//        $rules = [
//            'title' => ['.p-name>em', 'text'],
//            'link' => ['.p-name>a', 'href'],
//            'img' => ['.p-img>img', 'data-original'],
//            'intro' => ['.promo-words', 'text'],
//            'shop_price' => ['.J_price i', 'text']
//        ];
//        $rt = QueryList::get($url)->rules($rules)->range('.gl-warp')->query()->getData();
//        $data = $rt->all();
//        $client = new Client();
//
//        foreach ($data as $k => $v) {
//            $res = $client->request('GET', $v['link']);
//            $html = (string)$res->getBody();
//            $source = json_decode(QueryList::html($html)->find('#source_baidu a')->texts());
//            $author = json_decode(QueryList::html($html)->find('#author_baidu strong')->texts());
//            $content = json_decode(QueryList::html($html)->find('.post_content')->texts());
//            $content = str_format_filter($content[0]);
//            $title = $v['title'];
//            $img = $v['img'];
//            $intro = $v['intro'];
//            $sql = "insert into xyq_goods (cid,title,author,source,img,intro,content) values (1,'$title','$author[0]','$source[0]','$img','$intro','$content')";
//            //ajax_return(0, $content, $sql);
//            $this->db->query($sql);
//        }
//        ajax_return(0, '', $data);

//        echo 'hello Home!';

        $data['name'] = '光明乳业';
        $data['img'] = '/upload/image/20181022/20181022121856.jpg';
        $data['url'] = 'http://www.360.cn';
        $data['status'] = 1;
        $data['type'] = 1;
        $sql = data_to_build_insert_sql($data, 'xyq_links');
        $this->db->query($sql);
        print_r($sql);
    }

    /**
     * get: 测试接口
     * path: index
     * method: index
     */
    public function cai()
    {
        //猜你喜欢
        //条件->搜索记录->商品标题->价格->优惠(刚需)->分类
    }

    /**
     * get: 测试采集数据
     * path: cai_ji_jd_data
     * method: cai_ji_jd_data
     */
    public function cai_ji_jd_data()
    {
//        $url = 'https://list.jd.com/list.html?cat=653,655&page=1';
//        $rules = [
//            'title' => ['.p-name-type3 .p-name em', 'text'],
//            'link' => ['.p-name>a', 'href'],
//            'img' => ['.p-img>img', 'data-original'],
//            'intro' => ['.promo-words', 'text'],
//            'shop_price' => ['.J_price i', 'text']
//        ];
//        $rt = QueryList::get($url)->rules($rules)->range('.gl-warp')->query()->getData();
//        $data = $rt->all();
//        ajax_return(0, 'ok', $data);
//
//        $sql = "insert into xyq_goods (`name`,shop_price,market_price,img,info) values (?,?,?,?,?)";
//        $stmt = $this->db->execute($sql);
//        $stmt->bind_param("sssss", $name, $shop_price, $market_price, $img, $info);
////        $client = new Client();
//        foreach ($data as $k => $v) {
////            $res = $client->request('GET', $v['link']);
////            $html = (string)$res->getBody();
////            $spec = json_decode(QueryList::html($html)->find('#choose-attr-1 i')->texts());
////            $content = json_decode(QueryList::html($html)->find('.parameter2 ')->texts());
////            $content = str_format_filter($content[0]);
//
//            $name = $v['title'];
//            $shop_price = $v['shop_price'];
//            $market_price = $v['shop_price'];
//            $img = $v['img'];
//            $info = $v['intro'];
//            $stmt->execute();
//        }
//        ajax_return(0, 'ok', $data);
    }

    /**
     * get: 小程序基本数据
     * path: base
     * method: base
     */
    public function base()
    {
        if (IS_GET) {
            $wxapp_id = isset($_GET["wxapp_id"]) ? sql_format($_GET["wxapp_id"]) : "";//微信小程序ID->此处比较
            $token = isset($_GET['token']) ? sql_format($_GET["wxapp_id"]) : "";

            $sql = "select id, `name`, `data` from xyq_setting";
            $sys = $this->db->query($sql)->result_array();
            $sys_config = array_column($sys, "data", "name");

            ajax_return(1, '', $sys_config);
        }
    }
}