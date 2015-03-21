$(function() {
		var regxNickName = /^.{1,40}$/,
            regxthingName = /^.{1,40}$/; 

    /**
     * 提交信息
     */
	var sendFlag = false;
    $("#submit-btn").bind("click", function() {
    	
    	if (sendFlag) {
    		return;
    	} else {
    		//sendFlag = true;
    	}
    	
        //未登录提交
        var login = $(this).attr("login");

    	var nickname = $.trim($("#owner-nickname").val()),
    		introduce = $.trim($("#owner-introduce").val()),
            thingname = $.trim($("#thing-name").val()),
            phone = $.trim($(".phone-number").val()),
    		self = $(this);
    	
    	//检验昵称有效性
    	if (!regxNickName.test(nickname)) {
    		$(".alert-box").text("物主名称长度过长或未填写").show();
    		sendFlag = false;
    		return;
    	}
    	
        if (!regxthingName.test(nickname)) {
            $(".alert-box").text("物品名称过长或未填写").show();
            sendFlag = false;
            return;
        }

        if (introduce == "") {
            $(".alert-box").text("物品介绍内容不能为空").show();
            sendFlag = false;
            return;
        }
    	
        if (login == "") {
            var flag = confirm("您还未登录、是否确认继续提交？（登录后拾到者的留言信息将以短信或邮件的形式发送给您）")
            if (!flag) {
                sendFlag = false;
                return;
            }
        }

    	self.text("提交中....").addClass("disabled");
    	/**
    	 * 提交
    	 */
    	$.post(
    		"/show/katieSubmit",
    		{nickname: nickname, thingname:thingname, introduce: introduce, phone: phone},
    		function(data) {
                self.text("提交").removeClass("disabled");
    			if (data.flag == 1) {
    				alert("绑定成功！刷新后即可查看效果");
                    window.location.reload();
    			} else {
                    alert(data.message);
                }
    		}
    	);
    });

    //输入时、隐藏提交框，防止遮盖
    $("textarea,input").focus(function() {
        $(".submit-wrap").hide();
    }).blur(function() {
        $(".submit-wrap").show();
    });
});