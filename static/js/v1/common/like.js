/**
*====================点赞组件============================
*/
$(function() {
	 $.extend($, {
		 "customLike": function(dom) {
			 var value = parseInt(dom.text(), 10);						//当前点赞数
			 //点赞
			 dom.bind("click", function() {
				 $.post(
						 "/api/like",
						 {code:$(this).attr("code")},
						 function(data) {
							 if (data.code == 100000) {
								 if (dom.hasClass("pressed")) {
									 dom.removeClass("pressed");
									 dom.text(" " + --value);
								 } else {
									 dom.addClass("pressed");
									 dom.text(" " + ++value);
								 }
							 }
						 }
				 );
			 });
		 }
	 });
});

