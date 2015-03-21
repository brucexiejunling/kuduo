var imageSelector = null;
var avatarPath = '';
var prePid;

$(function() {
	initDatePicker();
	initImageAreaSelect();

	if($('.province-select-wrap span.btn-text').attr('aid') !== '-1') {
		initCity($('.province-select-wrap span.btn-text').attr('aid'));
	};

	listenRegionSelectEvents();

	$('.basic-info span.save-btn').click(submitUserBasic);

	$('.modify-password span.save-btn').click(saveNewPassword);

	$('.modify-avatar span.save-btn').click(saveNewAvatar);
	
	$('#user-avatar-input').bind("change", uploadAvatar);
});

function initDatePicker() {
	$('.basic-info .birthday-wrap i').click(function(e) {
		var event = e || window.event;
		if(event.stopPropagation) {
			event.stopPropagation();
		} else {
			event.cancelBubble = true;
		}
		$('#birthday').datepicker('show');
	});
	$('#birthday').datepicker();
}

function initImageAreaSelect() {
	imageSelector = $('.modify-avatar img.user-avatar').imgAreaSelect({ 
		instance: true,
		aspectRatio: '1:1', 
		handles: true,
		onSelectChange: previewAvatar
	});
}

 function previewAvatar(img, selection) {
 	if(!selection.width || !selection.height) {
 		return;
 	}
 	var avatarWidth = 100, avatarHeight = 100;
  var scaleX =  avatarWidth / (selection.width || 1);
  var scaleY = avatarHeight / (selection.height || 1);

  $(".modify-avatar div.preview-avatar-wrap img").css({
      width: Math.round(scaleX * img.width) + 'px',
      height: Math.round(scaleY * img.height) + 'px',
      marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px',
      marginTop: '-' + Math.round(scaleY * selection.y1) + 'px'
  });
}

function listenRegionSelectEvents() {
	$('ul.menu-list li.menu-item').click(function() {
		if(!$(this).hasClass('selected')) {
			var $selected = $('ul.menu-list li.selected');
			$selected.removeClass('selected');
			hideSpecificSettingContent($selected.attr('type'));
			$(this).addClass('selected');
			showSpecificSettingContent($(this).attr('type'));
		}
	});

	$('div.dropdown button.dropdown-toggle').click(function(e) {
		var event = e || window.event;
		if(event.stopPropagation) {
			event.stopPropagation();
		} else {
			event.cancelBubble = true;
		}
		$(this).next('ul:eq(0)').toggle();
	});

	$(document).click(function() {
		$('.basic-info ul.dropdown-menu').hide();
	});

	$('div.dropdown li.dropdown-menu-item').click(function() {
		var aid = $(this).attr('id');
		$(this).parent().prev('button').find('span.btn-text').attr('aid', aid).html($(this).text());
		$(this).parent().hide();

		//省份
		if( $(this).parent().parent().hasClass('province-select-wrap') 
			&& prePid !== aid ) {
			initCity(aid);
		}
	});
}

function hideSpecificSettingContent(type) {
	if(type === 'modify-avatar' && imageSelector) {
		imageSelector.cancelSelection();
	}
	$('div.setting-content div.' + type).hide();
}

function showSpecificSettingContent(type) {
	$('div.setting-content div.' + type).show();
}

function initCity(pid) {
	if(pid === '-1') {
		$('.city-select-wrap button span.btn-text').attr('aid', -1).html('请选择');
		return;
	}
	var capitalCities = {
		'2': '东城区',
		'22': '和平区',
		'41': '石家庄市',
		'225': '太原市',
		'356': '呼和浩特市',
		'471': '沈阳市',
		'586': '长春市',
		'656': '哈尔滨市',
		'803': '黄浦区',
		'823': '南京市',
		'943': '杭州市',
		'1045': '合肥市',
		'1168': '福州市',
		'1263': '南昌市',
		'1347': '济南市',
		'1532': '郑州市',
		'1709': '武汉市',
		'1826': '长沙市',
		'1963': '广州市',
		'2106': '桂林市',
		'2230': '海口市',
		'2258': '渝中区',
		'2299': '成都市',
		'2502': '贵阳市',
		'2600': '昆明市',
		'2746': '拉萨市',
		'2827': '西安市',
		'2945': '兰州市',
		'3046': '西宁市',
		'3098': '银川市',
		'3125': '乌鲁木齐市',
		'3235': '台北市',
		'3236': '九龙城区',
		'3237': '澳门半岛'
	}
  $('.city-select-wrap button span.btn-text').html(capitalCities[pid]);
  $('.city-select-wrap li.dropdown-menu-item').attr('id', pid).html(capitalCities[pid]);
	loadCities(pid);
}

function loadCities(pid) {
	$.post('/user/loadCities', {province: pid}, function(result) {
		if(result.flag === 1) {
			var data = result.data;
			var $dropdownMenu = $('.city-select-wrap ul.dropdown-menu');
			$dropdownMenu.html('');
			$.each(data, function(i, item) {
				// 默认选中的那条
				if(i === 0) {
					$('.city-select-wrap button span.btn-text').attr('aid', item.area_id);
				}

				var $menuItem = $('<li class="dropdown-menu-item"></li>').attr('id', item.area_id).text(item.title);
				$menuItem.click(function() {
					$(this).parent().prev('button').find('span.btn-text').attr('aid', item.area_id).html($(this).text());
					$(this).parent().hide();
				});
				$dropdownMenu.append($menuItem);
			});
		}
	});
}

