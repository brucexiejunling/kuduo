$(function() {
	//取消尚未完成的二维码
	$("#cancel-btn").on("click", function() {
		$.post(
			"/create/cancelQRCodeEditing/",
			function(data) {
				if (data.flag == 1) {
					$(".create-alert-continue").slideUp();
				} else {
					alert(data.message);
				}
			},
			"json"
		);
	});
	
	//动画效果
	$(".type-heading").hover(function() {
		if (!$(this).hasClass("selected")) {
			TweenMax.to($(this).children(".title"), 0.6, {color: "#000", fontWeight: "bold", paddingLeft: "10px"});
			//TweenMax.to($(this), 0.6, {backgroundColor: "#F4F4F4"});
		}
	}, function() {
		if (!$(this).hasClass("selected")) {
			TweenMax.to($(this).children(".title"), 0.6, {color: "#999", fontWeight: "normal", paddingLeft: "0"});
			//TweenMax.to($(this), 0.6, {backgroundColor: "#FFF"});
		}
	}).click(function() {
		var dataItem = $(this).attr("data-item"),
			  selected = $(this).hasClass("selected");
		
		
		
		//刚点开是没有selected的
		if (!selected) {
			 $(this).addClass("selected");
			 $(".type-heading[data-item!=" + dataItem + "]").removeClass("selected");
			 $(".type-content-wrap[data-for-item!=" + dataItem + "]").hide();
			 TweenMax.to($(".type-heading[data-item!=" + dataItem + "]").children(".title"), 0.6, {color: "#999", fontWeight: "normal", paddingLeft: "0"});
			 $(".type-content-wrap[data-for-item=" + dataItem + "]").show();
			 $.each($(".qr-type-wrap"), function(i, e) {
				 var dataClass = $(e).attr("data-class");
				 if (dataClass.indexOf(dataItem) == -1) {
					 TweenMax.to($(e), 0.5, {width: 0, opacity: 0, onComplete: function() {
						 $(e).hide();
					 }});
				 } else {
					 $(e).show();
					 TweenMax.to($(e), 0.5, {width: "240px", opacity: 1});
				 }
			 });
		} else {
			$(this).removeClass("selected");
			 $(".type-content-wrap[data-for-item=" + dataItem + "]").hide();
			 $(".qr-type-wrap").show();
			 TweenMax.to($(".qr-type-wrap"), 0.5, {width: "240px", opacity: 1});
		}
	});
	
	$(".type-content").hover(function() {
		TweenMax.to($(this), 0.5, {backgroundColor: "#F5F5F5"});
		TweenMax.to($(this).children(".title"), 0.5, {color: "#000", borderWidth: "5px"});
	}, function() {
		TweenMax.to($(this), 0.5, {backgroundColor: "#fff"});
		TweenMax.to($(this).children(".title"), 0.5, {color: "#444", borderWidth: "2px"});
	});
	
	
	
});