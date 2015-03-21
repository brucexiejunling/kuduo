$(function() {
	
	var powerMode = 1,					//是否使用增强模式
		 regxPassword = /[a-zA-Z0-9\-_]{8,20}/;
	
	
		/**
		 * ==========================百度富文本===========================
		 */
		var um = UM.getEditor("wifi-news-editor");
	
        /**
         * ================输入框更新的时候、右侧wifi的名字、密码都同步预览=====================
         * 此方式只在增强模式有效
         */
        var wifiNamePreview = $("#wifi-name-preview");
        $("#wifi-power-name").keyup(function() {
            wifiNamePreview.text($(this).val());
        });


        var wifiPasswordPreview = $("#wifi-password-preview");
        $("#wifi-power-password").keyup(function() {
            wifiPasswordPreview.text($(this).val());
        });

        var newsPreview = $("#shop-news-preview");
        um.addListener("keyup", function() {
            newsPreview.html(um.getContent());
        });

        var shopNamePreview = $("#shop-name-preview");
        $("#shop-name").keyup(function() {
            shopNamePreview.text($(this).val());
        });



        /**
         * ==================点击下一步记录===========================
         */
        $(".btn-next-render").click(function() {

        	//增强模式
        	if (powerMode == 1) {
        		
        		var name = $.trim($("#wifi-power-name").val()),
		       		  password = $.trim($("#wifi-power-password").val()),
		       		  shopname = $.trim($("#shop-name").val()),
		       		  news = um.getContent();
		       	
		       	if (name == "") {
		       		alert("请输入wifi账号");
		       		return;
		       	}
		
		       	if (!regxPassword.test(password)) {
		       		alert("密码应为8-20位非空字符");
		       		return;
		       	}
		       	
		       	if (shopname == "") {
		       		alert("商铺/组织名不能为空");
		       	}
        		
        		 $.post(
                 		"/create/save/",
                         {type: "wifi",
                           wifiname: name,
                           wifipassword: password,
                           shopname: shopname,
                           news: news,
                           powerMode: powerMode
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
        	} else {
        		var name = $.trim($("#wifi-normal-name").val()),
		       		  password = $.trim($("#wifi-normal-password").val()),
		       		  encrypt = $.trim($("#wifi-encrypt").val());
		       	
		       	if (name == "") {
		       		alert("请输入wifi账号");
		       		return;
		       	}
		
		       	if (!regxPassword.test(password)) {
		       		alert("密码应为8-20位非空字符");
		       		return;
		       	}
		       		
		       	$.post(
                 		"/create/save/",
                         {type: "wifi",
                           wifiname: name,
                           wifipassword: password,
                           wifiencrypt: encrypt,
                           powerMode: powerMode
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
        	}
        });

        /**
         * =======================wifi切换普通模式和增强模式=======================
         */
        $("#power-btn").click(function() {
        	if (!$(this).hasClass("selected")) {
        		$(this).addClass("selected");
        		$("#normal-btn").removeClass("selected");
        		$(".wifi-power-content").show();
        		$(".wifi-normal-content").hide();
        		powerMode = 1;
        		$(".wifi-preview").show();				//只有增强模式才支持预览
        	}
        });
        
        $("#normal-btn").click(function() {
        	if (!$(this).hasClass("selected")) {
        		$(this).addClass("selected");
        		$("#power-btn").removeClass("selected");
        		$(".wifi-normal-content").show();
        		$(".wifi-power-content").hide();
        		powerMode = 0;
        		$(".wifi-preview").hide();
        	}
        });
        
        /**
         * =======================上传店铺Logo=========================
         */
        var uploading = false;
        $("#logo-input").bind("change", uploadLogo);
        function uploadLogo() {
            console.log(222);
        	if (uploading) {
        		return;
        	} else {
        		uploading = true;
        	}
        	var _self  = $(".logo-upload-wrap logo-btn");
        	_self.text("上传中....").addClass("disabled");
        	$.ajaxFileUpload({
        			url: "/create/upload/",
            		data: {uploadType: "wifi_logo"},
            		fileElementId: "logo-input",
            		secureuri: false,
        			dataType: "json",
        			success: function(data, status) {
        				_self.text("选择文件").removeClass("disabled");
        				uploading = false;
        				$("#logo-input").unbind("change", uploadLogo).bind("change", uploadLogo);
        				if (data.response == 1) {
        					$(".logo-upload-wrap").hide();
        					$(".logo-upload-done").show();
                            $(".shop-logo-preview > img").attr("src", data.imagepath);
        				}
        			}
        	});
        }
        
        $(".logo-reupload-btn").click(function() {
        	$(".logo-upload-wrap").show();
        	$(".logo-upload-done").hide();
        })
});