<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
<title>沙雕程序员博客</title>
<meta name="renderer" content="webkit">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<link rel="icon" href="/favicon.ico">
<link rel="stylesheet" href="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/layui/css/layui.css" media="all"/>
<link rel="stylesheet" href="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/layui/css/admin.css" media="all"/>
<script type="text/javascript" src="<?php echo STATIC_SERVER; ?>/common/jquery-1.12.1.min.js"></script>
<script type="text/javascript" src="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/layui/layui.js"></script>
</head>
<body class="main_body">
<div class="layui-layout layui-layout-admin">
    <!-- 顶部 -->
<div class="layui-header">
    <!-- 头部区域 -->
    <ul class="layui-nav layui-layout-left">
        <li class="layui-nav-item layui-hide-xs" lay-unselect>
            <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>" target="_blank" title="前台">
                <i class="layui-icon layui-icon-website"></i>
            </a>
        </li>
        <li class="layui-nav-item" lay-unselect="">
            <a href="javascript:clear_system_cache();" layadmin-event="refresh" title="刷新">
                <i class="layui-icon layui-icon-refresh-3"></i>
            </a>
        </li>
        <span class="layui-nav-bar" style="left: 198px; top: 48px; width: 0px; opacity: 0;"></span>
    </ul>
    <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">

        <li class="layui-nav-item" lay-unselect="">
            <a lay-href="app/message/index.html" layadmin-event="message" lay-text="消息中心">
                <i class="layui-icon layui-icon-notice"></i>
                <!-- 如果有新消息，则显示小圆点 -->
                <span class="layui-badge-dot"></span>
            </a>
        </li>
        <li class="layui-nav-item">
            <a href="javascript:;">
                <img src="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/images/face.jpg" class="layui-circle" width="35" height="35">
                <cite><?php echo $sys_admin['nickname']; ?></cite>
            </a>
            <dl class="layui-nav-child">
                <dd><a href="javascript:;">
                    <i class="iconfont icon-zhanghu" data-icon="icon-zhanghu"></i><cite>个人资料</cite></a>
                </dd>
                <dd><a href="javascript:;">
                    <i class="iconfont icon-shezhi1" data-icon="icon-shezhi1"></i><cite>修改密码</cite></a>
                </dd>
                <dd><a href="javascript:;" onclick="logout();"><i class="iconfont icon-loginout"></i><cite>退出</cite></a></dd>
            </dl>
        </li>
        <li class="layui-nav-item layui-hide-xs" lay-unselect="">
            <a href="javascript:;" layadmin-event="about"><i class="layui-icon layui-icon-more-vertical"></i></a>
        </li>
    </ul>
</div>
<!-- 左侧导航 -->
<div class="layui-side layui-bg-black">
    <div class="navBar layui-side-scroll">
        <div class="layui-logo">
            <span id="tp-weather-widget"></span>
            <script type="text/javascript" src="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/js/tian.js"></script>
        </div>
        <div class="user-photo">
            <a class="img" title="我的头像"><img src="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/images/face.jpg"></a>
            <p>你好！<span class="userName"><?php echo $sys_admin['nickname']; ?></span>, 欢迎登录</p>
        </div>
        <ul class="layui-nav layui-nav-tree">
            <li class="layui-nav-item"><a href="/<?php echo $common_info['module']; ?>"><i class="layui-icon" style="font-size:16px; font-weight:bold;margin-right:5px;">&#xe68e;</i><cite>后台首页</cite></a></li>
            <?php foreach ($sys_menu as $k => $v) { ?>
            <li class="layui-nav-item <?php if ($common_info['controller'] == $v['nid']) { ?>layui-this<?php } ?>">
                <a href="<?php echo $v['url']; ?>">
                    <i class="layui-icon" style="font-size:16px; font-weight:bold;margin-right:5px;"><?php echo $v['icon']; ?></i><cite><?php echo $v['name']; ?></cite>
                </a>
            </li>
            <?php } ?>
            <li class="layui-nav-item"><a href="javascript:;" onclick="logout();"><i class="layui-icon" style="font-size:16px; font-weight:bold;margin-right:5px;">&#xe651;</i><cite>退出管理</cite></a></li>
        </ul>
    </div>
