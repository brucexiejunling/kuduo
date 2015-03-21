$(function() {
	//视频简介、预览
	var um = UM.getEditor("video-des-editor");
	var videodesPreview = $(".video-des-preview");
	um.addListener("keyup", function() {
		videodesPreview.html(um.getContent());
	});
	
	//上传者预览
	var usernamePreview = $(".username-preview");
	$(".video-user").keyup(function() {
		usernamePreview.text($(this).val());
	});
	
	//视频名称预览
	var videonamePreview = $(".video-name-preview");
	$(".video-name").keyup(function() {
		videonamePreview.text($(this).val());
	});

	//视频文件上传
	$("#video-input").bind("change", uploadVideo);
	var uploading = false;
	function uploadVideo() {
		if (uploading) {
			return;
		} else {
			uploading = true;
		}
		var _self = $('.video-upload-wrap .video-btn');
		_self.text("上传中...").addClass("disabled");

		$.ajaxFileUpload({
			url: "/create/upload/",
    		data: {uploadType: "create_video"},
    		fileElementId: "video-input",
    		secureuri: false,
			dataType: "json",
			success: function(data, status) {
				_self.text("选择文件").removeClass("disabled");
				uploading = false;
				$("#video-input").unbind("change", uploadVideo).bind("change", uploadVideo);
				if (data.response == 1) {
					$(".video-upload-wrap").hide();
					$(".video-upload-done").show();
                    $(".video-source-preview").attr("src", data.videopath);
                    $(".video-name-preview").text(data.videoname);
                    $("#video-name").val(data.videoname);
                    $(".video-size-preview").text(data.videosize);
                    if ($(".video-image-preview").length == 0) {
                    	 $("<img src='" + data.videoimage + "' class='video-image-preview' />").appendTo($(".qr-preview"));
                    } else {
                    	$(".video-image-preview").attr("src", data.videoimage);
                    }
                   
				} else {
					alert(data.message);
				}
			}
		});
	}
	
	/*
     * =====================点击下一步记录信息========================
     */
     var videonameRegx = /^[\S\s]{1,100}$/,
         userNameRegx = /^[\S\s]{1,40}$/;

    $(".btn-next-render").click(function() {
    	var videoname = $.trim($("#video-name").val()),
    		username = $.trim($("#video-user").val()),
    		videodes = um.getContent();

    	if (!videonameRegx.test(videoname)) {
    		alert("视频名字中含有非法字符");
    		return;
    	}

    	if (!userNameRegx.test(username)) {
    		alert("上传者名称中含有非法字符");
    		return;
    	}

        $.post(
    		"/create/save/",
            {
            	type: "video",
            	videoname: videoname,
            	username: username,
            	videodes: videodes
            },
            function(data) {
            	if (data.status == 1) {
            		location.href = "/create/modify";	
            	} else {
            		alert(data.message);
            	}
            },
            "json"
        );
    });


    $(".video-reupload-btn").click(function() {
    	$(".video-upload-done").hide();
    	$(".video-upload-wrap").show();
    });
})