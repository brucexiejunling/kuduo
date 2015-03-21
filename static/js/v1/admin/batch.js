$(function() {
	/*
	 * ==================初始化左侧面板
	 */
	$(".setting-item[data-bind=basic]").show();
	$(".modify-item-title").click(function() {
		var dataId = $(this).attr("data-id");
		$(".setting-item[data-bind !=" + dataId + "]").hide();
		$(".setting-item[data-bind =" + dataId + "]").show();
		$(".modify-item-title").removeClass("active");
		$(this).addClass("active");
	});
	
	
	/*
	 *================== 初始化crop插件、初始化二维码参数========================
	 */
	var globalValue = {},							//二维码对象、储存参数传递到后台
		  backgroundColor = {},	
		  foregroundColor = {};
	
	globalValue.enhance = true;			//默认增效

	//jcrop初始化
	var jcrop = $.Jcrop(".render-content", {
		aspectRatio : 1,
		allowSelect: false,
		minSize: [100, 100],
		bgOpacity: 0.6,
		setSelect: [0, 0, 400, 400]
	});
	
	/**
	 * =================================图像遮罩层=========================
	 */
	$("#image-preview-mask").click(function() {
		$(this).hide();
		$("#qrcode-text-insert-preview").show();
		$("#logo-image-preview").show();
		$(".jcrop-holder").show();
	});
	
	/**
	 * ================================二维码模式选择=====================
	 * 一种是传统的、一种是类似visulead的
	 */
	$(".qrcode-render-type").click(function() {
		if (!$(this).hasClass("selected")) {
			$(".qrcode-render-type").removeClass("selected");
			$(this).addClass("selected");
			
			var type = $(this).attr("render");
			
			if (type == "enhance") {
				globalValue.enhance = true;
				try {
					_hmt.push(['_trackEvent', "create_modify_qrcode_type", "click", "enhance"]);
				} catch (e) {}
			} else {
				globalValue.enhance = false;
				try {
					_hmt.push(['_trackEvent', "create_modify_qrcode_type", "click", "normal"]);
				} catch (e) {}
			}
		}
	})
	
	/**
	 * =============================切换容错率============================
	 */
	$(".qrcode-error-level > label").click(function() {
		$(".qrcode-error-level > label").removeClass("selected");
		$(this).addClass("selected");
		var level = $(this).find("input[type=radio]").val();
		switch(level) {
			case "L": globalValue['errorLevel'] = "L";break;
			case "M": globalValue['errorLevel'] = "M";break;
			case "Q": globalValue['errorLevel'] = "Q";break;
			case "H": globalValue['errorLevel'] = "H";break;
			default: globalValue['errorLevel'] = "M";
		}
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_error_level", "click", 1]);
		} catch (e) {}
		
		return false;
	});
	
	/**
	 * ==========================二维码背景颜色设置=====================
	 */
	$('#bg-color-select').ColorPicker({
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			try {
				_hmt.push(['_trackEvent', "create_modify_qrcode_bakcground_color", "click", 1]);
			} catch (e) {}
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#bg-color-select > span').css('backgroundColor', '#' + hex);
			$(".render-content").css("backgroundColor", "#" + hex);						//背景颜色
			
			backgroundColor.r = rgb['r'];				//背景颜色改变
			backgroundColor.g = rgb['g'];				//背景颜色改变
			backgroundColor.b = rgb['b'];				//背景颜色改变
			globalValue.backgroundColor = backgroundColor;					//重新赋值
		}
	});
	
	/**
	 * ====================二维码背景选择图库开始===========================
	 */
	
	//弹出窗口
	$("#select-from-image-library").click(function() {
		$("#image-store-mask").show();
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_background_image", "click", 2]);
		} catch (e) {}
	});
	
	//选择图片
	$("#image-store-list").delegate(".image-item-list", "click", function() {
		var src = $(this).attr("src"),		//获取图片地址
			  uploadWrapW = $("#upload-image-wrap").width();
		
		jcrop.destroy();
		
		$(".render-content").css({width: 400,height: 400, left:  (uploadWrapW - 400) / 2}).attr("src", src).show();
		$(".upload-image-wrap").height(400);
		globalValue.backgroundImage = src;
		globalValue.backgroundImageWidth = 400;
		globalValue.backgroundImageHeight = 400;
		globalValue.backgroundImageScale = 1;
		$("#image-store-mask").hide();

		jcrop = $.Jcrop(".render-content", {
			aspectRatio : 1,
			allowSelect: false,
			minSize: [100, 100],
			bgOpacity: 0.6,
			setSelect: [0, 0, 400, 400]
		});
	});
	
	$(".image-store-wrap .close-btn").click(function() {
		$("#image-store-mask").hide();
	});
	
	/*
	 * =================================上传背景图片==================
	 */
	$("#bg-image-src").bind("change", backgroundImageUpload);
	
	function backgroundImageUpload(){
		$(".bg-image-upload-label").hide();
		$(".bg-image-uploading").show();
		$(this).unbind("change");
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_background_image", "click", 1]);
		} catch (e) {}
		$.ajaxFileUpload({
			url: "/admin/upload/",
			data: {"uploadType": "modify_background_image"},
			fileElementId: "bg-image-src",
			secureuri: false,
			dataType: "json",
			success: function(data, status) {
				$(".bg-image-upload-label").text("重新上传").show();
				$(".bg-image-uploading").hide();
				$("#bg-image-src").bind("change", backgroundImageUpload);
				if (data.response) {
					globalValue.backgroundImage = data.imagepath;
					//后台图片显示在前台
					var imgW = data.width,
						  imgH = data.height,			//背景图片的宽高
						  uploadWrapW = $("#upload-image-wrap").width(),
						  uploadWrapH = $("#upload-image-wrap").height();

					jcrop.destroy();
					
					//确保最宽的时候
					if (imgW > uploadWrapW) {
						$(".render-content").css({"width": uploadWrapW, "height": imgH / (imgW / uploadWrapW), "left": 0}).attr("src", "/" + data.imagepath);
					} else {
						$(".render-content").css({"width": imgW, "height": imgH, "left": (uploadWrapW - imgW) / 2}).attr("src", "/" + data.imagepath);
					}
				
					$(".upload-image-wrap").height($(".render-content").height());			//未知bug

					globalValue.backgroundImageWidth = imgW;
					globalValue.backgroundImageHeight = imgH; 
					globalValue.backgroundImageScale = imgW > uploadWrapW ? imgW / uploadWrapW : 1;
					
					$("#image-preview-mask").trigger("click");			//
					
					jcrop = $.Jcrop(".render-content", {
						aspectRatio : 1,
						allowSelect: false,
						minSize: [100, 100],
						bgOpacity: 0.6,
						setSelect: [0, 0, 400, 400]
					});
				}
			}
		});
	}
	
	/*
	 * ============================二维码背景重置========================
	 */
	$("#bg-image-reset").click(function() {
		
		//颜色重置
		$('#bg-color-select > span').css('backgroundColor', '#fff');
		$(".render-content").css("backgroundColor", "#fff");						//背景颜色
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_background_reset", "click", 1]);
		} catch (e) {}
		backgroundColor.r = 255;				//背景颜色改变
		backgroundColor.g = 255;				//背景颜色改变
		backgroundColor.b = 255;				//背景颜色改变
		globalValue.backgroundColor = backgroundColor;					//重新赋值
		
		//图片重置
		jcrop.destroy();
		
		$(".render-content").css({"width": 400, "height": 400, "left": 170}).attr("src", "/static/img/default_bg_1.png");
		$("#image-preview-mask").hide();
	
		$(".upload-image-wrap").height(400);			//未知bug

		globalValue.backgroundImage = null;
		jcrop = $.Jcrop(".render-content", {
			aspectRatio : 1,
			allowSelect: false,
			minSize: [100, 100],
			bgOpacity: 0.6,
			setSelect: [0, 0, 400, 400]
		});
	});
	
	/*
	 * ============================二维码前景色设置=====================
	 */
	$('#front-color-select').ColorPicker({
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			try {
				_hmt.push(['_trackEvent', "create_modify_frontground", "click", 1]);
			} catch (e) {}
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#front-color-select > span').css('backgroundColor', '#' + hex);
			
			foregroundColor.r = rgb['r'];				//背景颜色改变
			foregroundColor.g = rgb['g'];				//背景颜色改变
			foregroundColor.b = rgb['b'];				//背景颜色改变
			globalValue.foregroundColor = foregroundColor;						//重新赋值
		}
	});
	
	/*
	 * =============================选择二维码边距==========================
	 */
	$("#qrcode-margin").change(function() {
		globalValue.qrcodeMargin = $(this).val();
		$("#code-eye-image").css("padding", $(this).val() * 10 - 10);
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_margin", "click", globalValue.qrcodeMargin]);
		} catch (e) {}
	});
	
	
	/*
	 * ==============================二维码尺寸选择===================================
	 */
	/**
	 * jquery-slider.1.0.0
	 * Margin
	 */
	var slider = $.slider({
		startX: 100,
		stepWidth: 100,
		totalWidth: 1000
	}).init($("#qrcode-size")).bind(sliderMoveFunc);
	
	
	//当游标移动时候执行的函数
	function sliderMoveFunc() {
		$(".qrcode-size-current").text(slider.curValue);
	}
	
	/*
	 * ===================码眼颜色设置===========================
	 */
	$('#code-eye-color-select').ColorPicker({
		color: 'rgb(0, 0, 0)',
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			try {
				_hmt.push(['_trackEvent', "create_modify_qrcode_codeeye_color", "click", 1]);
			} catch (e) {}
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#code-eye-color-select > span').css('backgroundColor', '#' + hex);
			
			var codeEyeColor = {};
			codeEyeColor.r = rgb['r'];
			codeEyeColor.g = rgb['g'];
			codeEyeColor.b = rgb['b'];
			globalValue.codeEyeColor = codeEyeColor;
		}
	});

	/*
	 * ================= ==选择码眼类型===========================
	 */
	$(".code-eye-type-item").on("click", function() {
		$(".code-eye-type-item").removeClass("selected");
		$(this).addClass("selected");
		var type = $(this).text();
		switch(type) {
		case "square":
			$("#code-eye-image").attr("src", "/static/img/code_eye_square.png");
			globalValue.codeEyeType = type;
			break;
		case "rounded":
			$("#code-eye-image").attr("src", "/static/img/code_eye_rounded.png");
			globalValue.codeEyeType = type;
			break;
		default:
			alert("眼码类型错误，请重新选择");
		}
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_codeeye_type", "click", type]);
		} catch (e) {}
		
	});
	
	/*
	 * =======================插入文字=============================
	 */
	$("#qrcode-text-insert").keyup(function() {
		var value = $(this).val();
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_text_insert", "click", 1]);
		} catch (e) {}
		if (value != "") {
			$(".qrcode-text-insert-preview").css({
				"top": $(".upload-image-wrap").height() / 2,
				"left": $(".upload-image-wrap").width() / 2,
			}).text(value).show();
			globalValue.insertText = 1;				//启用插入文字
			globalValue.insertTextValue = value;			//要插入的文字
			globalValue.insertTextPos = {};
			globalValue.insertTextPos.x = $(".upload-image-wrap").width() / 2 - parseInt($(".render-content").css("left"), 10);
			globalValue.insertTextPos.y = $(".upload-image-wrap").height() / 2
		} else {
			$(".qrcode-text-insert-preview").text(value).hide();
			globalValue.insertText = 0;
		}
		
		globalValue.insertTextSize = 14;
		
	});
	
	
	
	
	//===================移动文字位置=======================
	$("#qrcode-text-insert-preview").mousedown(function(e) {
		var mousePosX,
	  	  	mousePosY,
	  	  	previewDOM = $(this),
	  	    curLeft = parseInt(previewDOM.css("left"), 10),
			curTop = parseInt(previewDOM.css("top"), 10),					//当前文字的位置
	  	  	mouseDownFlag = true;				//标记鼠标是否按下
		
		mousePosX = e.pageX,
  		mousePosY = e.pageY;
		
		$(document).bind({
			mouseup: function() {
				mouseDownFlag = false;
			},
			mousemove: function(e) {
				if (mouseDownFlag) {
					var curX = e.pageX,
						  curY = e.pageY;
				
					//左右平移控制
					if ((parseInt(previewDOM.css("left"), 10) - parseInt($(".render-content").css("left"), 10) > 0
								&& previewDOM.width() + parseInt(previewDOM.css("left"), 10)  - parseInt($(".render-content").css("left"), 10)< $(".render-content").width())
						|| (parseInt(previewDOM.css("left"), 10) - parseInt($(".render-content").css("left"), 10) <= 0 && curX - mousePosX > 0)
						|| (previewDOM.width() + parseInt(previewDOM.css("left"), 10) >= $(".render-content").width() && curX - mousePosX < 0)) {
						//改变文字位置
						previewDOM.css({
							left: curLeft + curX - mousePosX
						});			
					} 

					if ((parseInt(previewDOM.css("top"), 10) - parseInt($(".render-content").css("top"), 10) > 0
							&& previewDOM.height() + parseInt(previewDOM.css("top"), 10)  - parseInt($(".render-content").css("top"), 10)< $(".render-content").height())
							|| (parseInt(previewDOM.css("top"), 10) - parseInt($(".render-content").css("top"), 10) <= 0 && curY - mousePosY > 0)
							|| (previewDOM.height() + parseInt(previewDOM.css("top"), 10) >= $(".render-content").height() && curY - mousePosY < 0)) {
						//改变文字位置
						previewDOM.css({
							top: curTop + curY - mousePosY,
						});	
					}
					
					//左边的空余快得减去才是二维码图片实际的位置
					globalValue.insertTextPos.x = curLeft + curX - mousePosX - parseInt($(".render-content").css("left"), 10);
					globalValue.insertTextPos.y = curTop + curY - mousePosY;				
				}
			}
		});	
		return false;
	});
	
	/*
	 * =====================文字颜色、字体加粗、字号选择、文字背景========================
	 */
	
	//加粗
	$(".text-insert-font-style").click(function() {
		if (globalValue.insertTextBold == 1) {
			globalValue.insertTextBold = 0;
			$(this).removeClass("bold");
			$(".qrcode-text-insert-preview").css("font-weight", "normal");
		} else {
			globalValue.insertTextBold = 1;
			$(this).addClass("bold");
			$(".qrcode-text-insert-preview").css("font-weight", "bold");
			try {
				_hmt.push(['_trackEvent', "create_modify_qrcode_text_bold", "click", 1]);
			}catch (e) {}
		}

		
	});
	
	//文字颜色
	$(".text-insert-font-color").ColorPicker({
			onShow: function (colpkr) {
				$(colpkr).fadeIn(500);
				try {
					_hmt.push(['_trackEvent', "create_modify_qrcode_text_color", "click", 1]);
				}catch (e) {}
				return false;
			},
			onHide: function (colpkr) {
				$(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				$('.text-insert-font-color > span').css('backgroundColor', '#' + hex);
				$(".qrcode-text-insert-preview").css("color", "#" + hex);
				var insertTextColor = {};
				
				insertTextColor.r = rgb['r'];
				insertTextColor.g = rgb['g'];
				insertTextColor.b = rgb['b'];
				
				globalValue.insertTextColor = insertTextColor;
			}
	});
	
	//文字背景色
	$(".text-insert-background-color").ColorPicker({
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			try {
				_hmt.push(['_trackEvent', "create_modify_qrcode_text_background_color", "click", 1]);
			}catch (e) {}
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$(".qrcode-text-insert-preview").css("background-color", "#" + hex);
			var insertTextBackgroundColor = {};
			
			insertTextBackgroundColor.r = rgb['r'];
			insertTextBackgroundColor.g = rgb['g'];
			insertTextBackgroundColor.b = rgb['b'];
			
			globalValue.insertTextBackgroundColor = insertTextBackgroundColor;
		}
	});

	//文字字体
	$(".text-insert-font-family").click(function() {
		globalValue.insertTextFontFamily = "#000";
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_text_family", "click", 1]);
		}catch (e) {}
		$(".qrcode-text-insert-preview").css("font-family", globalValue.insertTextFontFamily);
	});
	
	//文字大小
	$(".text-insert-font-size").change(function() {
		globalValue.insertTextSize = $(this).val();
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_text_size", "click", 1]);
		}catch (e) {}
		$(".qrcode-text-insert-preview").css("font-size", globalValue.insertTextSize + "px");
	});
	
	
	/*
	 * ================================上传Logo===================================
	 */
	
	$("#logo-upload-src").bind("change", logoImageUpload);
	
	function logoImageUpload(){
		$(".logo-upload-label").hide();
		$(".logo-uploading").show();
		$(this).unbind("change");
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_logo", "click", 1]);
		}catch (e) {}
		$.ajaxFileUpload({
			url: "/admin/upload/",
			fileElementId: "logo-upload-src",
			data: {"uploadType": "modify_logo_image"},
			secureuri: false,
			dataType: "json",
			success: function(data, status) {
				$("#logo-upload-src").bind("change", logoImageUpload);
				$(".logo-upload-label").text("重新上传").show();
				$(".logo-uploading").hide();
				
				if (data.response == 1) {
					globalValue.logoImage = data.imagepath;
					globalValue.logoImagePos = {};
					globalValue.logoImagePos.y =  ($(".upload-image-wrap").height() - data.height) / 2 ;
					globalValue.logoImagePos.x =  ($(".upload-image-wrap").width() -data.width) / 2 - parseInt($(".render-content").css("left"), 10);
					globalValue.logoImageSize = {};
					globalValue.logoImageSize.width = data.width;
					globalValue.logoImageSize.height = data.height;
					
					
					$(".logo-image-preview").css({
						"top": ($(".upload-image-wrap").height() - data.height) / 2,
						"left": ($(".upload-image-wrap").width() -data.width) / 2,
					}).attr("src", data.imagepath).show();
					
					jcrop.destroy();					
					jcrop = $.Jcrop(".render-content", {
						aspectRatio : 1,
						allowSelect: false,
						minSize: [100, 100],
						bgOpacity: 0.6,
						setSelect: [0, 0, 400, 400]
					});
				} else {
					alert(data.message);
				}	
			}
		});
	}
	
	//===================移动Logo位置=======================
	$("#logo-image-preview").mousedown(function(e) {
		var mousePosX,
	  	  	mousePosY,
	  	  	previewDOM = $(this),
	  	    curLeft = parseInt(previewDOM.css("left"), 10),
			curTop = parseInt(previewDOM.css("top"), 10),					//当前文字的位置
	  	  	mouseDownFlag = true;				//标记鼠标是否按下
		
		mousePosX = e.pageX,
  		mousePosY = e.pageY;
		
		$(document).bind({
			mouseup: function() {
				mouseDownFlag = false;
			},
			mousemove: function(e) {
				if (mouseDownFlag) {
					var curX = e.pageX,
						  curY = e.pageY;
				
					//左右平移控制
					if ((parseInt(previewDOM.css("left"), 10) - parseInt($(".render-content").css("left"), 10) > 0
								&& previewDOM.width() + parseInt(previewDOM.css("left"), 10)  - parseInt($(".render-content").css("left"), 10)< $(".render-content").width())
						|| (parseInt(previewDOM.css("left"), 10) - parseInt($(".render-content").css("left"), 10) <= 0 && curX - mousePosX > 0)
						|| (previewDOM.width() + parseInt(previewDOM.css("left"), 10) >= $(".render-content").width() && curX - mousePosX < 0)) {
						//改变文字位置
						previewDOM.css({
							left: curLeft + curX - mousePosX
						});			
					} 

					if ((parseInt(previewDOM.css("top"), 10) - parseInt($(".render-content").css("top"), 10) > 0
							&& previewDOM.height() + parseInt(previewDOM.css("top"), 10)  - parseInt($(".render-content").css("top"), 10)< $(".render-content").height())
							|| (parseInt(previewDOM.css("top"), 10) - parseInt($(".render-content").css("top"), 10) <= 0 && curY - mousePosY > 0)
							|| (previewDOM.height() + parseInt(previewDOM.css("top"), 10) >= $(".render-content").height() && curY - mousePosY < 0)) {
						//改变文字位置
						previewDOM.css({
							top: curTop + curY - mousePosY,
						});	
					}
					//左边的空余快得减去才是二维码图片实际的位置
					globalValue.logoImagePos.x = curLeft + curX - mousePosX - parseInt($(".render-content").css("left"), 10);
					globalValue.logoImagePos.y = curTop + curY - mousePosY;				
				}
			}
		});	
		return false;
	});
	
	/**
	 * ==================== Logo重置====================== 
	 */
	$("#logo-upload-reset").click(function() {
		if (globalValue.logoImage != null && globalValue.logoImage != "") {
			globalValue.logoImage = null;
			$("#btn-preview").trigger("click");
		}
	});
	
	/**
	 * ==========================二维码渐变颜色设置=====================
	 */
	$('#gradient-color').ColorPicker({
		onShow: function (colpkr) {
			$(colpkr).fadeIn(500);
			try {
				_hmt.push(['_trackEvent', "create_modify_qrcode_color_gradient", "click", 1]);
			}catch (e) {}
			return false;
		},
		onHide: function (colpkr) {
			$(colpkr).fadeOut(500);
			return false;
		},
		onChange: function (hsb, hex, rgb) {
			$('#gradient-color > span').css('backgroundColor', '#' + hex);
			var gradientColor = {};
			
			gradientColor.r = rgb['r'];				//背景颜色改变
			gradientColor.g = rgb['g'];				//背景颜色改变
			gradientColor.b = rgb['b'];				//背景颜色改变
			globalValue.gradientColor = gradientColor;					//重新赋值
		}
	});
	
	/**
	 * ===========================二维码渐变类型设置============================
	 */
	$("#gradient-direction").change(function() {
		var type = $(this).val();
		globalValue.gradientType = type;
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_color_gradient_type", "click", 1]);
		}catch (e) {}
	});
	
	/**
	* ===============隐藏登陆页面的input框==================
	*/
	$(".create-login-wrap input").focus(function() {
		$(".create-login-wrap .alert-box").hide();
	})

	/**
	 * ================用户登录=====================
	 */
	var loginFlag = false;
	$("#create-login-btn").click(createLogin);
	$("#user-password").on("keyup", function(e) {
		if (e.keyCode == 13) {
			createLogin();
		}
	});
	
	function createLogin() {
		if (loginFlag) {
			return;
		} else {
			loginFlag = true;
		}
		
		var account = $("#user-account").val(),
			  password = $("#user-password").val(),
			  _self = $("#create-login-btn");
	
		if (!QRCODE.regx.phone.test(account) && !QRCODE.regx.mail.test(account)) {
			$(".create-login-wrap .alert-box").text("账号格式错误。").show();
			loginFlag = false;
			return;
		}
		
		_self.text("登录中...").addClass("disabled");
		
		$.post(
			"/login/getLogin/",
			{"account": account, 
			  "password": password,
			  "options": {
				  "type": "pc",
				  "stayTime": 7
			  }
			},
			function(data) {
				loginFlag = false;
				if (data.flag == 1) {
					_self.text("登录成功").removeClass("disabled");
					$(".create-login-mask").hide();
				} else {
					$(".create-login-wrap .alert-box").text(data.message).show();
					_self.text("立即登录").removeClass("disabled");
				}
			},
			"json"
		);
	}


	$(".create-login-wrap .close-btn").click(function() {
		$(".create-login-mask").hide();
	});

	//微博登陆
	$(".create-login-wrap .social-weibo-btn").click(function() {
		try {
			_hmt.push(['_trackEvent', "create_modify_login_weibo", "click", 1]);
		}catch (e) {}
		var link = $(this).attr("social-link");
		var win = window.open(link, '_blank', 'toolbar=no,scrollbar=yes,top=100,left=100,width=300,height=200');
		$(".create-login-mask").hide();
	});



	/********************  后台功能  ********************/
	/**
	 * ================================高级模式选择=====================
	 * 一种是传统的、一种是类似visulead的
	 */
	$(".qrcode-advanced-type").click(function() {
		if (!$(this).hasClass("selected")) {
			$(".qrcode-advanced-type").removeClass("selected");
			$(this).addClass("selected");

			var type = $(this).attr("advanced-render");
			
			globalValue.material = false;
			globalValue.bginsert = false;

			if (type == "materialize") {
				globalValue.material = true;
			} else if (type == "bginsert") {
				globalValue.bginsert = true;
			}
		} else {
			$(".qrcode-advanced-type").removeClass("selected");
			globalValue.material = false;
			globalValue.bginsert = false;
		}
	});


	/*
	 * =================================上传图案==================
	 */
	$("#material-image-src").bind("change", materialImageUpload);

	function materialImageUpload(){
		$(".material-image-upload-label").hide();
		$(".material-image-uploading").show();
		$(this).unbind("change");
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_background_image", "click", 1]);
		} catch (e) {}
		$.ajaxFileUpload({
			url: "/admin/upload/",
			data: {"uploadType": "modify_background_image"},
			fileElementId: "material-image-src",
			secureuri: false,
			dataType: "json",
			success: function(data, status) {
				$(".material-image-upload-label").text("重新上传").show();
				$(".material-image-uploading").hide();
				$("#material-image-src").bind("change", materialImageUpload);
				if (data.response) {
					globalValue.materialImage = data.imagepath;
					var imgW = data.width,
						  imgH = data.height;

				}
			}
		});
	}


	/*
	 * ==============================二维码预览===============================
	 */
	$("#btn-preview").click(function() {
		var jcropRange = jcrop.tellSelect();
		globalValue.posX = jcropRange.x;
		globalValue.posY = jcropRange.y;
		globalValue.qrWidth = jcropRange.w;
		globalValue.qrHeight = jcropRange.h;
		try {
			_hmt.push(['_trackEvent', "create_modify_qrcode_preview", "click", 1]);
		} catch (e) {}
		
		$(".loading-mask").show();
		$.post(
			"/admin/preview/",
			{
				"qrCodeValue": "空白内容待添加",
				"globalValue": globalValue,
			},
			function(data) {
				$(".loading-mask").hide();
				$("#image-preview-mask").trigger("click");			
				$(".jcrop-holder").hide();
				var left = $(".render-content").css("left");
				if (globalValue['backgroundImage']) {
					$("#image-preview-mask").attr("src", data.src + "?v=" + Math.random()).css({left: left, width: globalValue.backgroundImageWidth / globalValue.backgroundImageScale}).show();
					$("#qrcode-text-insert-preview").hide();
					$("#logo-image-preview").hide();
				} else {
					$("#image-preview-mask").attr("src", data.src + "?v=" + Math.random()).css({left: left, width: 400}).show();
					$("#qrcode-text-insert-preview").hide();
					$("#logo-image-preview").hide();
				}
			},
			"json"
		);
	});


	/**
	 * ============================二维码完成================================
	 */
	 var creatingFlag = false;
	$("#btn-batch").click(function() {

		if (creatingFlag) {
			return;
		} else {
			creatingFlag = true;
		}

		var amount = $("#batch-amount").val();
		// 批量生成数量不能为0
		if (amount == "") {
			creatingFlag = false;
			alert("请输入数量");
			return ;
		}

		var type = $("#qr-batch-type").val();
		if (type != "lost_card" && type != "video_card") {
			creatingFlag = false;
			alert("请选择类型");
			return;
		}

		var codeName = $("#batch-codename").val();

		if (codeName == "") {
			creatingFlag = false;
			alert("请输入该批次的代号");
			return;
		}

		var jcropRange = jcrop.tellSelect();
		globalValue.posX = jcropRange.x;
		globalValue.posY = jcropRange.y;
		globalValue.qrWidth = jcropRange.w;
		globalValue.qrHeight = jcropRange.h;

		var _self = $(this),
			createNumber = 0;		

		_self.addClass('disabled').text("生成中(0)。请勿刷新界面。");
		//通过前端的for循环来执行
		for (var i = 0; i < amount; i++) {
			$.post(
				"/admin/batchGenerate/",
				{
					"code_name": codeName,
					"batchAmount": 1,
					"type": type,
					"globalValue": globalValue,
				},
				function(res) {
					if (res.status == 1) {
						createNumber++;
						_self.text("生成中(" + createNumber + ")。请勿刷新界面。");
						if (createNumber >= amount) {
							creatingFlag = false;
							_self.text("批量生成").removeClass("disabled");
							alert("生成完毕");
						}
					} else {
						_self.text(res.message).removeClass("disabled");
					}
				},
				"json"
			);
		}
		
	});

});