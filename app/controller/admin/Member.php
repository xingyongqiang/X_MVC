<?php
/**
 * 用户管理
 */
defined('BASEPATH') or exit("No direct script access allowed");


class Member extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    //首页
    public function index($keyword = "")
    {
        $this->assign('keyword', $keyword);
        $this->display();
    }

    //首页
    public function index_list()
    {
        $keyword = isset($_GET["keyword"]) ? $_GET["keyword"] : '';//是否启用
        $page = isset($_GET["page"]) ? $_GET["page"] : 1;//是否启用
        $limit = isset($_GET["limit"]) ? $_GET["limit"] : 10;//是否启用

        $where = '';
        if ($keyword) $where = "where nickName like '%$keyword%'";

        //页码
        $page = (int)$page <= 0 ? 1 : (int)$page;
        $start = ($page - 1) * $limit;

        //总数
        $sql = "select count(*) as c from `xyq_user`";
        $total = $this->db->query($sql)->row_array();
        $total = $total["c"];

        //查询
        $sql = "select * from `xyq_user` $where order by user_id desc limit $start,$limit";
        $list = $this->db->query($sql)->result_array();

        admin_ajax_return(0, '', $list, $total);
    }
}