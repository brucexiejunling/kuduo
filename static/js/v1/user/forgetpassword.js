$(function () {
	var regxPhone = /^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/,
		regxMail = /^[a-zA-Z0-9-._]{1,50}@[a-zA-Z0-9-]{1,65}.(com|net|org|info|biz|([a-z]{2,3}.[a-z]{2}))$/i;
	
	//发送密码找回邮件
	var sendEmailFlag = false;
	$("#send-email").click(function(){
		if (sendEmailFlag) {
			return;
		} else {
			sendEmailFlag = true;
		}
		
		var email = $("#email-input").val(),
			code = $(".verify-code-1").val();
		
		//邮箱格式验证
		if (!regxMail.test(email)) {
			$(".custom-alert").removeClass("alert-warning").addClass("alert-danger").text("邮箱格式错误");
			sendEmailFlag = false;
			return;
		}


		if (code == "") {
			$(".custom-alert").removeClass("alert-warning").addClass("alert-danger").text("验证码不能为空");
			sendEmailFlag = false;
			return;
		}	
		
		$(this).text("发送中...").addClass("disabled");
		
		$.post(
			"/user/getPassword/",
			{account: email, code: code},
			function(data) {
				sendEmailFlag =  false;
				$("#send-email").text("发送").removeClass("disabled");
				if (data.flag == 1) {
					$(".content-email-wrap, .content-header").remove();
					$(".email-success-wrap").show();
					$(".email-success-wrap .custom-alert-success-1").show();
				} else {
					$(".custom-alert").removeClass("alert-warning").addClass("alert-danger").text(data.message);
				}
			},
			"json"
		);
	});
	
	//发送手机验证码
	var sendPhoneCodeFlag  = false;
	$("#send-phone-code").click(function() {
		if (sendPhoneCodeFlag) {
			return;
		} else {
			sendPhoneCodeFlag = true;
		}
		
		var phone = $("#phone-input").val();
		if (!regxPhone.test(phone)) {
			$(".custom-alert").removeClass("alert-warning").addClass("alert-danger").text("手机号码格式错误");
			sendPhoneCodeFlag = false;
			return;
		}

		var code = $(".verify-code-2").val();
		if (code == "") {
			$(".custom-alert").removeClass("alert-warning").addClass("alert-danger").text("验证码不能为空");
			sendPhoneCodeFlag = false;
			return;
		}
		
		
		$(this).text("发送中...").addClass("disabled");
		
		$.post(
			"/user/getPassword/",
			{account: phone, code: code},
			function(data) {
				sendPhoneCodeFlag =  false;
				$("#send-phone-code").text("发送").removeClass("disabled");
				if (data.flag == 1) {
					$(".content-phone-wrap, .content-header").remove();
					$(".email-success-wrap").show();
					$(".email-success-wrap .custom-alert-success-2").show();
				} else {
					$(".custom-alert").removeClass("alert-warning").addClass("alert-danger").text(data.message);
				}
			},
			"json"
		);
	});
	
	
	
	//提示词变换
	$("#email-input").focus(function() {
		$(".custom-alert").removeClass("alert-danger").addClass("alert-warning").text("系统将给您的注册邮箱发送一封密码重置邮件");
	});
	
	//提示词变换
	$("#phone-input").focus(function() {
		$(".custom-alert").removeClass("alert-danger").addClass("alert-warning").text("系统将给您手机发送验证码");
	});
	
	
	//切换邮箱和手机号码
	$(".email-btn").click(function() {
		if (!$(this).hasClass("selected")) {
			$(this).addClass("selected");
			$(".phone-btn").removeClass("selected");
			$(".content-email-wrap").show();
			$(".content-phone-wrap").hide();
		}
	});
	$(".phone-btn").click(function() {
		if (!$(this).hasClass("selected")) {
			$(this).addClass("selected");
			$(".email-btn").removeClass("selected");
			$(".content-phone-wrap").show();
			$(".content-email-wrap").hide();
		}
	});

	//更换验证码
	$(".verify-code-image, .verify-code-link").click(function() {
		$(".verify-code-image").attr("src", "/api/createVerify");
	});

	/**
	* 提交发送过来的验证码
	*/
	$("#confirm-code-btn").click(function() {
		var code = $(".confirm-code").val();
		if (!/^\d{4,10}$/.test(code)) {
			alert("验证码格式错误");
			return;
		}

		$.post(
			"/user/verify_reset_pwd_code",
			{resetcode:code},
			function(data) {
				if (data.flag == 1) {
					location.replace("/user/resetpassword");
				} else {
					alert(data.message);
				}
			},
			"json"
		);
	});
});