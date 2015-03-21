

$(function() {
	/**
	 * =================生成短地址=========================
	 */
	 var creatingFlag = false;
	$("#create-short-url-btn").click(function() {
		var number = $("#short-url-create-number").val(),
			length = $("#short-url-create-length").val();
		
		if (number % 10 != 0) {
			alert("请输入10倍数");
			creatingFlag = false;
			return;
		}

		if (creatingFlag) {
			return;
		} else {
			creatingFlag = true;
		}

		var _self = $(this),
			createNumber = trueNumber = 0
		_self.text("生成中(0)。请勿刷新界面" ).addClass('disabled');

		for (var i = 0; i < number; i += 10) {
			$.post(
				"/admin/produceShortUrl",
				{"number": 10, "length": length},
				function(data) {
					if (data.status) {
						createNumber += 10;
						trueNumber += data.validLength;
						_self.text("生成中(理论生成：" + createNumber + "；实际生成：" + trueNumber + ")。请勿刷新界面");
						if (createNumber == number) {
							_self.text("确认").removeClass('disabled');
							creatingFlag = false;
							alert('生成完毕');
						}
					} else {
						alert("生成失败、请联系系统管理员");
					}
				},
				"json"
			)
		}
		
	});

})