function submitUserBasic() {
	genderMapping = {"male": 1, "female": 0};
	var nickName = $('.basic-info input.nick-name').val();
	var gender = genderMapping[$('.basic-info input.gender:checked').attr('id')];
	var birthday = $('.basic-info input.birthday').val();
	var province = $('.province-select-wrap span.btn-text').attr('aid');
	var city = $('.city-select-wrap span.btn-text').attr('aid');
	var valid = true;
	var introduction = $('.basic-info textarea.introduction').val();

	var nameRegx = /^[a-zA-Z0-9\u4e00-\u9fa5_]{2,20}$/,
			dateRegx = /^\d{4}-\d{2}-\d{2}$/;

	if(!nameRegx.test(nickName)) {
		valid = false;
		alert('昵称须是字母,数字,汉字或下划线组成的2到20个字符!');
	}

	if(!dateRegx.test(birthday)) {
		valid = false;
		alert('生日的日期格式须是yyyy-mm-dd!');
	}

	if(province === '-1') {
		valid = false;
		alert('请选择省份！');
	}

	if(city === '-1') {
		valid = false;
		alert('请选择城市！');
	}

	if(valid) {
		$.post('/user/saveBasicInfo', 
			{
				nickName: escapeHtml(nickName),
				gender: gender,
				birthday: birthday,
				province: province,
				city: city,
				introduction: escapeHtml(introduction)
			}, 
			function(result) {
				if(result.flag === 1) {
					location.reload();
				} else {
					alert(result.message);
				}
		});
	}
}

function saveNewPassword() {
	var pRegx = window.QRCODE.regx.password;
	var curPassword = $('div.modify-password div.current-password input').val();
	var newPassword = $('div.modify-password div.new-password input').val();
	var confirmPassword = $('div.modify-password div.confirm-password input').val();
	if(!pRegx.test(curPassword)) {
		showCurrentPasswordErorr('密码必须是6-16位的字母数字或下划线');
	} else if (!pRegx.test(newPassword)) {
		showNewPasswordError('密码必须是6-16位的字母数字或下划线');
	} else if(confirmPassword !== newPassword){
		showConfirmPasswordError('两次密码不一致！');
	} else {
		validatePassword(curPassword, function(result) {
			if(result.flag === 1) {
				$.post('/user/savePassword', {password: newPassword}, function(result) {
					if(result.flag === 1) {
						alert('密码修改成功！');
					} else {
						alert('密码修改失败！请重试');
					}
				});
			} else {
				alert('当前密码错误！');
			}
		});
	}
}

function showCurrentPasswordErorr(msg) {
	alert(msg);
}

function showNewPasswordError(msg) {
	alert(msg);
}

function showConfirmPasswordError(msg) {
	alert(msg);
}

function validatePassword(password, callback) {
	$.post('/user/validatePassword', {password: password}, callback);
}

function uploadAvatar() {
	$("#user-avatar-input").unbind("change", uploadAvatar);
	$.ajaxFileUpload({
		url: "/user/uploadAvatar",
		data: {uploadType: "avatar"},
		fileElementId: "user-avatar-input",
		secureuri: false,
		dataType: "json",
		success: function(result, status) {
			$("#user-avatar-input").bind("change", uploadAvatar);
			if (result.flag === 1) {
				avatarPath = result.imagePath;
				var savePath = "http://www.ikuduo.com/" + result.imagePath;
				$('div.modify-avatar .user-avatar-wrap img').attr('src', savePath);
				$('div.modify-avatar .preview-avatar-wrap img').attr('src', savePath);
				$('div.modify-avatar img.user-avatar-with-original-size').attr('src', savePath);
			} else {
				alert("上传失败");
			}
		},
		error: function(data) {
		
		}
	});
}

function saveNewAvatar() {
	var $img = $('.modify-avatar .user-avatar-wrap img');
	if(avatarPath === '') {
		avatarPath = $img.attr('src');
		avatarPath = avatarPath.substring(22);
	}
	var selection = imageSelector.getSelection();
	selection.width = selection.width || $img.width();
	selection.height = selection.height || $img.height();
	var originalImgWidth = $('div.modify-avatar img.user-avatar-with-original-size').width();
	var scale = originalImgWidth / $img.width();
	selection.width = selection.width * scale;
	selection.height = selection.height * scale;
	selection.x1 = selection.x1 * scale;
	selection.x2 = selection.x1 + selection.width;
	selection.y1 = selection.y1 * scale;
	selection.y2 = selection.y1  + selection.height;

	$.post('/user/cropImg', {selection: selection, imgPath: avatarPath}, function(result) {
		alert('头像已修改')
		location.reload();
	});
}

function escapeHtml(html) {
	var entityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': '&quot;',
    "'": '&#39;',
    "/": '&#x2F;'
  };
  var str = html.replace(/[&<>"'\/]/g, function(s) {
  	return entityMap[s];
	});
  return str;
}
