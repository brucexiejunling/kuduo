$(function() {
        /**
         * 文本框输入内容的时候右侧同步更新
         */
        var previewWrap = $("#qr-preview-url");
        $("#url-value").keyup(function() {
                previewWrap.text($(this).val());
        })
        
        /*
         * =========================点击下一步记录信息=======================
         */
        $(".btn-next-render").click(function() {
    		if ($("#url-value").val() == "http://" || $("#url-value").val() == "https://") {
    			return;
    		}
            $.post(
        		"/create/save/",
                {
                    type: "url", 
                    urlValue: $("#url-value").val()
                },
                function(data) {
                	if (data.status == 1) {
                		location.href = "/create/modify";
                	} else {
                		alert(data.message);
                	}
                	 
                },
                "json"
            );
        });
});