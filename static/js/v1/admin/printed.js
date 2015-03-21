$(function() {
	$(document).delegate(".qr-batch-use", "click", function() {
		var qrId = $(this)[0].id.split("-")[3];

		$.ajax({
			type: 'post',
			url: '/admin/updateStatus',
			data: {
				qrId: qrId,
				status: 0
			},
			dataType: 'json'
		}).done(function (res) {
			notify("提示", res['message'], 2000);
		});
	});

	$(document).delegate(".qr-batch-lock", "click", function() {
		var qrId = $(this)[0].id.split("-")[3];

		$.ajax({
			type: 'post',
			url: '/admin/updateStatus',
			data: {
				qrId: qrId,
				status: 2
			},
			dataType: 'json'
		}).done(function (res) {
			notify("提示", res['message'], 2000);
		});
	});
	
	$(".qr-batch-download").click(function() {
		var codeName = $(this)[0].id.split("-")[3];
		location.href = "/admin/downloadByCodeName?codeName=" + codeName;
	});
});