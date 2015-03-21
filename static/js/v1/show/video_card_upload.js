$(function() {	
	    
	//用户选择完视频之后名称的改变
	$("#video-input").bind("change", labelChange);
	function labelChange() {
		var name = $(this).val();
		if (name == "") {
			name = "点击选择文件";
		}
		$(".video-input-wrap .video-input-label").text(name);
		$("#video-input").unbind("change", labelChange);
		$("#video-input").bind("change", labelChange);
	}
	
	/**
	 * =====================上传音频的函数========================
	 * uploadVideoTag用来防止多次点击
	 */
	var uploadVideoTag = false;
	$("#upload-btn").click(function uploadVideo() {
		if (uploadVideoTag) {
			return;
		} else {
			uploadVideoTag = true;
		}
	    
	    $(this).addClass("disabled").text("上传中..."); 
	    
	    $.ajaxFileUpload({
	    	url: '/create/upload/',
	    	data: {"uploadType": "show_upload_video"},
	    	fileElementId: "video-input",
	        success: function(data) {   
	        	uploadVideoTag = false;
	        	$("#upload-btn").text("上传").removeClass("disabled");
	        	$("#video-input").bind("change", labelChange);					//这个出现了两次是为了实现改变input框的值时能够响应change事件
	            if(data.response) {
	            	if (data.uploadtype == "mp3") {
	            		$(".video-preview").hide()
	            		$(".audio-preview").show();
	            	} else {
	            		$(".audio-preview").hide()
	            		$(".video-preview").show().attr("src", data.videoimage);	            	
	            	}
	            	$(".upload-section").hide();
	            	$(".video-section").show();
	            	
	            } else {
	                $(".alert-box").show().text(data.message);
	            }
	        },
	        dataType: "json"
	    });
	});
	
	//重新上传视频、先删除上次上传的视频、再显示上传
	$("#reupload-btn").click(function() {
		$(".upload-section").show();
		$(".video-section").hide();
		$("#video-name").val("");
		$(".video-input-wrap .video-input-label").text("点击选择文件");
	});
	
	
	/**
	 * =================点击下一步、提交视频路径、视频名字、视频描述（可选）===========
	 */
	$("#submit-video-btn").click(function() {
		var  videoDescription = $.trim($("#video-description").val());

		if (videoDescription == "") {
			$(".alert-box").show().text("视频描述不能为空");
		}

		var _self = $(this);
		_self.addClass("disabled").text("提交中...");
	
		$.post(
			"/show/video_card_finish",
			{type: "video_card", description:videoDescription},
			function(data) {
				if (data.flag == 0) {
					 $(".alert-box").show().text(data.message);
					 _self.removeClass("disabled").text("重新提交");
				} else if (data.flag == 1) {
					window.location.reload();
				}
			},
			"json"
		);
	});


	//预览
	$(".video-card-preview").click(function() {
		var description = $.trim($("#video-description").val());
		$.post(
			"/show/videoCardPreview",
			{description:description},
			function(data) {
				if (data.flag) {
					location.href = data.redirect;
				} else {
					alert(data.message);
				}
			}
		);
	});
});
