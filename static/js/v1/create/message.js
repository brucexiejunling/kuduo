$(function() {
	syncEditingContent();
    /*
     * 点击下一步记录
     */
    $(".btn-next-render").click(function() {
        var btnPosition = $(this).attr('id').substring($(this).attr('id').lastIndexOf('-') + 1);
        var phoneNumber = $.trim($('.phone-number-input').val());
        var messageContent = $.trim($('.message-content-input').val());
        var isValid = true, regx = /^[0-9-#\*]{3,20}$/;
        if(!phoneNumber) {
        	isValid = false;
        	alert('请输入手机号码！');
        }
        if(!regx.test(phoneNumber)) {
        	isValid = false;
        	alert('请输入合法的手机号码！');
        }
        if(!messageContent) {
        	isValid = false;
        	alert('请输入短信内容！');
        }
        if(isValid) {
          $.post(
              "/create/save/",
              {"type": "message", "phoneNumber": phoneNumber, messageContent: messageContent},
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
});

function syncEditingContent() {
	$('.phone-number-input').keyup(function() {
		$('div.message-preview .phone-number').text($(this).val());
	});
	$('.message-content-input').keyup(function() {
		$('div.message-preview .message-content').text($(this).val());
	});
}
