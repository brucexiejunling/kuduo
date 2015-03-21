$(function() {
	var regxPhone = /^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/,
		regxMail = /^[a-zA-Z0-9-._]{1,50}@[a-zA-Z0-9-]{1,65}.(com|net|org|info|biz|([a-z]{2,3}.[a-z]{2}))$/i,
		regxPassword = /^[a-zA-Z0-9_]{6,16}$/,
		alertBox = $(".register-alert-box");
	/**
	 * 功能描述：验证手机格式、发送验证码请求
	 */
	function sendPhoneCode() {
		$("#send-phone-code").unbind("click").text("发送中...").addClass("disabled");

		var phone = $.trim($("#phone-number").val());
		if (regxPhone.test(phone)) {
			$.post(
				"/register/sendRegisterPhoneCode",
				{"phone": phone},
				function(responce) {
					if (responce.flag) {
						var sendBtn = $("#send-phone-code");
						sendBtn.attr("seconds", 60);
						var timeinterval = setInterval(function(){
							var second = parseInt(sendBtn.attr("seconds"), 10);
							sendBtn.text(second + "秒后再发");
							sendBtn.attr("seconds", second - 1);

							if (second == 0) {
								sendBtn.removeClass("disabled").bind("click", sendPhoneCode).text("发送");
								clearInterval(timeinterval);
							}
						}, 1000);
					} else {
						alertBox.show().text(responce.message);
						$("#send-phone-code").bind("click", sendPhoneCode).text("重新发送").removeClass("disabled");
					}
				},
				"json"
			);
		} else {
			alertBox.show().text("您的手机号码格式有误！");
			$("#send-phone-code").bind("click", sendPhoneCode).text("发送激活码").removeClass("disabled");
		}
	};

	/**
	 * 绑定验证码发送函数
	 */
	$("#send-phone-code").bind("click", sendPhoneCode);

	/**
	 * 当input框有输入的时候alert框隐藏
	 */
	$("input").focus(function() {
		alertBox.addClass("hide");
	});

	
	//手机号码注册方式按钮绑定
	$("#register-next-phone").bind("click", registerNextPhone);
	
	
	/**
	 * 提交密码和手机信息、验证码验证有效性、检查密码的正确性
	 */
	function registerNextPhone() {
		var password = $.trim($("#user-password").val()),
			code = $.trim($("#phone-code").val()),
			that = $(this),
			phone = $.trim($("#phone-number").val());				//手机号码

		//检验密码有效性
		if (!regxPassword.test(password)) {
			alertBox.show().text("密码格式错误");
			return;
		}

		//检验手机号码有效性
		if (!regxPhone.test(phone)) {
			alertBox.show().text("手机号码格式错误");
			return;
		}
		
		that.text("提交中...").addClass("disabled").unbind("click");

		//验证验证码
		$.post(
			"/register/getRegister/",
			{"mobileCode": code, "account": phone, "password": password, "type": "web_app_register"},
			function(responce) {
				if (responce.flag == 1) {
					that.text("注册成功、正在跳转...").removeClass("disabled");
					setTimeout(function() {
						history.go(-1);
					}, 4000);
				} else {
					that.text("立即注册").removeClass("disabled").bind("click", registerNextPhone);
					alertBox.show().text(responce.message);
				}
			},
			"json"
		);
	}
	
	//邮箱注册绑定
	$("#register-next-email").bind("click", registerNextMail);
	
	/**
	 * 使用邮箱注册时提交密码和电子邮件、检查密码的正确性
	 */
	function registerNextMail() {
		var password = $.trim($("#user-password-mail").val()),
			that = $(this),
			mail = $.trim($("#email-input").val());

		//检验邮箱的有效性
		if (!regxMail.test(mail)) {
			alertBox.show().text("邮箱格式错误");
			return;
		}

		//检验密码有效性
		if (!regxPassword.test(password)) {
			alertBox.show().text("密码由6-16位非空字符组成");
			return;
		}

		that.text("提交中...").addClass("disabled").unbind("click");

		$.post(
			"/register/getRegister/",
			{"account": mail, "password": password, "type": "mobile"},
			function(data) {
				if (data.flag == 1) {
					alertBox.show().text("已发送激活链接到您的邮箱，请激活后再进行登录...");
					that.text("注册成功、正在跳转...").removeClass("disabled");
					setTimeout(function() {
						history.go(-1);
					}, 4000);
				} else {
					that.text("立即注册").removeClass("disabled").bind("click", registerNextMail);
					alertBox.show().text(data.message);
				}
			},
			"json"
		);
	}
	
	/**
	 * 功能：当密码输入框失去焦点的时候检查密码的有效性
	 * 这边包含手机号码注册以及邮箱注册
	 */
	$("#user-password, #user-password-mail").blur(function() {
		var password = $.trim($(this).val());
		if (!regxPassword.test(password)) {
			alertBox.show().text("密码由6-16位非空字符组成");
		}
	});

	/**
	 * input框失去焦点时检查手机号码有效性
	 * @author 李珠刚
	 */
	$("#phone-number").blur(function() {
		var phone = $.trim($(this).val());
		if (!regxPhone.test(phone)) {
			alertBox.show().text("手机号码格式错误");
		}
	});
	
	/**
	 * input框失去焦点时检查电子邮件有效性
	 * @author 李珠刚
	 */
	$("#email-input").blur(function() {
		var email = $.trim($(this).val());
		if (!regxMail.test(email)) {
			alertBox.show().text("电子邮箱格式错误");
		}
	});
	
	/**
	 * 返回主界面
	 */
	$("#go-back").click(function(e){
		e.stopPropagation();
		history.go(-1);
	});
	
	/**
	 * 使用邮箱注册，隐藏手机号码注册的页面
	 */
	$(".go-mail-method").click(function() {
		alertBox.text("").hide();
		$(".register-form-phone").hide();
		$(".register-form-mail").show();
	});
	
	/**
	 * 使用手机注册，隐藏邮箱注册的页面
	 */
	$(".go-phone-method").click(function() {
		alertBox.text("").hide();
		$(".register-form-mail").hide();
		$(".register-form-phone").show();
	});
});