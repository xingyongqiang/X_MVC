$(function(){
	//全选/反选
	$("input:checkbox[name='check-all']").click(function(){
		alert(1);
		var thisList = $("input:checkbox[name='id[]']");
		if($(this).prop("checked")){
			thisList.each(function(){ 
				if($(this).attr("disabled") == "disabled"){
					$(this).prop("checked",false);
				}else{
					$(this).prop("checked",true);
				}
			});
		}else{
			thisList.prop("checked",false);
		}
	});
	
	//删除一条
	$(".btn-del-one").click(function(){
		var id = $(this).attr("data");
		if (id) del(id);
	})
	
	//删除全部
	$(".btn-del-all").click(function(){
		var id = get_check();
		if (id) del(id);
	})
	
	//还原一条
	$(".btn-back-one").click(function(){
		var id = $(this).attr("data");
		if (id) back(id);
	})
	
	//还原全部
	$(".btn-back-all").click(function(){
		var id = get_check();
		if (id) back(id);
	})
	
	//启用状态
	$(".label-status").click(function(){
		if ($(this).find("input").val()==1) {
			var status = 0;
		} else {
			var status = 1;
		}
		var id = $(this).attr("data");
		http_request_post(req + "/change", {id:id, status:status}, function(res) {});
	})
	
	//通用form提交
	$("form").submit(function () {
		var method = $(this).attr("method");
		var action = $(this).attr("action");
		if (method == "post") {
			//保存表单
			var msg = $(this).attr("data-msg");
			if (!msg) msg = "您确定要提交保存么？";
			var data = $("form").serialize();
			layer.confirm(msg, {icon: 3}, function(){
				http_request_post(action, data, function (result) {
					layer.msg(result.msg, {icon:1, time:800}, function (){
						if (result.data != "") {
							location.href = "/" + module + result.data;
						} else {
							location.reload();
						}
					});
				});
			});
		} else {
			//搜索结果
			var data = $(this).serializeArray();
			var	url = req + "/" + action;
			$.each(data, function(key, val) {
				if (val.value == "") {
					url += "/0";
				} else {
					url += "/" + val.value;
				}
			});
			location.href = url;
		}
		return false;
	})
})

//全局变量
var req = "/" + module + "/" + controller;
var p = 2;
var tmp = 0;

//自动加载
$(window).scroll(function() {
	var scrollTop = $(this).scrollTop();
	var scrollHeight = $(document).height();
	var windowHeight = $(this).height();
	if (scrollTop + windowHeight == scrollHeight) {
		if (tmp != p) {
			//业务需求
			//.....
			//.....
			//.....
			//.....
			//.....
		}
	}
});

//角色授权
function go_auth() {
	var auth = get_check();
	http_request_post("", {auth:auth}, function(res) {
		layer.msg(res.msg, {icon: 1,time: 800}, function(){location.reload();});
	});
}

//清除缓存
function clear_system_cache() {
	layer.confirm('您确定要更新缓存？', {icon: 3}, function () {
		var url = "/" + module + "/user/clear_system_cache";
		layer.closeAll();
		http_request_post(url, null, function(res) {
			layer.msg(res.msg, {icon: 1,time: 800});
		});
	});
}
		
//退出登录
function logout() {
	layer.confirm('您确定要退出？', {icon: 3}, function () {
		stock = system_info = -1
		var url = "/" + module + "/user/logout";
		layer.closeAll();
		http_request_post(url, null, function(res) {
			layer.msg(res.msg, {icon: 1, time: 800}, function () {
				//跳转到登录页面
				location.href = "/" + module + "/user/login";
			});
		});
	});
}

//还原记录
function back(id) {
	layer.confirm('您确定要执行还原？', {icon: 3}, function () {
		var url = req + "/back";
		layer.closeAll();
		http_request_post(url, {id:id}, function(res) {
			layer.msg(res.msg, {icon: 1,time: 800}, function(){location.reload();});
		});
	})
}

//删除记录
function del(id) {
	layer.confirm('您确定要执行删除？', {icon: 3}, function () {
		var url = req + "/del";
		layer.closeAll();
		http_request_post(url, {id:id}, function(res) {
			layer.msg(res.msg, {icon: 1,time: 800}, function(){location.reload();});
		});
	})
}

//获取选中
function get_check() {
	var gid = new Array();
	$("input:checkbox[name='id[]']").each(function() {
		if ($(this).prop("checked")) {
			var bid = parseInt($(this).val());
			gid.push(bid);
		}
	});
	var id = gid.join(",");
	if (! id) {
		layer.msg("请选择你要操作的记录！");return false;
	}
	return id;
}

//图片放大
function layui_open_img(img) {
	layer.open({
		title:"图片预览",
		type: 1,
		area: ['600px'],
		shadeClose: true, //点击遮罩关闭
		content: '<div style="padding:20px;"><img src="'+img+'" width="560" /></div>'
	});
}

//通用post请求
function http_request_post(url, data, callback, type) {
	$.ajax({
		type : 'post',  
		url : url,
		dataType : 'json',
		data : data,
		success : function(repones) {
			if (type == "all") {
				//原样返回
				if (typeof callback === "function") {
					callback(repones);
					return false;
				} else {
					layer.msg('请求加载数据失败，请重新加载');
				}
			} else {
				if (repones.status == 1) {
					if (typeof callback === "function") {
						callback(repones);
						return false;
					} else {
						layer.msg('请求加载数据失败，请重新加载');
					}
				} else {
					layer.msg(repones.msg);
					return false;
				}
			}
		},
		error : function () {
			return false;
		}
	});
	return false;
}