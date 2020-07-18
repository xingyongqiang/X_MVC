$(function(){
    //通用form提交
    $("form").submit(function () {
        var method = $(this).attr("method");
        if (method == "get") {
            //搜索结果
            var data = $(this).serializeArray();
            var	url = "/search";
            $.each(data, function(key, val) {
                if (val.value == "") {
                    alert("关键字不允许为空");
                    ///url += "/0";
                } else {
                    url += "/" + val.value;
                    location.href = url;
                }
            });
        }
        return false;
    })
});