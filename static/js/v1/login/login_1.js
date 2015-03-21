$(function() {
	var regxPhone = /^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/,
		  regxMail = /^[a-zA-Z0-9-._]{1,50}@[a-zA-Z0-9-]{1,65}.(com|net|org|info|biz|([a-z]{2,3}.[a-z]{2}))$/i,
		  regxPassword = /^[a-zA-Z0-9_]{6,16}$/;
	

	/**
	 * 当input获得焦点的时候隐藏警告框
	 */
	$("input").focus(function() {
		$(".login-alert-box").hide();
	});

	/**
	 * 请求登录接口
	 */
	var loginFlag = false;
	function getLogin() {
		if (loginFlag) {
			return;
		} else {
			loginFlag = true;
		}
		
		var account = $("#user-account").val(),
			  password = $("#user-password").val(),
			  _self = $("#get-login");

		if (!regxPhone.test(account) && !regxMail.test(account)) {
			$(".login-alert-box").text("账号格式错误").show();
			loginFlag = false;
			return;
		}
		
		_self.text("登录中...").addClass("disabled");
		
		$.post(
			"/login/getLogin/",
			{"account": account, 
			  "password": password,
			  "options": {
				  "type": "pc",
				  "stayTime": 7
			  }
			},
			function(data) {
				loginFlag = false;
				if (data.flag == 1) {
					_self.text("登录成功").removeClass("disabled");
					location.replace("/user");
				} else {
					if (data.flag == 4) {
						$(".login-alert-box").html(data.message + "<span class='resend-register-email'>重新发送邮件</span>").show();
						$(".resend-register-email").bind("click", resendMail);
					} else {
						$(".login-alert-box").text(data.message).show();
					}
					
					_self.text("立即登录").removeClass("disabled");
				}
			},
			"json"
		)
	}

	/**
	 * 绑定登陆函数
	 */
	$("#get-login").on("click", getLogin);
	$("#user-password").on("keyup", function(e) {
		if (e.keyCode == 13) {
			getLogin();
		}
	});

	//重新发送激活邮件
	var resendMailing = false;
	function resendMail() {
		var mail = $.trim($("#user-account").val()),
			_self = $(this);

		if (!regxMail.test(mail)) {
			$(".login-alert-box").text("邮箱格式错误").show();
			return;
		}

		if (resendMailing) {
			return;
		} else {
			resendMailing = true;
		}
		_self.text("正在发送...");
		$.post(
			"/register/resendRegisterMail",
			{email:mail},
			function(data) {
				_self.text("重新发送邮件");
				resendMailing = false;
				alert(data.message);
			},
			"json"
		);
	};
});