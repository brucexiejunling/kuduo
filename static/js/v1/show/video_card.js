$(function() {
	//播放视频
	$(".video-wrap .btn-play").click(function() {
		var type = $(this).attr("media-type");
		if (type == "mp4") {
			$("<video class='video-playing' autoplay='true' src='" + $(this).attr("data-video") + "'>加载中...</video>").appendTo($(".video-playing-wrap"));
		}  else {
			$("<div class='video-playing'><audio  autoplay='true' src='" + $(this).attr("data-video") + "'>加载中...</audio></div>").appendTo($(".video-playing-wrap"));		
		}
		$("<a class='video-link' target='_blank' href='" + $(this).attr("data-video") + "'>小提示：若无法播放请点击此链接使用外部播放器</a>").appendTo($(".video-playing-wrap"));
		$(".video-wrap").hide();
		$(".video-playing-wrap").show();
	});	

	$(".video-playing-wrap .close-btn").click(function() {
		$(".video-playing-wrap .video-playing").remove();
		$(".video-playing-wrap .video-link").remove();
		$(".video-playing-wrap").hide();
		$(".video-wrap").show();
	});

	$(".video-playing-wrap .download-btn").click(function() {
		if ($(this).attr("wechat") == 1) {
			alert("微信不允许直接下载文件，请点击右上角使用浏览器打开并下载");
		}
	});
});