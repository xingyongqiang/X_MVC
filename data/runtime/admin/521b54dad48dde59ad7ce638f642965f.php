<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>登录</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" href="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/layui/css/layui.css" media="all" />
	<link rel="stylesheet" href="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/css/login.css" media="all" />
	<script type="text/javascript" src="<?php echo STATIC_SERVER; ?>/common/jquery-1.12.1.min.js"></script>
	<script type="text/javascript" src="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/layui/layui.js"></script>
</head>
<body>
<div class="video_mask"></div>
<div class="login">
	<img src="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/images/b2.png" />
	<h1>管理员登录</h1>
	<form>
		<div class="layui-form-item">
			<input class="layui-input" name="username" placeholder="用户名"  type="text">
		</div>
		<div class="layui-form-item">
			<input class="layui-input" name="password" placeholder="密码" type="password">
		</div>
		<input type="submit" class="layui-btn login_btn"  value="立即登录" />
	</form>
</div>
<canvas id="Mycanvas"></canvas>
<script type="text/javascript" src="<?php echo STATIC_SERVER; ?>/<?php echo $common_info['module']; ?>/js/login.js"></script>
<script>
	layui.use('form', function(){
		var form = layui.form;
	});
	$("form").submit(function(){
		var data = $(this).serialize();
		$.ajax({
			type:'post',
			url : "/<?php echo $common_info['module']; ?>/user/login",
			dataType : 'json',
			data : data,
			success : function(repones) {
				if (repones.status == 1 || repones.status == 2) {
					layer.msg('登录成功', {icon: 1, time: 800}, function(){
						location.href = "/<?php echo $common_info['module']; ?>";
					});
				} else {
					layer.msg(repones.msg);return false;
				}
			},
			error : function() {
				layer.msg('加载数据失败，请重新加载');return false;
			}
		});
		return false;
	})
</script>
</body>
</html>