var currentEditType = 'company';
var coverImageName = '', logoImageName = '', avatarImageName = '';

$(function() {
	$(document).on('scroll', function(event) {
		var scrollTop = $(document).scrollTop();
		if(scrollTop >= 180 && scrollTop <= 800) {
			$('div.card-preview-wrap').css({'position': 'fixed', top: '100px'});
		} else if(scrollTop <= 800){
			$('div.card-preview-wrap').css({'position': 'absolute', top: '0px'});
		} else {
			$('div.card-preview-wrap').css({'position': 'absolute', top: '450px'});
		}
	});
	listenNextStepBtnClickEvent();
	listenReuploadBtnClickEvent();
	syncEditingContent();
});

function listenNextStepBtnClickEvent() {
	$('#btn-next-render-top, #btn-next-render-bottom').click(function() {
		var btnId = $(this).attr('id');
		var rel = btnId.substring(btnId.lastIndexOf('-') + 1);

		var cardCover = $('div.card-preview-wrap img.card-cover').attr('src');
		var companyLogo = $('div.card-preview-wrap img.company-logo').attr('src');
		var companyName = $('div.edit-company-wrap div.company-name input').val();
		var companySite = $('div.edit-company-wrap div.company-website input').val();
		if(companySite.indexOf('http') !== 0) {
			companySite = 'http://' + companySite;
		}
		var companyAddr = $('div.edit-company-wrap div.company-address input').val();
		var productInfo = $('div.edit-company-wrap div.company-product textarea').val();

		var personAvatar = $('div.card-preview-wrap img.person-avatar').attr('src');
		var personName = $('div.edit-personal-wrap div.person-name input').val();
		var personJob = $('div.edit-personal-wrap div.person-job input').val();
		var personPhone = $('div.edit-personal-wrap div.person-phone input').val();
		var personEmail = $('div.edit-personal-wrap div.person-email input').val();
		var personSite = $('div.edit-personal-wrap div.person-website input').val();
		if(personSite.indexOf('http') !== 0) {
			personSite = 'http://' + personSite;
		}

		// 验证输入~~
		var isValid = true;
		var emailRegx = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;

		if(!companyName) {
			isValid = false;
			alert('请输入公司名称！');
		} 

		if(!companyAddr) {
			isValid = false;
			alert('请输入公司地址！');
		}

		if(!personName) {
			isValid = false;
			alert('请输入您的姓名！');
		}

		if(!personJob) {
			isValid = false;
			alert('请输入您的职位！');
		}

		if(!personPhone) {
			isValid = false;
			alert('请输入您的联系电话！');
		}

		if(personEmail && !emailRegx.test(personEmail)) {
			isValid = false;
			alert('请输入合法的邮箱地址！');
		}

		if(isValid) {
			$.post('/create/save/',
	      {
	        type: 'vcard',
	        cardCover: cardCover,
	        coverImageName: coverImageName,
	        companyLogo: companyLogo,
	        logoImageName: logoImageName,
	        companyName: companyName,
	        companyAddress: companyAddr,
	        companySite: companySite,
	        productInfo: productInfo,
	        personAvatar: personAvatar,
	        avatarImageName: avatarImageName,
	        personName: personName,
					personJob: personJob,	        
	        personPhone: personPhone,
	        personEmail: personEmail,
	        personSite: personSite
	      },
	      function(data) {
	    	 	if (data.status == 1) {
	    		 	location.href = '/create/modify?rel=' + rel;
	    	 	} else {
	    		 	alert(data.message);
	    	 	}
	     	},
	     	'json'
	    );
		}
	});
}

function listenReuploadBtnClickEvent() {
	$('div.upload-cover-done span.reupload-cover-btn').click(function() {
		$('div.upload-cover-done').addClass('hide');
		$('#cover-image-input').val('');
		$('div.upload-cover-wrap span.upload-file-wrap').removeClass('hide');
	});

	$('div.upload-logo-done span.reupload-logo-btn').click(function() {
		$('div.upload-logo-done').addClass('hide');
		$('#logo-image-input').val('');
		$('div.upload-logo-wrap span.upload-file-wrap').removeClass('hide');
	});

	$('div.upload-avatar-done span.reupload-avatar-btn').click(function() {
		$('div.upload-avatar-done').addClass('hide');
		$('#avatar-image-input').val('');
		$('div.upload-avatar-wrap span.upload-file-wrap').removeClass('hide');
	});
}

