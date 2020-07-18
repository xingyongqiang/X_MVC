<?php

/**
 * 图片管理
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Picture extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 列表
     */
    public function index($cid = 0, $status = 99, $keyword = "", $p = 1)
    {
        //通用搜索
        $where = " and a.isdel = 0";
        $keyword = sql_format($keyword);
        if ($cid) $where .= " and a.cid = $cid";//栏目
        if ($status != 99) $where .= " and a.status = $status";//状态
        //搜索条件
        if ($keyword) {
            $where .= " and a.title like '%$keyword%'";
        } else {
            $keyword = 0;
        }

        //数据分页
        $p = (int)$p <= 0 ? 1 : (int)$p;
        $size = 15;
        $start = ($p - 1) * $size;

        //统计总数
        $sql = "SELECT count(*) as c FROM xyq_article a INNER JOIN xyq_classes c ON a.cid = c.id where c.model=1" . $where;
        $total = $this->db->query($sql)->row_array();
        $total = $total["c"];

        //查询记录
        $sql = "SELECT a.img,a.title,a.id,c.`name` FROM xyq_article a INNER JOIN xyq_classes c ON a.cid = c.id where c.model=1" . $where . " order by a.id desc limit $start, $size";
        $list = $this->db->query($sql)->result_array();

        //分页展示
        $page = page_format($total, $size, $p, $cid . "/" . $status . "/" . $keyword);

        //查询栏目
        $sql = "select * from `xyq_classes` where model=1 order by sorts asc, id desc";
        $res = $this->db->query($sql)->result_array();
        $classes = list_to_tree($res);

        //分配变量
        $this->assign('classes', $classes);
        $this->assign('list', $list);
        $this->assign('total', $total);
        $this->assign('cid', $cid);
        $this->assign('keyword', $keyword);
        $this->assign('page', $page);
        $this->display();
    }

    /**
     * 新增
     */
    public function add()
    {
        if (IS_POST) {
            //接收参数
            $title = isset($_POST["title"]) ? sql_format($_POST["title"]) : "";
            $aid = isset($_POST["aid"]) ? (int)$_POST["aid"] : 0;
            $cid = isset($_POST["cid"]) ? (int)$_POST["cid"] : 0;
            $nid = isset($_POST["nid"]) ? sql_format($_POST["nid"]) : "";
            $author = isset($_POST["author"]) ? sql_format($_POST["author"]) : "";
            $source = isset($_POST["source"]) ? sql_format($_POST["source"]) : "";
            $intro = isset($_POST["intro"]) ? sql_format($_POST["intro"]) : "";
            $content = isset($_POST["content"]) ? $_POST["content"] : "";
            $stitle = isset($_POST["stitle"]) ? sql_format($_POST["stitle"]) : "";
            $skey = isset($_POST["skey"]) ? sql_format($_POST["skey"]) : "";
            $sdesc = isset($_POST["sdesc"]) ? sql_format($_POST["sdesc"]) : "";
            $status = isset($_POST["status"]) ? (int)$_POST["status"] : 0;
            $sorts = isset($_POST["sorts"]) ? (int)$_POST["sorts"] : 99;
            $img = isset($_POST['img']) ? $_POST["img"] : "";

            //校验参数
            if ($cid <= 0) ajax_return(0, "请选择文档栏目");
            if (!$title) ajax_return(0, "标题不能为空");
            if (!$content) ajax_return(0, "文章内容不能为空");
            if ($status < 0 || $status > 2) $status = 0;

            //判断是否存在
            $sql = "select id from xyq_article where title = '$title'";
            $res = $this->db->query($sql)->row_array();
            if ($res) ajax_return(0, "文档已存在！");
            if (!$nid) $nid = rand_code(8, "both");
            if ($nid && strlen($nid) < 3) ajax_return(0, "短连接长度至少为3位！");


            //写入记录
            $words = mb_strlen(strip_tags($content));
            $sql = "insert into xyq_article (title, cid, aid, nid, author, source, sorts, img, intro, content, words, stitle, skey, sdesc, status) values ('$title', $cid, $aid, '$nid', '$author', '$source', '$sorts', '$img', '$intro', '$content', $words, '$stitle', '$skey', '$sdesc', $status)";
            $this->db->query($sql);
            $res = $this->db->insert_id();

            //返回结果
            if (!$res) {
                ajax_return(0, "添加失败");
            } else {
                ajax_return(1, "添加成功", "/" . $this->_controller . "/index");
            }
        }

        //查询栏目
        $sql = "select * from `xyq_classes` where status = 1 and model = 1 order by sorts asc, id desc";
        $list = $this->db->query($sql)->result_array();
        $classes = list_to_tree($list);

        //分配变量
        $this->assign('classes', $classes);
        $this->display();
    }

    /**
     * 编辑
     */
    public function edit($id = 0)
    {
        //校验参数
        $id = intval($id);
        if ($id < 0) {
            IS_AJAX && ajax_return(0, "参数有误");
            $this->error("参数有误");
        }

        //查询记录
        $sql = "select * from `xyq_article` where id = $id";
        $res = $this->db->query($sql)->row_array();
        if (!$res) {
            IS_AJAX && ajax_return(0, "记录不存在");
            $this->error("记录不存在");
        }

        $sql = "select * from `xyq_attach` where aid = $id";
        $tmp_img = $this->db->query($sql)->row_array();
        $imgs = explode("|", $tmp_img['file_path']);
        if (IS_POST) {
            //接收参数
            $title = isset($_POST["title"]) ? sql_format($_POST["title"]) : "";
            $aid = isset($_POST["aid"]) ? (int)$_POST["aid"] : 0;
            $cid = isset($_POST["cid"]) ? (int)$_POST["cid"] : 0;
            $nid = isset($_POST["nid"]) ? sql_format($_POST["nid"]) : "";
            $author = isset($_POST["author"]) ? sql_format($_POST["author"]) : "";
            $source = isset($_POST["source"]) ? sql_format($_POST["source"]) : "";
            $img = isset($_POST["img"]) ? $_POST["img"] : "";
            $intro = isset($_POST["intro"]) ? sql_format($_POST["intro"]) : "";
            $content = isset($_POST["content"]) ? $_POST["content"] : "";
            $stitle = isset($_POST["stitle"]) ? sql_format($_POST["stitle"]) : "";
            $skey = isset($_POST["skey"]) ? sql_format($_POST["skey"]) : "";
            $sdesc = isset($_POST["sdesc"]) ? sql_format($_POST["sdesc"]) : "";
            $status = isset($_POST["status"]) ? (int)$_POST["status"] : 0;
            $sorts = isset($_POST["sorts"]) ? (int)$_POST["sorts"] : 0;

            //校验参数
            if ($cid <= 0) ajax_return(0, "请选择文档栏目");
            if (!$title) ajax_return(0, "标题不能为空");
            //if (! $intro) ajax_return(0, "简介不能为空");
            if (!$content) ajax_return(0, "文章内容不能为空");
            if ($status < 0 || $status > 2) $status = 0;
            if (!$nid) $nid = rand_code(8, "both");
            if ($nid && strlen($nid) < 3) ajax_return(0, "短连接长度至少为3位！");

            //判断是否存在
            $sql = "select id from xyq_article where title = '$title' and id != $id";
            $res = $this->db->query($sql)->row_array();
            if ($res) ajax_return(0, "文档已存在！");

            //更新记录
            $words = mb_strlen(strip_tags($content));
            $sql = "update `xyq_article` set title = '$title', aid = $aid, cid = $cid, nid = '$nid', sorts = '$sorts', author = '$author', source = '$source', intro = '$intro', words = $words, stitle = '$stitle', skey = '$skey', sdesc = '$sdesc', img = '$img', content = '$content', status = $status, uptime = CURRENT_TIMESTAMP where id = $id";
            $this->db->query($sql);

            //返回结果
            ajax_return(1, "修改成功", "/" . $this->_controller . "/index");
        }

        //查询记录
        $sql = "select * from `xyq_classes` where status = 1 and model = 1 order by sorts asc, id desc";
        $list = $this->db->query($sql)->result_array();
        $classes = list_to_tree($list);

        //分配变量
        $this->assign('classes', $classes);
        $this->assign('res', $res);
        $this->assign('imgs', $imgs);
        $this->display();
    }

    /**
     * 删除
     */
    public function del()
    {
        if (IS_AJAX) {
            //接收参数
            $id = isset($_POST["id"]) ? strval($_POST["id"]) : "";
            if (!$id) ajax_return(0, "参数有误！");

            //删除记录
            $sql = "update `xyq_article` set isdel = 1 where id in (" . $id . ")";
            $this->db->query($sql);

            //返回结果
            ajax_return(1, "删除成功");
        }
    }
}
