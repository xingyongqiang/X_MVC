$(function(){
	//通用form提交
	$("form").submit(function () {
		var method = $(this).attr("method");
		var action = $(this).attr("action");
		if (method == "post") {
			//保存表单
			var msg = $(this).attr("data-msg");
			if (!msg) msg = "您确定要提交信息么？";
			var data = $(this).serialize();
			layer.confirm(msg, {icon: 3}, function(){
				http_request_post(action, data, function (result) {
					layer.msg(result.msg, {icon:1, time:800}, function (){
						if (result.data != "") {
							location.href = "/";
						} else {
							location.reload();
						}
					});
				});
			});
		}
		return false;
	})
});

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