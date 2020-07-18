<?php
/**
 * 商品管理
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Goods extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 列表
     */
    public function index($keyword = "")
    {
        //https://xcx.xingyongqiang.com/admin/goods/index/%E5%B0%8F%E5%8F%B0
        //https://xcx.xingyongqiang.com/admin/article/index/99/0/2.html
        $this->assign('keyword', urldecode($keyword));
        $this->display();
    }

    //首页
    public function index_list()
    {
        $keyword = isset($_GET["keyword"]) ? urldecode($_GET["keyword"]) : '';//是否启用
        $page = isset($_GET["page"]) ? $_GET["page"] : 1;//是否启用
        $limit = isset($_GET["limit"]) ? $_GET["limit"] : 10;//是否启用

        $where = "is_del = 0";
        if ($keyword) $where .= " and name like '%$keyword%'";
        //页码
        $page = (int)$page <= 0 ? 1 : (int)$page;
        $start = ($page - 1) * $limit;

        //总数
        $sql = "select count(*) as c from `xyq_goods`";
        $total = $this->db->query($sql)->row_array();
        $total = $total["c"];

        //查询
        $sql = "select id,cate_top_id,`name`,img,sales,is_sale,rate,add_time from `xyq_goods` where $where order by id desc limit $start,$limit";
        $list = $this->db->query($sql)->result_array();

        foreach ($list as $k => $v) {
            $sql = "select `name` from xyq_goods_cate where id = " . $v['cate_top_id'] . " limit 1";
            $tmp_name = $this->db->query($sql)->row_array();
            $list[$k]['cate_top_id'] = $tmp_name['name'];
            $list[$k]['add_time'] = date('Y-m-d', $v['add_time']);
        }

        admin_ajax_return(0, '', $list, $total);
    }

    public function del()
    {
        //接收参数
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : "";
        if (!$id) ajax_return(0, "参数ID错误！");

        //删除记录
        $sql = "update `xyq_goods` set is_del = 1 where id = $id";
        $this->db->query($sql);

        //返回结果
        ajax_return(1, "修改成功");
    }

    public function wdl_status()
    {
        //接收参数
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
        $status = isset($_POST["status"]) ? intval($_POST["status"]) : 0;
        if (!$id) ajax_return(0, "参数有误！");
        if ($status < 0 || $status > 1) $status = 1;

        //更改状态
        $sql = "update `xyq_goods` set is_sale = $status where id = $id";
        $this->db->query($sql);

        //返回结果
        ajax_return(1, "修改成功");
    }

    public function add()
    {
        //查询栏目
        $sql = "select * from `xyq_goods_cate` where pid = 0";
        $role = $this->db->query($sql)->result_array();

        //分配变量
        $this->assign("role", $role);
        $this->display();
    }

    public function edit($id = 0)
    {
        $id = intval($id);
        if ($id < 0) {
            IS_AJAX && ajax_return(0, "参数有误");
            $this->error("参数有误");
        }

        $sql = "select * from `xyq_goods` where id = $id";
        $info = $this->db->query($sql)->row_array();
        if (!$info) {
            IS_AJAX && ajax_return(0, "记录不存在");
            $this->error("记录不存在");
        }

        //查询栏目
        $sql = "select * from `xyq_goods_cate` where pid = 0";
        $role = $this->db->query($sql)->result_array();

        //查询二级栏目
        $sql = "select * from `xyq_goods_cate` where pid = " . $info['cate_top_id'];
        $role_son = $this->db->query($sql)->result_array();

        //图片分割
        $info['images'] = explode(',', $info['images']);

        //分配变量
        $this->assign("role", $role);
        $this->assign('info', $info);
        $this->assign('role_son', $role_son);
        $this->display();
    }

    public function wdl_get_two_cate_list()
    {
        $id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;

        //查询栏目
        $sql = "select * from `xyq_goods_cate` where pid = $id";
        $role = $this->db->query($sql)->result_array();

        //分配变量
        ajax_return(1, '', $role);
    }

    public function goods_post_into()
    {
        $images = isset($_POST['images']) ? $_POST['images'] : '';
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $info = isset($_POST['info']) ? sql_format($_POST['info']) : '';
        $cate_top_id = isset($_POST['cate_top_id']) ? $_POST['cate_top_id'] : 0;
        $cate_son_id = isset($_POST['cate_son_id']) ? $_POST['cate_son_id'] : 0;
        $id = isset($_POST['id']) ? $_POST['id'] : 0;

        if (!$name) ajax_return(0, '请输入商品名称');
        if (!$cate_top_id) ajax_return(0, '请选择商品分类');
        if (!$images) ajax_return(0, '请上传商品组图');
        if (!$info) ajax_return(0, '商品简介不允许为空');

        $data['img'] = $images[0];
        $data['images'] = implode(',', $images);
        $data['info'] = $info;
        $data['name'] = $name;
        $data['cate_top_id'] = $cate_top_id;
        $data['cate_son_id'] = $cate_son_id;

        if (!$id) {
            $data['is_sale'] = 1;
            $sql = data_to_build_insert_sql($data, 'xyq_goods');
            $this->db->query($sql);
            $res = $this->db->insert_id();
            if ($res) {
                ajax_return(1, "添加成功", "/" . $this->_controller);
            } else {
                ajax_return(0, "添加失败");
            }
        } else {
            $sql = data_to_build_update_sql($data, 'xyq_goods', "id = $id");
            $this->db->query($sql);
            ajax_return(1, "编辑成功", "/" . $this->_controller);
        }
    }

    //商品规格管理
    public function spec($id = 0)
    {
        $id = intval($id);
        if ($id < 0) {
            IS_AJAX && ajax_return(0, "参数有误");
            $this->error("参数有误");
        }

        $sql = "select * from xyq_goods_spec where goods_id = $id";
        $list = $this->db->query($sql)->result_array();

        $this->assign('id', $id);
        $this->assign('list', $list);
        $this->display();
    }

    public function spec_add($id = 0)
    {
        $id = intval($id);
        if ($id < 0) {
            IS_AJAX && ajax_return(0, "参数有误");
            $this->error("参数有误");
        }

        $sql = "select id from xyq_goods where id = $id";
        $goods = $this->db->query($sql)->row_array();
        if (!$goods) $this->error("参数有误");

        $this->assign('gid', $id);
        $this->display();
    }

    public function spec_edit($gid = 0, $id = 0)
    {
        $gid = intval($gid);
        $id = intval($id);
        if ($id < 0 || $gid < 0) {
            IS_AJAX && ajax_return(0, "参数有误");
            $this->error("参数有误");
        }

        $sql = "select id from xyq_goods where id = $gid";
        $goods = $this->db->query($sql)->row_array();
        if (!$goods) $this->error("参数有误");

        $sql = "select id,spec_name,market_price,shop_price,spec_img,spec_num from xyq_goods_spec where id = $id";
        $info = $this->db->query($sql)->row_array();
        if (!$info) $this->error("参数有误");

        $this->assign('id', $id);
        $this->assign('gid', $gid);
        $this->assign('info', $info);
        $this->display();
    }

    public function spec_post_into()
    {
        $id = isset($_POST['id']) ? $_POST['id'] : 0;
        $spec_name = isset($_POST['spec_name']) ? $_POST['spec_name'] : '';
        $market_price = isset($_POST['market_price']) ? $_POST['market_price'] : 0;
        $shop_price = isset($_POST['shop_price']) ? $_POST['shop_price'] : 0;
        $spec_num = isset($_POST['spec_num']) ? $_POST['spec_num'] : 999;
        $gid = isset($_POST['gid']) ? $_POST['gid'] : 0;
        $spec_img = isset($_POST['spec_img']) ? $_POST['spec_img'] : '';

        if (!$spec_name) ajax_return(0, '请输入规格名称');
        if (!$market_price) ajax_return(0, '请输入原价');
        if (!$shop_price) ajax_return(0, '请输入现价');

        $data['spec_name'] = $spec_name;
        $data['market_price'] = $market_price;
        $data['shop_price'] = $shop_price;
        $data['spec_num'] = $spec_num;
        $data['goods_id'] = $gid;
        $data['spec_img'] = $spec_img;

        if (!$id) {
            $sql = data_to_build_insert_sql($data, 'xyq_goods_spec');
            $this->db->query($sql);
            $res = $this->db->insert_id();
            if ($res) {
                ajax_return(1, "添加成功", "/" . $this->_controller);
            } else {
                ajax_return(0, "添加失败");
            }
        } else {
            $sql = data_to_build_update_sql($data, 'xyq_goods_spec', "id = $id");
            $this->db->query($sql);
            ajax_return(1, "编辑成功", "/" . $this->_controller);
        }
    }
}