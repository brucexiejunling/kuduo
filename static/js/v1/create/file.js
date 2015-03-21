$(function() {	
		//改变文件名
		$("#file-name").click(function() {
			$(this).hide();
			$("#file-name-input").show();
		});
		
		$("#file-name-input").keyup(function() {
			$("#file-name-preview").text($(this).val());
			$("#file-name").text($(this).val());
		}).blur(function() {
			$(this).hide();
			$("#file-name").show();
		});

        /**
        *  ===========================文件描述======================
        */
        var um = UM.getEditor("file-description");
        um.addListener("keyup", function() {
            $("#file-description-preview").html(um.getContent());
        })
	
        /**
        * ========================文件上传者========================
        */
        var userNamePreview = $("#user-name-preview");
        $("#file-user-name").keyup(function() {
            userNamePreview.text($(this).val());
        });

        /*
         * 点击下一步记录
         */
        var fileNameRegx = /^.{1,100}$/,
            userNameRegx = /^.{1,40}$/,
            fileEncryptRegx = /^[a-zA-Z0-9_-]{4,20}$/;

        $(".btn-next-render").click(function() {
	    	//加密
	    	var encrypt = $.trim($("#file-encrypt").val());
	    	if ($(".custom-checkbox").hasClass("checked")) {
	    		if (!fileEncryptRegx.test(encrypt)) {
		    		alert("文件加密密码应由4-20位的英文或数字构成");
		    		return;
		    	}
	    	} else {
	    		encrypt = "";
	    	}

            //上传用户名
            var username = $.trim($("#file-user-name").val());
            if (!userNameRegx.test(username)) {
                alert("上传者用户名长度应在40个字符内");
                return;
            }

            //文件名合法性
            var filename = $.trim($("#file-name-input").val());
            if (!fileNameRegx.test(filename)) {
                alert("文件名长度应在100个字符内");
                return;
            }

            $.post(
                    "/create/save",
                    {
                    type: "file",
                    username: username,
                    filename: filename,
                    description: um.getContent(),
                    encrypt: encrypt
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

        //文件加密开关
        $(".custom-checkbox").on("click", function() {
        	if ($(this).hasClass("checked")) {
        		$(this).removeClass("checked");
        		$("#file-encrypt").val("").hide();
        	} else {
        		$(this).addClass("checked");
        		$("#file-encrypt").show();
        	}
        });
        
        
        /**
         * ======================ajax文件上传==========================
         */
        $("#file-input").bind("change", fileUpload); 
        function fileUpload() {
        	//防止正在上传的时候多次提交
        	$("#file-input").unbind("change", fileUpload);
        	$(".file-upload-wrap").hide();
        	$(".file-upload-done").show();
        	$(".file-reupload-btn").text("上传中...");
        	$.ajaxFileUpload({
        		url: "/create/upload/",
        		data: {uploadType: "create_qrcode_file"},
        		fileElementId: "file-input",
        		secureuri: false,
    			dataType: "json",
    			success: function(data, status) {
    				$("#file-input").bind("change", fileUpload);
    				if (data.response == 1) {
    					 $(".file-reupload-btn").text("重新上传");
    					$(".file-name").text(data.filename).show();
    					$("#file-name-preview").text(data.filename);
                        $("#file-size-preview").text(data.filesize);
    					$("#file-name-input").val(data.filename);
    				} else if(data.response == 0){
    					$(".file-upload-wrap").show();
    					$(".file-upload-done").hide();
    					alert(data.message);
    				}
    			}
        	});
        };
        
        $(".file-reupload-btn").bind("click", function() {
			$(".file-upload-done").hide();
			$(".file-name").hide();
			$("#file-input").val("");
			$(".file-upload-wrap").show();
		});
});