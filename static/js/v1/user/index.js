$(function() {
	/**
	 * ======================删除二维码========================
	 */
	var deleteShortCode = "",
		  deletePanel = "";
	$(".qrcode-delete").click(function() {
		$("#confirm-delete-modal").slideDown();
		deleteShortCode = $(this).attr("shortcode");
		deletePanel = $(this).parents(".qrcode-item");

		$.ajax({
			url: "/user/deleteQRCode/",
			type: "post",
			data: {'shortcode': deleteShortCode},
			timeout: 1000,
			success: function(data) {
				if (data.flag) {
					deletePanel.css({padding: 0})
					TweenLite.to(deletePanel, 1, {width: 0, opacity: 0, top: "-999px", ease:"Power4.easeOut", onComplete: function() {
						deletePanel.remove();
					}});
					deleteShortCode = "";
				} else{
					alert(data.message);
				}
			},
			dataType: "json"
		});
	});
	
	/**
	 * ================备注二维码、添加详情信息======================
	 */
	$(".qrcode-remark").click(function() {
		var parentNode = $(this).parents(".qrcode-item");
			shorturl = parentNode.find(".short-url").val();		//二维码短地址
			
		if (typeof shorturl != undefined && shorturl) {
			$.ajax({
				url: "/user/deleteQrCode/",
				type: "post",
				data: {'shortUrl': shorturl},
				timeout: 1000,
				success: function(data) {
					if (data.flag) {
						parentNode.remove();
					} else{
						alert(data.message);
					}
				},
				error: function() {
					
				},
				dataType: "json"
			});
		}
	});
	
	//显示二维码统计信息
	$(".qrcode-panel .qrcode-image").click(function() {
		var shortcode = $(this).attr("shortcode");		//二维码短地址
		console.log(shortcode);
	});
});
