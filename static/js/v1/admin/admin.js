$(".qr-bat-generate").click(function(){
	var borderType = $("input[name=qr-border]:checked").val(),
		borderColor = $("#qr-border-color").val(),
		fillType = $("input[name=qr-fill]:checked").val(),
		fillColor = $("#qr-fill-color").val(),
		imageURL = $("#qr-imageURL").val(),
		num = $("#qr-bat-num").val();

	if(borderType == "" || borderColor == "" || fillColor == "" || fillType == "" || num == ""){
		alert("请勿留空!");
		return;
	}

	for(var i = 0; i < num; i ++){
		$.ajax({
			type: 'post',
			url: "/Create/generateQr/",
			data: {
				borderType: borderType,
				borderColor: borderColor,
				fillBlackType: fillType,
				fillWhiteType: fillType,
				fillColor: fillColor,
				imageURL: imageURL,
				num: num,
			},
			success: function(msg){
				var data = msg['data'];
				$("#generate-result").append("<tr><td>"+data['qrId']+"</td><td>"+data['shortCode']+"</td><td>"+data['qrImage']+"</td></tr>");
			},
			dataType: 'json'
		});
	}
	
});

$(".qr-preview").click(function(){
	var borderType = $("input[name=qr-border]:checked").val(),
		borderColor = $("#qr-border-color").val(),
		fillType = $("input[name=qr-fill]:checked").val(),
		fillColor = $("#qr-fill-color").val(),
		imageURL = $("#qr-imageURL").val();

	if(borderType == "" || borderColor == "" || fillColor == "" || fillType == ""){
		alert("请勿留空!");
		return;
	}

	$.ajax({
		type: 'post',
		url: "/Create/previewQrImg/",
		data: {
			borderType: borderType,
			borderColor: borderColor,
			fillBlackType: fillType,
			fillWhiteType: fillType,
			fillColor: fillColor,
			imageURL: imageURL,
		},
		success: function(msg){
			$("#img-preview").attr("src","data:image/jpeg;base64," + msg.data);
		},
		dataType: 'json'
	});
});

$(".short-produce").click(function(){
	var num = $("#short-num").val();
	if(num == "" || num == 0){
		alert("生成数量无效");
		return;
	}
	$.ajax({
		type: 'post',
		url: "./produceShort",
		data: {
			num: num,
		},
		success: function(msg){
			window.location.href = window.location.href;
		},
		dataType: 'json'
	});
});

$(".tooltip-control").hover(function(){
	$(".tooltip-control").tooltip('show');
})

$(".qr-update").click(function(){
	var qr_scan = $("#qr-scan").val(),
		qr_image = $("#qr-image").val(),
		qr_experid = $("#r-experid").val(),
		qr_uid = $("#qr-uid").val(),
		qr_content = editor.getContent(),
		qr_id = $("#qr-id").html();

	$.ajax({
		type: 'post',
		url: "./qrUpdate",
		data: {
			qr_id: qr_id,
			qr_scan: qr_scan,
			qr_image: qr_image,
			qr_experid: qr_experid,
			qr_uid: qr_uid,
			qr_content: qr_content
		},
		success: function(msg){
			if(msg){
				window.location.href = window.location.href;
			}
		},
		dataType: 'json'
	});
})

$(".qr-delete").click(function(){
	var id = this.id.split("-")[1];
	$.ajax({
		type: 'post',
		url: './qrDelete',
		data: {
			qr_id: id,
		},
		success: function(msg){
			if(msg){
				window.location.href = window.location.href;
			}
		},
		dataType: 'json'
	});
});

$(".back").click(function(){
	history.back();
});