// function switchToEditCompany() {
// 	currentEditType = 'company';
// 	$('div.edit-type-btn span.edit-personal-info').removeClass('selected');
// 	$('div.edit-type-btn span.edit-company-info').addClass('selected');

// 	// 切换为显示编辑公司信息
// 	$('div.edit-personal-wrap:eq(0)').fadeOut(200, function() {
// 		$('div.edit-company-wrap:eq(0)').fadeIn(200);
// 	});

// 	// 名片预览处切换为显示公司信息
// 	$('div.card-content div.personal-info:eq(0)').fadeOut(200, function() {
// 		$('div.card-content div.company-info:eq(0)').fadeIn(200);
// 	});

// 	$('div.info-type-tab div.personal-info-tab').removeClass('selected');
// 	$('div.info-type-tab div.company-info-tab').addClass('selected');
// }

// function switchToEditPersonal() {
// 	currentEditType = 'personal';
// 	$('div.edit-type-btn span.edit-company-info').removeClass('selected');
// 	$('div.edit-type-btn span.edit-personal-info').addClass('selected');

// 	// 切换为显示编辑个人信息
// 	$('div.edit-company-wrap:eq(0)').fadeOut(200, function() {
// 		$('div.edit-personal-wrap:eq(0)').fadeIn(200);
// 	});

// 	// 名片预览处切换为显示个人信息
// 	$('div.card-content div.company-info:eq(0)').fadeOut(200, function() {
// 		$('div.card-content div.personal-info:eq(0)').fadeIn(200);
// 	});

// 	$('div.info-type-tab div.company-info-tab').removeClass('selected');
// 	$('div.info-type-tab div.personal-info-tab').addClass('selected');
// }

