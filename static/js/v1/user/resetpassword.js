$(function() {
	$("#reset-btn").click(reset);
	$("repwd-input").keydown(function(e) {
		if (e.keyCode == 13) {
			reset();
		}
	});

	function reset() {
		var password = $.trim($("#password-input").val()),
			repwd = $.trim($("#repwd-input").val());

		if (!QRCODE.regx.password.test(password)) {
			alert("密码应由6-16位英文/数字组成");
			return;
		}

		if (password != repwd) {
			alert("两次密码不相同");
			return;
		}

		$.post(
			"/user/resetpwd",
			{password:password},
			function(data) {
				if (data.flag == 1) {
					location.href = "/login";
				} else {
					alert(data.message);
				}
			},
			"json"
		);
	};

	
});