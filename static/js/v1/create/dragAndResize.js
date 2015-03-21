(function($) {
	$.fn.extend({'dragAndResize' : function(options) {
		var boundary = this.parent();
		var defaults = {
			boundary: boundary,
			minWidth: 50,
			maxWidth: 300
		};

		$.extend(defaults, options);

		return this.each(function() {
			var hasInit = false;
			var $elem, resizeMode, canResize, direction,
			    dragMode, canDrag, hasSetBottom, hasSetTop, 
			    hasSetRight, hasSetLeft, prePageX, prePageY,
			    borderWidth = 0, rate;
			var boundaryWidth = defaults.boundary.width(),
					boundaryHeight = defaults.boundary.height(),
					boundaryLeft = parseInt(defaults.boundary.css('left'), 10);
					boundaryTop = parseInt(defaults.boundary.css('top'), 10);
			var original = {};

			$elem = $(this);
			borderWidth = parseInt($elem.css('border-width'));
			resizeMode = false, direction = '';
			hasSetBottom = hasSetRight = false, hasSetLeft = hasSetTop = true;
			canResize = false;

			var init = function() {
				rate = $elem.width() / $elem.height();
				original.top = parseInt($elem.css('top'), 10);
				original.left = parseInt($elem.css('left'), 10);
				original.width = $elem.width() + 2 * borderWidth;
				original.height = $elem.height() + 2 * borderWidth;
			}

			var setTop = function() {
				if(hasSetBottom) {
					borderWidth = parseInt($elem.css('border-width'));
					$elem.css('top', $elem.parent().height() - 2 * borderWidth - parseInt($elem.css('bottom'), 10) - $elem.height()).css('bottom', '');
					hasSetTop = true;
					hasSetBottom = false;
				}
			};

			var setLeft = function() {
				if(hasSetRight) {
					borderWidth = parseInt($elem.css('border-width'));
					$elem.css('left', $elem.parent().width() - 2 * borderWidth - parseInt($elem.css('right'), 10) - $elem.width()).css('right', '');
					hasSetLeft = true;
					hasSetRight = false;
				}
			};

			var setBottom = function() {
				if(hasSetTop) {
					borderWidth = parseInt($elem.css('border-width'));
					$elem.css('bottom', $elem.parent().height() - 2 * borderWidth - $elem.position().top - $elem.height()).css('top', '');
					hasSetBottom = true;
					hasSetTop = false;
				}
			};

			var setRight = function() {
				if(hasSetLeft) {
					borderWidth = parseInt($elem.css('border-width'));
					$elem.css('right', $elem.parent().width() - 2 * borderWidth - $elem.position().left - $elem.width()).css('left', '');
					hasSetRight = true;
					hasSetLeft = false;
				}
			};

			var mouseMoveOnElem = function(event) {
				var width = $elem.width(), height = $elem.height();
				var offsetY = event.offsetY, offsetX = event.offsetX;
				if(offsetY > height - 10 && offsetX > width - 10) {
					$elem.css('cursor', 'se-resize');
					resizeMode = true;
					direction = 'se';

					setTop();
					setLeft();

				} else if(offsetX > width - 10 && offsetY < 10) {
					$elem.css('cursor', 'ne-resize');
					resizeMode = true;
					direction = 'ne';

					setBottom();
					setLeft();

				} else if(offsetX < 10 && offsetY < 10) {
					$elem.css('cursor', 'nw-resize');
					resizeMode = true;
					direction = 'nw';

					setBottom();
					setRight();

				} else if(offsetX < 10 && offsetY > height - 10) {
					$elem.css('cursor', 'sw-resize');
					resizeMode = true;
					direction = 'sw';

					setTop();
					setRight();

				} else {
					dragMode = true;
					resizeMode = false;
					direction = '';
					$elem.css('cursor', 'move'); 
				} 
			};

			var curLeft, curTop;					//当前文字的位置
		
			var mouseMoveOnDocument = function(event) {
				if(canResize) {
					var deltaPageX = event.pageX - prePageX;
					var deltaPageY = event.pageY - prePageY;
					var toLeft = deltaPageX < 0, toTop = deltaPageY < 0;
					prePageX = event.pageX;
					prePageY = event.pageY;

					setTop();
					setLeft();

					var isLeftValid = (parseInt($elem.css("left"), 10) - boundaryLeft > 0 && $elem.width() + 2 * borderWidth + parseInt($elem.css("left"), 10) - boundaryLeft < boundaryWidth)
														|| (parseInt($elem.css("left"), 10) - boundaryLeft <= 0 && !toLeft)
														|| (parseInt($elem.css("left"), 10) - boundaryLeft <= 0 && toLeft && direction.indexOf('e') !== -1)
														|| ($elem.width() + 2 * borderWidth + parseInt($elem.css("left"), 10) >= boundaryWidth && toLeft)
														|| ($elem.width() + 2 * borderWidth + parseInt($elem.css("left"), 10) >= boundaryWidth && !toLeft && direction.indexOf('w') !== -1); 				

					var isTopValid = (parseInt($elem.css("top"), 10) - boundaryTop > 0 && $elem.height() + 2 * borderWidth + parseInt($elem.css("top"), 10)  - boundaryTop < boundaryHeight)
														|| (parseInt($elem.css("top"), 10) - boundaryTop <= 0 && !toTop)
														|| (parseInt($elem.css("top"), 10) - boundaryTop <= 0 && toTop && direction.indexOf('s') !== -1)
														|| ($elem.height() + 2 * borderWidth + parseInt($elem.css("top"), 10) >= boundaryHeight && toTop)
														|| ($elem.height() + 2 * borderWidth + parseInt($elem.css("top"), 10) >= boundaryHeight && !toTop && direction.indexOf('n') !== -1);

					if(isTopValid && isLeftValid) {
						switch(direction) {
							case 'se':
								break;
							case 'sw':
								deltaPageX = -deltaPageX;
								setRight();
								break;
							case 'ne':
								deltaPageY = -deltaPageY;
								setBottom();
								break;
							case 'nw': 
								deltaPageX = -deltaPageX;
								deltaPageY = -deltaPageY;
								setBottom();
								setRight();
								break;
						}

						if(Math.abs(deltaPageX) < Math.abs(deltaPageY)) {
							if($elem.width() + deltaPageX < defaults.minWidth) {
								$elem.width(defaults.minWidth);
								$elem.height(defaults.minWidth / rate);
							} else if ($elem.width() + deltaPageX > defaults.maxWidth) {
								$elem.width(defaults.maxWidth);
								$elem.height(defaults.maxWidth / rate);
							} else {
								$elem.width($elem.width() + deltaPageX)
								$elem.height($elem.width() / rate);
							}
						} else {
							var deltaWidth =  rate * Math.abs(deltaPageY);
							deltaWidth = deltaPageY > 0 ? deltaWidth : -deltaWidth;

							if($elem.width() + deltaWidth < defaults.minWidth) {
								$elem.width(defaults.minWidth);
								$elem.height(defaults.minWidth / rate);
							} else if ($elem.width() + deltaWidth > defaults.maxWidth) {
								$elem.width(defaults.maxWidth);
								$elem.height(defaults.maxWidth / rate);
							} else {
								$elem.width($elem.width() + deltaWidth);
								$elem.height($elem.width() / rate);
							}
						}
					}
				} else if(canDrag) {
					var curX = event.pageX,
						  curY = event.pageY;
				
					//左右平移控制
					if ( (parseInt($elem.css("left"), 10) - boundaryLeft > 0 && $elem.width()  + 2 * borderWidth + parseInt($elem.css("left"), 10) - boundaryLeft < boundaryWidth)
						|| (parseInt($elem.css("left"), 10) - boundaryLeft <= 0 && curX - prePageX > 0)
						|| ($elem.width()  + 2 * borderWidth + parseInt($elem.css("left"), 10) >= boundaryWidth && curX - prePageX < 0) ) {
						$elem.css({
							left: curLeft + curX - prePageX
						});			
					} 

					if(parseInt($elem.css("left"), 10) <= boundaryLeft) {
						$elem.css('left', boundaryLeft);
					}

					if($elem.width()  + 2 * borderWidth + parseInt($elem.css("left"), 10) - boundaryLeft >= boundaryWidth) {
						$elem.css('left', boundaryLeft + boundaryWidth - $elem.width() - 2 * borderWidth);
					}

					if ((parseInt($elem.css("top"), 10) - boundaryTop > 0 && $elem.height()  + 2 * borderWidth + parseInt($elem.css("top"), 10)  - boundaryTop < boundaryHeight)
							|| (parseInt($elem.css("top"), 10) - boundaryTop <= 0 && curY - prePageY > 0)
							|| ($elem.height()  + 2 * borderWidth + parseInt($elem.css("top"), 10) >= boundaryHeight && curY - prePageY < 0)) {
						$elem.css({
							top: curTop + curY - prePageY,
						});	
					}

					if(parseInt($elem.css("top"), 10) <= boundaryTop) {
						$elem.css('top', boundaryTop);
					}

					if($elem.height() + 2 * borderWidth + parseInt($elem.css("top"), 10) - boundaryTop >= boundaryHeight) {
						$elem.css('top', boundaryTop + boundaryHeight - $elem.height() - 2 * borderWidth);
					}

				}
			};


			$elem.on('selectstart', function(event) {
				var event = event || window.event;
				if(event.preventDefault) {
					event.preventDefault();
				} else {
					event.returnValue = false;
				}
				return false;
			});

			$elem.on('dragstart', function(event) {
				var event = event || window.event;
				if(event.preventDefault) {
					event.preventDefault();
				} else {
					event.returnValue = false;
				}
				return false;
			});
			$elem.on('mousemove', mouseMoveOnElem);

			var $background = null;
			$elem.mousedown(function(event) {
				if(!hasInit) {
					init();
					hasInit = true;
				}
				if(resizeMode) {
					if(!$background) {
						$background = $('<div class="background"></div>');
						$background.css({
							'width': $('body').width() * 0.9,
							'height': $('body').height() * 0.9,
							'position': 'absolute',
							'top': 0,
							'left': 0,
							'z-index': $elem.css('z-index'),
							'opacity': 0
						});
						$background.insertBefore($elem);
					}
					canResize = true;
					prePageX = event.pageX;
					prePageY = event.pageY;
					$('body').css('cursor', direction + '-resize')
					$elem.off('mousemove', mouseMoveOnElem);
					$(document).on('mousemove', mouseMoveOnDocument);

				} else if(dragMode) {
					canDrag = true;
					setTop();
					setLeft();
					prePageX = event.pageX;
					prePageY = event.pageY;
					curLeft = parseInt($elem.css('left'), 10);
					curTop = parseInt($elem.css("top"), 10);
					$elem.off('mousemove', mouseMoveOnElem);
					$(document).on('mousemove', mouseMoveOnDocument);
				}
			});

			$(document).mouseup(function() {
				if(canResize) {
					canResize = false;
					resizeMode = false;
					direction = '';
					$background.remove();
					$background = null;
					$('body').css('cursor', 'default');
					$(document).off('mousemove', mouseMoveOnDocument);
					setTop();
					setLeft();
					$elem.trigger('resize-end', {
						original: original,
						finished: {
							top: parseInt($elem.css('top'), 10),
							left: parseInt($elem.css('left'), 10),
							width: $elem.width(),
							height: $elem.height()
						}
					});
					$elem.on('mousemove', mouseMoveOnElem);
				} else if(canDrag) {
					canDrag = false;
					dragMode = false;
					setTop();
					setLeft();
					$(document).off('mousemove', mouseMoveOnDocument);
					$elem.trigger('drag-end', {
						original: original,
						finished: {
							top: parseInt($elem.css('top'), 10),
							left: parseInt($elem.css('left'), 10),
							width: $elem.width(),
							height: $elem.height()
						}
					});
					$elem.on('mousemove', mouseMoveOnElem);
				}
			});

		});
	}});
})(jQuery);