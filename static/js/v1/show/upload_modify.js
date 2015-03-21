$(function() {
    /**
     * 功能：返回上一页(即类型选择页面)
     * 细节描述：后台会根据本次功能所处理的卡片类型来重定向、到具体的页面比如 /affix/text
     *           实际上不存在/fix/index/ 这个页面
     */
    $("#go-back").click(function() {
        var type = $("#qr-type").val(),
            shortUrl = $("#short-url").val();

            location.href = "/fix/" + type + "/?q=" + shortUrl;
    });

    /**
     * 功能：控制密保问题的开启和关闭状态
     * 细节描述：开启的时候dom的class会增添 类on 来控制css的渲染
     *           关闭的时候请求后台删除密保的有效状态
     *           前台默认关闭成功
     */
    $("#lock-question-switch").click(function(){     
        var lockStatus = $(this).hasClass("on");           //获取开关是否开启   值由后台决定  点击确认件才能修改后台的值
        console.log(lockStatus);
        if (lockStatus == true) {
            $(this).removeClass("on");
            $("#lock-question-wrap").hide();

            //off的时候取消Lock状态
            $.post(
                "/fix/deleteLock/",
                "json"
            )
        } else {
            $(this).addClass("on");
            $("#lock-question-wrap").show();
        }
    });

    /**
     * 功能：完成密保问题和答案的提交
     * 发送参数：lock_question  问题 （不能为空）
     *           lock_answer   答案 (可为空)
     * 细节描述：这边没有设置超时时间、之后补、第一版先不管了
     * 返回值：后台返回提交是否成功的状态
     */
    $("#lock-submit").click(function(){
        var lockQuestion = $("#lock-question").val(),
            lockAnswer = $("#lock-answer").val();

        if (lockQuestion !== "") {

            $(this).text("提交中...").addClass("disabled");    //改变btn的ui

            $.post(
                "/fix/setLock/",
                {"lock_question": lockQuestion, "lock_answer": lockAnswer},
                function(responce) {
                    if (responce.flag) {
                        $("#lock-alert-box").text(responce.message).show();
                        setTimeout(function() {
                            $("#lock-submit").text("提交").removeClass("disabled");
                            $("#lock-question-wrap").hide();
                            $("#lock-alert-box").text("").hide();
                        }, 600);
                    } else {
                        $("#lock-alert-box").text(responce.message).show();
                        $("#lock-submit").text("提交").removeClass("disabled");
                    }
                },
                "json"
            );
        } else {
            $("#lock-alert-box").text("问题不能为空喔！").show();
        }
    });

    //lock的问题获得焦点的时候alert框隐藏
    $("#lock-question").focus(function(){
        $("#lock-alert-box").text("").hide();
    });


    /**
     * 功能：用户编辑时间的时候、隐藏去之前显示的alert框
     */
    $("#fire-time").focus(function(){
        $("#fire-alert-box").text("").hide();
    });


    /**
     * 功能：提交用户绑定的数据
     * 
     */
    $("#qr-publish").click(function() {
        $(this).text("发布中....").addClass("disabled");
        $.post(
            "/fix/publishQr/",
            {"shortUrl": $("#short-url").val(), "type": $("#qr-type").val()},
            function(data) {
                 if (data.flag === 1) {
                     location.replace("/fix/success/");
                 } else {
                    $("#qr-publish").text(data.message);
                    setTimeout(function(){
                         $("#qr-publish").text("发布").removeClass("disabled");        //一秒后结束提示词
                    }, 1000);
                 }
            },
            "json"
        );
    });

});