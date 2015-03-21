$.extend($, {
	/**
	 * 类Android Toast提示框
	 * @param string message
	 * @param JSONstring options
	 * @param int millisecond
	 */
	toast: function(message, customCSS, millisecond) {
		var dom = $("<div></div>");		//新建的dom元素 Toast元素
		
		//初始化
		switch(arguments.length) {
			case 1: customCSS = {};millisecond = 3000;
			case 2: millisecond = 3000;
		}
		
		//默认样式表
		var defaultCSS = {
			display: "block",
			width: "120px",
			height: "auto",
			borderWidth: "0px",
			position: "fixed",
			padding: "5px 8px",
			background: "#444",
			opacity: "0.8",
			color: "#fff",
			borderRadius: "3px",
			textAlign: "center",
			fontSize: "14px",
			fontFamily: "Arial, 微软雅黑, sans-serif",
			zIndex: 9999
		};
		
		//合并css对象
		var resultCSS = $.extend({}, defaultCSS, customCSS);
		
		/**
		 * 设置toast样式 并显示
		 */
		var divPosition = function() {
			var clientWidth = document.body.clientWidth, 		//屏幕宽度
				clientHeight = document.body.clientHeight;	    //屏幕宽度
			
			var domTop = Math.abs(clientHeight - 40) / 2,			//40是随机弄的、减去一个任意高度
				domLeft = Math.abs(clientWidth - parseInt(resultCSS.width, 10)) / 2;
			
			resultCSS.left = domLeft;
			resultCSS.top = domTop;

			dom.css(resultCSS);					   //样式
			dom.html(message);						//内容
			dom.appendTo($("body"));				//还插入到尾部
			
			//两秒后消失
			setTimeout(function() {
				dom.remove();
			}, millisecond);
		};
		
		divPosition();				//调用
	},
	
	
	/**
	 * 分享到第三方平台
	 * @param string $type 分享到的平台类型 
	 * $type的参数类型
	 * ----------weixin  分享到微信朋友圈
	 * ----------weixin2  分享给微信朋友
	 * 
	 * @author 李珠刚
	 */
	SNSShare: function($type) {
		$.toast("<img id='sns-loader-image' src='/static/img/ajax-loader-white.gif' />", {"background": "transparent"}, 10000);
		switch($type) {
			
			//分享到微信朋友圈
			case "weixin":
				$("#sns-loader-image").parent().remove();
				if (typeof WeixinJSBridge == "undefined") {
					$.toast("请先用微信关注【有声二维码】，再通过微信分享到朋友圈");
				} else {
					//在微信开放接口之前只能通过微信本身来分享
					//以后开发出App之后可以使用SDK..但是SDK貌似是需要一定条件的、现在先用这个来分享
					$.toast("请先用微信关注【有声二维码】，再通过微信分享到朋友圈");
				}
			break;
			
			//分享给微信朋友
			case "weixin2":
				$("#sns-loader-image").parent().remove();
				if (typeof WeixinJSBridge == "undefined") {
					$.toast("请先用微信关注【有声二维码】，再通过微信分享给好友");
				} else {
					//在微信开放接口之前只能通过微信本身来分享
					//以后开发出App之后可以使用SDK..但是SDK貌似是需要一定条件的、现在先用这个来分享
					$.toast("请先用微信关注【有声二维码】，再通过微信分享给好友");
				}
			break;
			
			//分享到新浪微博、采用的方式是使用用户的微博发送一条图文微博
			case "sina":
				/*
				 * 发送微博
				 * @param string refer  本页面地址
				 * @param string qr_url_short  短地址
				 * @author 李珠刚
				 */
				$.ajax({
					type: "post",
					url: "/oauth/sendSinaWeibo/",			//本页面地址
					data: {"refer": encodeURIComponent(location.href), "qr_url_short": $.trim($("#short-url").val()), "qr_type": $.trim($("#qr-type").val())},
					success: function(data) {
						$("#sns-loader-image").parent().remove();
						if (data.response == false) {
							$.toast(data.message, {}, 2000);
						} else {
							$.toast(data.message, {}, 2000);
						}
					},
					dataType: "json"
				});
		}
	}
});