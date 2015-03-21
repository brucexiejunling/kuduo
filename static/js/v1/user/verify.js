$(function() {
	var sendFlag = false;
	$("#resend-mail").click(function(){
		if(sendFlag){
			return;
		} else {
			sendFlag = true;
		}
		
		var _self=$(this);
		_self.css("color", "#666").text("发送中...");
		
		$.post(
			"/register/resendRegisterMail",
			function(data){
				sendFlag = false;
				if (data.flag == 1) {
					_self.text("发送成功");
					location.replace("/register/sendmail/?rel=verify");
				} else if (data.flag == 2) {
					_self.text("您的邮箱不存在，请先注册");
				} else if (data.flag == 3) {
					_self.text("发送失败，重新发送");
				}
			}
		);
		
	});
});