</div>
    <!-- 右侧内容 -->
    <div class="layui-body layui-form">
        <div class="layui-tab marg0" lay-filter="bodyTab">
            <div class="layui-tab-content clildFrame">
                <div class="layui-tab-item layui-show">
                    <div class="childrenBody">
                        <div class="layui-card layadmin-header mb10">
                            <div class="layui-breadcrumb" lay-filter="breadcrumb" style="visibility: visible;">
                                <a href="/<?php echo $common_info['module']; ?>">后台首页</a><span lay-separator="">/</span>
                                <a><cite>广告管理</cite></a>
                            </div>
                        </div>
                        <blockquote class="layui-elem-quote news_search">
                            <div class="layui-inline">
                                <a href="/<?php echo $common_info['module']; ?>/<?php echo $common_info['controller']; ?>/add" style="background-color:#5FB878" class="layui-btn layui-btn-normal newsAdd_btn">添加广告</a>
                            </div>
                            <div class="layui-inline">
                                <a class="layui-btn layui-btn-danger audit_btn  btn-del-all">批量删除</a>
                            </div>
                        </blockquote>
                        <div class="layui-form links_list">
                            <table class="layui-table">
                                <colgroup>
                                    <col width="50">
                                    <col width="3%">
                                    <col>
                                    <col>
                                    <col>
                                    <col>
                                    <col>
                                    <col width="13%">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" name="" lay-skin="primary" lay-filter="allChoose" id="allChoose">
                                        <div class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon"></i></div>
                                    </th>
                                    <th style="text-align:left;">ID</th>
                                    <th>广告名称</th>
                                    <th>广告图</th>
                                    <th>链接地址</th>
                                    <th>状态</th>
                                    <th>添加时间</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody class="links_content">
                                <?php if (empty($list) == false) { ?>
                                <?php foreach ($list as $val) { ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" value="<?php echo $val['id']; ?>" name="id[]" lay-skin="primary" lay-filter="choose">
                                        <div class="layui-unselect layui-form-checkbox" lay-skin="primary"><i class="layui-icon"></i></div>
                                    </td>
                                    <td align="left"><?php echo $val['id']; ?></td>
                                    <td><?php echo $val['name']; ?></td>
                                    <td>
                                        <a class="common-pic" href="javascript:;">
                                            <img style="height:40px;" src="<?php echo $val['img']; ?>">
                                        </a>
                                    </td>
                                    <td><a style="color:#1E9FFF;" target="_blank" href="<?php echo $val['url']; ?>"><?php echo $val['url']; ?></a></td>
                                    <td class="label-status" data="<?php echo $val['id']; ?>">
                                        <input type="checkbox" name="show" lay-skin="switch" lay-text="是|否" value="<?php echo $val['status']; ?>" <?php if ($val['status'] == 1) { ?>checked<?php } ?> />
                                        <div class="layui-unselect layui-form-switch" lay-skin="_switch"><em>是</em><i></i></div>
                                    </td>
                                    <td><?php echo date('Y-m-d',strtotime($val['addtime'])); ?></td>
                                    <td>
                                        <a href="/<?php echo $common_info['module']; ?>/<?php echo $common_info['controller']; ?>/edit/<?php echo $val['id']; ?>" class="layui-btn layui-btn-sm"><i class="layui-icon">&#xe642;</i></a>
                                        <a href="javascript:;" data="<?php echo $val['id']; ?>" class="layui-btn layui-btn-danger layui-btn-sm btn-del-one"><i class="layui-icon">&#xe640;</i></a>
                                    </td>
                                </tr>
                                <?php } ?>
                                <?php } else { ?>
                                <td colspan="7" style="text-align:center">暂无数据</td>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="page">
                            <?php echo $page; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 底部 -->
    <div class="layui-footer footer">
    <p>加载耗时 <?php echo $runtime; ?>ms</p>
</div>

<script>
    layui.use(['form','element'], function(){
        var form = layui.form;
        var element = layui.element;
        form.on('checkbox(allChoose)', function(data){
            var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
            child.each(function(index, item){
                item.checked = data.elem.checked;
            });
            form.render('checkbox');
        });
    });

    $('.common-pic').on('click', function(){
        var img=$(this).find("img").attr("src");
        layer.open({
            title:"图片预览",
            type: 1,
            area: ['600px'],
            shadeClose: true, //点击遮罩关闭
            content: '<div style="padding:20px;"><img src="'+img+'" width="560" /></div>'
        });
    });

    //全局设置
    var module = "<?php echo $common_info['module']; ?>";
    var controller = "<?php echo $common_info['controller']; ?>";
    var action = "<?php echo $common_info['action']; ?>";
    $('.layui-this').parent('dl').parent('li').addClass('layui-nav-itemed');
</script>
<script src="<?php echo STATIC_SERVER; ?>/common/base.js?<?php echo time(); ?>"></script>
</div>
</body>
</html>