$(function(){
    /**
     * 立即添加，点击出现类型选择面板。点击任意地方则隐藏面板
     * @param e 事件对象
     */
     $(document).click(function(e) {

        //如果事件的元素为add-conent则显示面板 否则则隐藏面板
        if (e.target.id === "add-content") {
            $("#content-type").show();
        } else {
            $("#content-type").hide();
        }
    });

     
     var textTip = "请登陆后再操作喔，更易管理您的二维码";
     /**
      * 点击跳转到文字上传页面
      * @author 李珠刚
      */
     $("#type-text-label").click(function(e) {
         e.stopPropagation();                       //停止冒泡
         var shortUrl = $("#short-url").val();
         location.href = "/fix/?type=text&q=" + shortUrl;			//后面紧跟短地址
     });
     
     /**
      * 点击跳转到视频上传页面
      * @author 李珠刚
      */
     $("#type-video-label").click(function(e) {
         e.stopPropagation();                       //停止冒泡
  
         var shortUrl = $("#short-url").val();
         location.href = "/fix/?type=video&q=" + shortUrl;
     });
     
     /**
      * 点击跳转到音频上传页面
      * @author 郑钧耀
      */
     $("#type-audio-label").click(function(e) {
         e.stopPropagation();                       //停止冒泡

         var shortUrl = $("#short-url").val();
         location.href = "/fix/?type=audio&q=" + shortUrl;
     });
     
     
     /**
      * 点击跳转到是失物卡贴页面
      * @author 李珠刚
      */
     $("#type-katie-label").click(function(e) {
    	  e.stopPropagation();                       //停止冒泡
         var shortUrl = $("#short-url").val();
         location.href = "/katie";
     });
     
     $("#user-login").click(function() {
    	 var localPage = location.href;
    	 location.href = "http://www.ikuduo.com/login?continue=" + encodeURIComponent(localPage) + "&rel=web_app_upload";
     });
     
     
     
  
});