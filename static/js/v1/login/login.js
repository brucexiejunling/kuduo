$(function() {
	var regxPhone = /^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/,
		regxMail = /^[a-zA-Z0-9-._]{1,50}@[a-zA-Z0-9-]{1,65}.(com|net|org|info|biz|([a-z]{2,3}.[a-z]{2}))$/i,
		regxPassword = /^[a-zA-Z0-9_]{6,16}$/;

	/**
	 * ==================当手机号码框失去焦点的时候检查号码有效性===================
	 */
	$("#user-account").blur(function() {
		var account = $.trim($("#user-account").val());
			
		if (!regxPhone.test(account) && !regxMail.test(account)) {
			$(".login-alert-box").removeClass("hide").text("账号格式错误");
		}
	});

	/**
	 * ==================当input失去焦点的时候/alert框隐藏======================
	 */
	$("input").focus(function() {
		$(".login-alert-box").text("").addClass("hide");
	});

	/**
	 * =======================登陆函数======================
	 */
	var loginFlag = false;
	$("#get-login").bind("click", function() {
		if (loginFlag) {
			return;
		} else {
			loginFlag = true;
		}
		
		var account = $.trim($("#user-account").val()),
			 password = $.trim($("#user-password").val());
		
		if (!regxPassword.test(password)) {
			$(".login-alert-box").removeClass("hide").text("密码格式错误");
			loginFlag = false;
			return;
		}
	
		if (!regxPhone.test(account) && !regxMail.test(account)) {
			$(".login-alert-box").removeClass("hide").text("账号格式错误");
			loginFlag = false;
			return;
		}
		
		$(this).text("登录中...").addClass("disabled");
		
		$.post(
			"/login/getLogin/",
			{	"account": account,
				"password": password,
				"options": {
					"type": "web_mobile",
					"stayTime": 31
				}
			},
			function(data) {
				loginFlag = false;
				if (data.flag == 1) {
					$("#get-login").text("登录成功").removeClass("disabled");
					var continueValue = "http://www.ikuduo.com"; 
					var urlSearchArray = location.search.slice(1).split("&");
					for (var i = 0; i < urlSearchArray.length; i++) {
						var tmp = urlSearchArray[i].split("=");
						if (tmp[0] == "continue") {
							continueValue = tmp[1];
						}
					}
					if (decodeURIComponent(continueValue).indexOf("?") > -1) {
						location.replace(decodeURIComponent(continueValue) + "&sess=" + data.sess);
					} else {
						location.replace(decodeURIComponent(continueValue) + "?sess=" + data.sess);
					}
					
				} else {
					$(".login-alert-box").removeClass("hide").text(data.message);
					$("#get-login").text("立即登录").removeClass("disabled");
				}
			},
			
			"json"
		)
	});
	
	/**
	 * redirect-url back
	 */
	$("#go-back").click(function(e) {
		e.stopPropagation();
		var continueValue = ""; 
		var urlSearchArray = location.search.slice(1).split("&");
		for (var i = 0; i < urlSearchArray.length; i++) {
			var tmp = urlSearchArray[i].split("=");
			if (tmp[0] == "continue") {
				continueValue = tmp[1];
			}
		}
		location.replace(decodeURIComponent(continueValue));
	});
	
	/**
	 * 前往注册页面
	 */
	$("#go-register").click(function(e) {
		e.stopPropagation();
		var continueValue = ""; 
		var urlSearchArray = location.search.slice(1).split("&");
		for (var i = 0; i < urlSearchArray.length; i++) {
			var tmp = urlSearchArray[i].split("=");
			if (tmp[0] == "continue") {
				continueValue = tmp[1];
			}
		}
		location.replace("/register?continue=" + continueValue + " &rel=web_app_login");
	});
});