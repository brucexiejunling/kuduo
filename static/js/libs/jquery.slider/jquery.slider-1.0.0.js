/**
 * @extends jquery1.4.2+
 * @version 1.0.0
 * @date 2014.7.12
 * @site github.com/mugua/jquery.slider
 * @license BDS
 */

(function($) {

	/**
	 * 默认设置
	 */
	var options = {
		startX: 0,						//起始地方
		width: 1000,
		height: 25,
		stepWidth: 100,				//单位步长
		totalWidth: 1000,			//总长度
		prev: "jquery-slider",
	};

	/**
	 * element
	 */
	var lineTotal;
	var lineCompleted;
	var cursor;
	

	var cursorWidth,
		  curLineBarWidth;
	
	var slider = function(setting) {
		options = $.extend(true, options, setting);
		return slider;						
	}

	slider.init = function(ele) {

		$("<div class='" + options.prev + "-cursor'></div><div class='" + options.prev + "-line'></div><div class='" + options.prev + "-line-completed'></div>").appendTo(ele);
	
		cursor = $("." + options.prev + "-cursor");
		lineTotal = $("." + options.prev + "-line");
		lineCompleted = $("." + options.prev + "-line-completed");

		cursor.on({
			mousedown: function(e) {
				var isMouseDown = true,
				      startX = e.pageX,
				      startCSSLeft = parseInt(cursor.css("left"), 10);
				
				$(document).on({
					mouseup: function() {
						isMouseDown = false;
					},
					
					//鼠标移动时候的动作
					mousemove: function(e) {
						if (isMouseDown) {
							var curCSSLeft = parseInt(cursor.css("left"), 10),
								  curX = e.pageX;
							
							if ((curCSSLeft > - cursorWidth / 2 && curCSSLeft < curLineBarWidth - cursorWidth / 2)
									|| (curCSSLeft <= 0 && curX - startX > 0) || (curCSSLeft + cursorWidth >= curLineBarWidth && curX - startX < 0)) {
								cursor.css("left", startCSSLeft + curX - startX);
								lineCompleted.css("width", startCSSLeft + curX - startX)
							}
							
							slider.curValue = Math.floor(curCSSLeft / curLineBarWidth * 10 + 1) * options.stepWidth;
						}
					}
				});
			}
		});

		curLineBarWidth = lineTotal.width();
		cursorWidth = cursor.width();
		
		return slider;
	}	
	
	slider.curValue = 2;
	
	//绑定用户自定义函数
	slider.bind = function(func) {
		$("html").on("mousemove", func);
		return slider;
	}
	
	$.extend({
		slider: slider
	});
})(jQuery);

