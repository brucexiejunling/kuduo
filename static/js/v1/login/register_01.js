$(function() {
	var regxPhone = /^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/,
		  regxMail = /^[a-zA-Z0-9-._]{1,50}@[a-zA-Z0-9-]{1,65}.(com|net|org|info|biz|([a-z]{2,3}.[a-z]{2}))$/i,
		  regxPassword = /^[a-zA-Z0-9_]{6,16}$/;
	
	/**
	 * 更换验证码
	 */
	$("#verify-code-image, #verify-code-link").click(function() {
		$("#verify-code-image").attr("src", "/api/createVerify");
	});
	
	//提交注册
	var registerSubmitFlag = false;
	$("#register-submit-email").click(emailRegisterSubmit);
	$("#verify-code, #user-password-mail").keydown(function(e) {
		if (e.keyCode == 13) {
			emailRegisterSubmit();
		}
	})

	function emailRegisterSubmit() {
		var password = $("#user-password-mail").val(),
			_self = $("#register-submit-email"),
			mail = $("#email-input").val(),
		    code = $("#verify-code").val();
		//防止多次提交
		if (registerSubmitFlag) {
			return;
		} else {
			registerSubmitFlag = true;
		}
		
		//检验邮箱的有效性
		if (!regxMail.test(mail)) {
			$(".register-alert-box").text("邮箱格式错误").show();
			registerSubmitFlag = false;
			return;
		}
	
		//检验密码有效性
		if (!regxPassword.test(password)) {
			$(".register-alert-box").text("密码由6-16位英文或数字构成").show();
			registerSubmitFlag = false;
			return;
		}
		
		//检验密码有效性
		if (code == "") {
			$(".register-alert-box").text("验证码不能为空").show();
			registerSubmitFlag = false;
			return;
		}
		
		_self.text("注册中...").addClass("disabled");
		$.post(
			"/register/getRegister/",
			{"account": mail, "password": password, "validCode": code, "type": "pc"},
			function(data) {
				registerSubmitFlag = false;
				if (data.flag == 1) {
					_self.text("立即注册").removeClass("disabled");
					location.replace("/register/sendmail/?rel=register");
				} else if (data.flag == 5){ //验证码已过期
					_self.text("立即注册").removeClass("disabled");
					$(".register-alert-box").text(data.message).show();
				} else {
					_self.text("立即注册").removeClass("disabled");
					$(".register-alert-box").text(data.message).show();
				}
			},
			"json"
		);
	};

	//input元素焦距的时候、警告框隐藏
	$("input").focus(function() {
		$(".register-alert-box").hide();
	});
	
	/**
	 * ================切换邮箱和手机注册=====================
	 */
	$(".register-phone-btn").click(function() {
		if (!$(this).hasClass("selected")) {
			$(this).addClass("selected");
			$(".register-mail-btn").removeClass("selected");
			$("#register-form-mail").hide();
			$("#register-form-phone").show();
		}
	});
	
	$(".register-mail-btn").click(function() {
		if (!$(this).hasClass("selected")) {
			$(this).addClass("selected");
			$(".register-phone-btn").removeClass("selected");
			$("#register-form-phone").hide();
			$("#register-form-mail").show();
		}
	});

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
						$(".register-alert-box").show().text(responce.message);
						$("#send-phone-code").bind("click", sendPhoneCode).text("发送").removeClass("disabled");
					}
				},
				"json"
			);
		} else {
			$(".register-alert-box").text("您的手机号码格式有误！");
			$("#send-phone-code").bind("click", sendPhoneCode).text("发送").removeClass("disabled");
		}
	};

	/**
	 * 绑定验证码发送函数
	 */
	$("#send-phone-code").bind("click", sendPhoneCode);


	//手机号码注册方式按钮绑定
	$("#register-submit-phone").bind("click", registerNextPhone);
	
	
	/**
	 * 提交密码和手机信息、验证码验证有效性、检查密码的正确性
	 */
	function registerNextPhone() {
		var password = $.trim($("#user-password-phone").val()),
			code = $.trim($("#phone-code").val()),
			that = $(this),
			phone = $.trim($("#phone-number").val());				//手机号码

		//检验密码有效性
		if (!regxPassword.test(password)) {
			alertBox.removeClass("hide").text("密码由6-16位英文或数字构成");
			return;
		}

		//检验手机号码有效性
		if (!regxPhone.test(phone)) {
			alertBox.removeClass("hide").text("手机号码格式错误");
			return;
		}
		
		that.text("提交中...").addClass("disabled").unbind("click");

		//验证验证码
		$.post(
			"/register/getRegister/",
			{"mobieCode": code, "account": phone, "password": password},
			function(responce) {
				if (responce.flag == 1) {
					that.text("注册成功").removeClass("disabled");
					location.replace("/login");
				} else {
					that.text("立即注册").removeClass("disabled").bind("click", registerNextPhone);
					$(".register-alert-box").text(responce.message);
				}
			},
			"json"
		);
	}
});