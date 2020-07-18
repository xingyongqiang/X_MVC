<?php
/**
 * 快递管理
 */
defined('BASEPATH') or exit("No direct script access allowed");

class Express extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    //首页
    public function index($p = 1)
    {
        //页码
        $p = (int)$p <= 0 ? 1 : (int)$p;
        $size = 15;
        $start = ($p - 1) * $size;

        //总数
        $sql = "select count(*) as c from `xyq_express_area`";
        $total = $this->db->query($sql)->row_array();
        $total = $total["c"];

        //查询
        $sql = "select express_area_id,express_id,price,express_name,add_time from `xyq_express_area` order by id asc limit $start,$size";
        $list = $this->db->query($sql)->result_array();

        foreach ($list as $k => $v) {
            $list[$k]['add_time'] = date('Y-m-d', $v['add_time']);
        }

        //分页
        $page = page_format($total, $size, $p, "");

        //赋值变量
        $this->assign("list", $list);
        $this->assign("page", $page);
        $this->display();
    }
}