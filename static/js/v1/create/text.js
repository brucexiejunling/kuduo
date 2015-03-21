$(function() {
		var shortUrlActive = 1,				//是否使用活码
			  normalTag = 0;			//是否使用普通文本
			
		
		//百度富文本
        var um = UM.getEditor("text-editor");
        
        /**
         * 文本框输入内容的时候右侧同步更新
         */
        var previewWrap = $("#qr-preview-text-value");
        $("#text-value").keyup(function() {
                previewWrap.text($(this).val());
        });
        
        um.addListener("keyup", function() {
        	previewWrap.html(um.getContent());
        });

        /**
         * 点击下一步记录
         */
        $(".btn-next-render").click(function() {
        		if (normalTag) {
        			var textValue =  $("#text-value").val();
        		} else {
        			var textValue =  um.getContent();
        		}

        		if (textValue == "") {
        			alert("文本内容不能为空");
        			return;
        		}
                $.post(
                        "/create/save/",
                        {"type": "text", "textValue": textValue, "shortUrlActive": shortUrlActive, "normal": normalTag},
                        function(data) {
                            location.href = "/create/modify";
                        },
                        "json"
                );
        });
        
        //活码
        $(".custom-checkbox").on("click", function() {
        	if ($(this).hasClass("checked")) {
        		$(this).removeClass("checked");
        		shortUrlActive = 0;
        	} else {
        		$(this).addClass("checked");
        		shortUrlActive = 1;
        	}
        });
        
        $("#normal-text-btn").on("click", function() {
        	if (!$(this).hasClass("selected")) {
        		shortUrlActive = 1;
        		normalTag = 1;
        		$("#umeditor-text-btn").removeClass("selected");
        		$(this).addClass("selected")
        		 $(".normal-text-wrap").show();
            	 $(".umeditor-text-wrap").hide();
        	}
        	
        });
        
        $("#umeditor-text-btn").on("click", function() {
        	if (!$(this).hasClass("selected")) {
        		shortUrlActive = 1;
        		normalTag = 0;
        		$("#normal-text-btn").removeClass("selected");
        		$(this).addClass("selected")
	        	$(".umeditor-text-wrap").show(); 
	        	$(".normal-text-wrap").hide();
        	}
        });
});