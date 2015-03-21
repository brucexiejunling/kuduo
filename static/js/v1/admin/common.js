$(function() {
	$(".back").click(function() {
		window.history.back();
	});

});

function notify(title, content, time) {
	$("#admin-notification").find(".modal-title").html(title);
	$("#admin-notification").find(".modal-body").html("<p>" + content + "</p>");
	$("#admin-notification").fadeIn(500);
	setTimeout(function() {
		$("#admin-notification").fadeOut(300);
	}, time)
}