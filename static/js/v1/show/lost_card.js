$(function() {
	
	//给物主留言
	var sendFlag = false;
	$("#katie-comment-btn").click(function() {
		if (sendFlag) {
			return;
		} else {
			sendFlag = true;
		}
		
		var comment = $.trim($("#katie-comment-area").val()),
			self = $(this),
			recommentBtn = $("#re-comment-btn");
		
		//留言为空的时候不处理
		if (comment == "") {
			sendFlag = false;
			return;
		}
		
		self.text("发送中").addClass("disabled");
		$.post(
			"/show/sendKatieComment/",
			{"comment": comment},
			function(data) {
				if (data.flag == 1) {
					self.removeClass("disabled").text("发送"); 		//发送按钮恢复
					//60秒后可再次留言
					recommentBtn.addClass("disabled").text("继续留言(60秒)").attr("seconds", 60);

					$("#katie-comment-area").val("");
					$(".comment-send-box").hide();
					$(".comment-finish-box").show();
					
					var timeinterval = setInterval(function(){
							var second = parseInt(recommentBtn.attr("seconds"), 10);
							recommentBtn.text("继续留言(" + second + "秒)");
							recommentBtn.attr("seconds", second - 1);

							if (second == 0) {
								recommentBtn.removeClass("disabled").text("继续留言").bind("click", recomment);
								sendFlag = false;
								clearInterval(timeinterval);
							}
						}, 1000);

					alert(data.message);
				} else {
					self.text("发送").removeClass("disabled");
					sendFlag = false;
					alert(data.message);
				}
			},
			"json"
		);
	});


	//再次发送留言函数
	function recomment() {
		$(".comment-send-box").show();
		$(".comment-finish-box").hide();
		$(this).unbind("click");
	}
});
