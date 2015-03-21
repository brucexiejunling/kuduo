$(function() {
    /**
     * 文本框输入内容的时候右侧同步更新
     */
    $("input.phone-number-input").keyup(function() {
        $('div.phone-preview div.phone-number').text($(this).val());
    });

    /*
     * 点击下一步记录
     */
    $(".btn-next-render").click(function() {
        var phoneNumber = $.trim($('input.phone-number-input').val());
        if(phoneNumber) {
            $.post(
                "/create/save/",
                {"type": "phone", "phoneNumber": phoneNumber},
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