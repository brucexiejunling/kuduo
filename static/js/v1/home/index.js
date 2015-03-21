$(function() {
	
	// 图片轮播插件
	initSliders()
});


function initSliders() {
	$("ul.slider").responsiveSlides({
    auto: false,
    pager: false,
    nav: true,
    speed: 500
  });
  $('ul.slider li.hide').removeClass('hide');
}