function syncEditingContent() {
	// 公司信息
	$('div.edit-company-wrap div.company-name input').keyup(function() {
		var content = $(this).val();
		content = content ? content : '公司名称';
		$('div.card-preview-wrap span.company-name').text(content);
	});

	$('div.edit-company-wrap div.company-website input').keyup(function() {
		var content = $(this).val();
		var $website = $('div.card-preview-wrap a.company-website');
		if(content) {
			$website.text(content);
			$website.attr('href', content);
		} else {
			$website.text('www.company.com');
			$website.attr('href', '');
		}
	});

	$('div.edit-company-wrap div.company-address input').keyup(function() {
		var content = $(this).val();
		content = content ? content : '公司地址';
		$('div.card-preview-wrap div.company-address').text(content);
	});

	$('div.edit-company-wrap div.company-product textarea').keyup(function() {
		var content = $(this).val();
		content = content ? content : '产品/服务简介...'
		$('div.card-preview-wrap div.company-product').text(content);
	});

	// 个人信息
	$('div.edit-personal-wrap div.person-name input').keyup(function() {
		var content = $(this).val();
		content = content ? content : '姓名';
		$('div.card-preview-wrap div.person-name').text(content);
	});

	$('div.edit-personal-wrap div.person-job input').keyup(function() {
		var content = $(this).val();
		content = content ? content : '职位';
		$('div.card-preview-wrap div.person-job').text(content);
	});

	$('div.edit-personal-wrap div.person-phone input').keyup(function() {
		var content = $(this).val();
		content = content ? content : '联系电话';
		$('div.card-preview-wrap div.person-phone').text(content);
	});

	$('div.edit-personal-wrap div.person-email input').keyup(function() {
		var content = $(this).val();
		content = content ? content : '邮箱地址';
		$('div.card-preview-wrap div.person-email').text(content);
	});

	$('div.edit-personal-wrap div.person-website input').keyup(function() {
		var content = $(this).val();
		var $website = $('div.card-preview-wrap a.person-website');
		if(content) {
			$website.text(content);
			$website.attr('href', content);
		} else {
			$website.text('www.personal.com');
			$website.attr('href', '');
		}
	});

	// ajax上传图片
	// 封面图片
	$('#cover-image-input').bind("change", uploadCoverImage);
	function uploadCoverImage() {
		$("#cover-image-input").unbind("change", uploadCoverImage);
		$('div.upload-cover-wrap span.upload-file-wrap').addClass('hide');
		$('div.upload-cover-done span.reupload-cover-btn').text('正在上传...');
		$('div.upload-cover-done').removeClass('hide');
		$.ajaxFileUpload({
  		url: "/create/upload/",
  		data: {uploadType: "create_qrcode_vcard", imageType: 'cover-image'},
  		fileElementId: "cover-image-input",
  		secureuri: false,
			dataType: "json",
			success: function(data, status) {
				$("#cover-image-input").bind("change", uploadCoverImage);
				if (data.response == 1) {
					var savePath = data.savepath.replace(/\/data\//g, function(s) {
						return '/';
					});
					$('div.upload-cover-done span.reupload-cover-btn').text('重新上传');
					$('div.upload-cover-done div.image-name').text(data.filename);
					$('div.card-preview-wrap img.card-cover').attr('src', savePath);
					coverImageName = data.filename;
				} else if(data.response == 0){
					alert(data.message);
				}
			},
			error: function(data) {
				alert('error!');
			}
  	});
	}

	//logo图片
	$('#logo-image-input').bind("change", uploadLogoImage);
	function uploadLogoImage() {
		$("#logo-image-input").unbind("change", uploadLogoImage);
		$('div.upload-logo-wrap span.upload-file-wrap').addClass('hide');
		$('div.upload-logo-done span.reupload-logo-btn').text('正在上传...');
		$('div.upload-logo-done').removeClass('hide');
		$.ajaxFileUpload({
  		url: "/create/upload/",
  		data: {uploadType: "create_qrcode_vcard", imageType: 'logo-image'},
  		fileElementId: "logo-image-input",
  		secureuri: false,
			dataType: "json",
			success: function(data, status) {
				$("#logo-image-input").bind("change", uploadLogoImage);
				if (data.response == 1) {
					var savePath = data.savepath.replace(/\/data\//g, function(s) {
						return '/';
					});
					$('div.upload-logo-done span.reupload-logo-btn').text('重新上传');
					$('div.upload-logo-done div.image-name').text(data.filename);
					$('div.card-preview-wrap img.company-logo').attr('src', savePath);
					logoImageName = data.filename;
				} else if(data.response == 0){
					alert(data.message);
				}
			},
			error: function(data) {
				alert('error!');
			}
  	});
	}

	//个人头像
	$('#avatar-image-input').bind("change", uploadAvatarImage);
	function uploadAvatarImage() {
		$("#avatar-image-input").unbind("change", uploadAvatarImage);
		$('div.upload-avatar-wrap span.upload-file-wrap').addClass('hide');
		$('div.upload-avatar-done span.reupload-avatar-btn').text('正在上传...');
		$('div.upload-avatar-done').removeClass('hide');
		$.ajaxFileUpload({
  		url: "/create/upload/",
  		data: {uploadType: "create_qrcode_vcard", imageType: 'avatar-image'},
  		fileElementId: "avatar-image-input",
  		secureuri: false,
			dataType: "json",
			success: function(data, status) {
				$("#avatar-image-input").bind("change", uploadAvatarImage);
				if (data.response == 1) {
					var savePath = data.savepath.replace(/\/data\//g, function(s) {
						return '/';
					});
					$('div.upload-avatar-done span.reupload-avatar-btn').text('重新上传');
					$('div.upload-avatar-done div.image-name').text(data.filename);
					$('div.card-preview-wrap img.person-avatar').attr('src', savePath);
					avatarImageName = data.filename;
				} else if(data.response == 0){
					alert(data.message);
				}
			},
			error: function(data) {
				alert('error!');
			}
  	});
	}
};