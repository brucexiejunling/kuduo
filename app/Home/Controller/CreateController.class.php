<?php

namespace Home\Controller;
use Think\Controller;
use Think\Cache\Driver\Memcache;

class CreateController extends Controller {
	
	/**
	 * 类型选择主页
	 */
	public function index() {
		if (session("create_qrcode_type")) {
			$this->assign("tmp_qrcode_undone", true);
		}
		$this->display();
	}
	
	/**
	 * ===========================文本类型二维码===========================
	 */
	public function text() {
		if (session("create_qrcode_type") == "text") {
			$shortCode = session("create_short_code");
			if (session("create_text_normal_tag")) {
				$this->assign("normalTag", 1);
				//如果存在短地址
				if ($shortCode) {
					$data =  S("create_" . $shortCode);
					$this->assign("textValue",  $data['create_qrcode_content']);
				} else {
					$this->assign("textValue", session("create_qrcode_content"));
				}		
			} else {
				$data = S("create_" . $shortCode);
				$this->assign("textValue", $data['create_qrcode_content']);
			}
		}
		$this->display();
	}

	/**
	 *===================== url类型二维码生成页面===============================
	 */
	public function url() {
		if (session("create_qrcode_type") == "url") {
			$shortCode = session("create_short_code");				//取出短地址
			$data = S("create_" . $shortCode);
			$this->assign("urlValue", $data['create_qrcode_content']);
		}
		$this->display();
	}
	
	/**
	 * ===========================WIFI类型二维码页面=============================
	 */
	public function wifi() {
		if (session("create_qrcode_type") == "wifi") {
			$shortCode = session("create_short_code");				//取出短地址
			if ($shortCode) {
				$data = S("create_" . $shortCode);
				$this->assign("value", json_decode($data['create_qrcode_content'], true));
			} else {
				$this->assign("value_normal", json_decode(session("create_qrcode_content"), true));
			}
		}
		$this->display();
	}
	
	/**
	 * ==========================文件类型二维码页面=================================
	 */
	public function file() {
		if (session("create_qrcode_type") == "file") {
			$shortCode = session("create_short_code");				//取出短地址
			$data = S("create_" . $shortCode);
			$this->assign("value", json_decode($data['create_qrcode_content'], true));
		}

		$this->display();
	}
	
	
	/**
	 * ==========================名片类型二维码页面=================================
	 */

	public function mp() {
		if(session("create_qrcode_type") == "mp") {
			$shortCode = session("create_short_code");				//取出短地址
			$data = S("create_" . $shortCode);
			$this->assign("cardValue", json_decode($data['create_qrcode_content']));
		}	
		$this->display();
	}


	/**
	 * ==========================电话类型二维码页面=================================
	 */

	public function phone() {
		if(session("create_qrcode_type") == "phone") {
			$this->assign("value", session("create_qrcode_content"));
		}	
		$this->display();
	}

		/**
	 * ==========================短信类型二维码页面=================================
	 */

	public function message() {
		if(session("create_qrcode_type") == "message") {
			$this->assign("messageValue", json_decode(session("create_qrcode_content"), true));
		}	
		$this->display();
	}

	/**
	* =========================视频类型二维码页面================================
	*/
	public function video() {
		if(session("create_qrcode_type") == "video") {
			$shortCode = session("create_short_code");
			$data = $data = S("create_" . $shortCode);
			$this->assign("value", json_decode($data['create_qrcode_content'], true));
		}	
		$this->display();
	}

	/**
	 * ===================modify页面=======================
	 * 如果qrcode_type没有指定、则不能进入此页面
	 */
	public function modify() {
		if (session("create_qrcode_type") == "") {
			header("location: /create?rel=modify_error");
		}

		Vendor ( "SaeTOAuthV2" );
		$sinaSNS = new \SaeTOAuthV2 ( C ( "SNS_OPEN_SINA.APP_KEY" ), C ( "SNS_OPEN_SINA.APP_SECRET" ) );
		$sinaSNSURL = $sinaSNS->getAuthorizeURL ( C ( "SNS_OPEN_SINA.CALLBACK_URL" ) );
		$this->assign ( "sina_url", $sinaSNSURL );
		session("sns_redirect", U("login/oauth_temp_login"));

		$this->display();
	}
	 
	
	/**
	 * ===============用户取消正在编辑的二维码、恢复短地址状态为未使用===========
	 */
	public function cancelQRCodeEditing() {
		$type = session("create_qrcode_type");
		if ($type) {
			$shortCode = session("create_short_code");
			$qrcodeData = S("create_" . $shortCode);
			
			if ($shortCode) {
				D("ShortUrl", "Service")->updateShortUrlData($shortCode, array("url" => "", 'status' => 0));
				S("create_" . $shortCode, null);
			}
			
			switch ($type) {
				case "file":    //删除已上传的文件
					@unlink(session("create_file_upload_path"));
					session("create_file_upload_path", null);
					session("create_file_upload_size", null);
					break;
				case "wifi":
					@unlink(session("create_wifi_logo_path"));
					session("create_wifi_logo_path", null);
					break;
				case "video":
					@unlink(session("create_video_upload_path"));
					session("create_video_upload_path", null);
					session("create_video_upload_size", null);
					session("create_video_upload_image_path", null);
					break;
			}
			
			session("create_qrcode_type", null);
			session("create_short_code", null);
			session("create_qrcode_value", null);
			session("create_qrcode_content", null);

			$this->ajaxReturn(array(
				"flag" => 1,
				"message" => "取消成功"
			));
		} else {
			$this->ajaxReturn(array(
					"flag" => 0,
					"message" => "您未有正在编辑的二维码"
			));
		}
	}
	 /*
	  * 如果是有声类型二维码、进入美化页面就自动分配一个短地址
	  * 如果是普通类型的二维码、则直接根据内容来生成
	  */
    public function preview(){
        $type = session("create_qrcode_type");
        $qrCodeValue = session("create_qrcode_value");
        $globalValue = I("post.globalValue");             //用户二维码的设置
       
        //防止非法调用、没有规定type的二维码不是在网站上生成的
        if (!$type) {
        	$this->ajaxReturn(array('msg' => '非法调用', "flag" => 0));
        }
        
        if (!session("create_qrcode_image_name")) {
        	session("create_qrcode_image_name", md5(session_id() . time()) . ".png"); 
        }
        
		$imagePath = D("QRCode", "Service")->createQRCode($globalValue, array(
			"savepath" => "data/preview/",
			"qrcode_value" => $qrCodeValue,
			"qrcode_image_name" => session("create_qrcode_image_name")
		));
		
		$this->ajaxReturn(array('msg' => '生成成功', "flag" => 1, 'src' => $imagePath));
    }

    /**
     * 添加一条二维码到数据库
     * @param  [array] $config 经过转换的二维码配置信息
     * @return string  msg   返回信息
     *         status  bool  成功与否
     *         data    array 返回生成的二维码id, 短地址和图片地址
     * @author  李展威 <zhanweelee@gmail.com>
     */
    public function complete(){
    	$type = session("create_qrcode_type");
    	$qrCodeValue = session("create_qrcode_value");
    	$shortCode = session("create_short_code");
    	
    	if ($shortCode) {
    		$tmpData = S("create_" . $shortCode);
    		$qrCodeContent = $tmpData['create_qrcode_content'];
    	} else {
    		$qrCodeContent = session("create_qrcode_content");
    	}
    	
    	$globalValue = I("post.globalValue");             //用户二维码的设置
    	
    	//防止非法调用、没有规定type的二维码不是在网站上生成的
    	if (!$type) {
    		$this->ajaxReturn(array('msg' => '非法调用', "flag" => 0));
    	}
    	
    	/**
    	 * 没登陆、且具有跳过的状态
    	 * 1. 没登陆的用户可以点击完成、但是第一次的时候会提示登录
    	 * 2. 在第一次点击生成的的时候会做一个标记
    	 */
    	// if (!session("uid") && !session("qrcode_complete_skip_login")) {
    	// 	session("qrcode_complete_skip_login", true);
    	// 	$this->ajaxReturn(array("flag" => 3, "message" => "是否选择登录"));
    	// }
    	
    	// if (!session("create_qrcode_image_name")) {
    	// 	session("create_qrcode_image_name", md5(session_id() . time()) . ".png");
    	// }
    	//这个阶段要求必须登录
    	if (!session("uid")) {
    		$this->ajaxReturn(array('msg' => '请先登录', "flag" => 3));
    	}
    	

    	if (!session("create_qrcode_image_name")) {
        	session("create_qrcode_image_name", md5(session_id() . time()) . ".png"); 
        }
    	$imagePath = D("QRCode", "Service")->createQRCode($globalValue, array(
    			"savepath" => "data/qrcode/",
    			"qrcode_value" => $qrCodeValue,
    			"qrcode_image_name" => session("create_qrcode_image_name")
    	));
    	
    	$result = D("QRCode", "Service")->saveQRCodeData(array(
    		"imagePath" => $imagePath,
    		"content" => $qrCodeContent,
    		"type" => $type,
    		"shortCode" => $shortCode ? $shortCode : ""
    	));

    	if ($result) {
    		//清除缓存数据
    		switch (session("create_qrcode_type")) {
    			case 'file':
    				session("create_file_upload_path", null);
    				session("create_file_upload_size", null);
    				break;
    			case 'wifi':
    				session("create_wifi_logo_path", null);
    				break;
    			case "video":
    				session("create_video_upload_path", null);
	 				session("create_video_upload_size", null);
    				break;
    			default:
    				# code...
    				break;
    		}

    		session("create_qrcode_type", null);
    		session("create_short_code", null);
    		session("create_qrcode_value", null);
    		session("create_modify_background_image", null);
    		session("create_modify_logo_image", null);
    		session("create_qrcode_image_name", false);
    		
    		if ($shortCode) {
    			S("create_" . $shortCode, null);				//清空缓存
    			D("ShortUrl", "Service")->updateShortUrlData($shortCode, array("status" => 1));			//更新锁定的短地址数据
    		} else {
    			session("create_qrcode_content", null);
    		}
    		$this->ajaxReturn(array(
    			"flag" => 1,
    			"message" => "生成成功"
    		));
    	} else {
    		$this->ajaxReturn(array(
    			"flag" => 2,
    			"message" => "二维码数据已过期，请重新生成"
    		));
    	}
    }
	
	/**
	 * ===================储存二维码制作时临时提交的数据===========================
	 * session中以tmp开头的是共有的、务必保持初始化。其他均为私有
	 */

	 public function save() {
    	$tempData = I("post.");					//获取前台传递过来的数据
	 	$shortCode = "";

		switch ($tempData['type']) {
			/**=============================text类型======================**/
			case 'text':	
				session("create_qrcode_type", "text");						//规定文本
				
				//代表使用活码
				if ($tempData['shortUrlActive'] == 1) {	
					//shortcode不能浪费啊！！！
					if (session("create_short_code")) {
						$shortCode = session("create_short_code");
					} else {
						$shortCode = D("ShortUrl", "Service")->getOneShortUrlCode();
						session("create_short_code", $shortCode);
					}
					D("ShortUrl", "Service")->updateShortUrlData($shortCode, array("url" => C("GLOBAL_DIRECT_URL") . $shortCode));
					
					session("create_qrcode_value", C("SHORT_URL_DOMAIN") . $shortCode);
					if ($tempData['normal'] == 0) {
						session("create_text_normal_tag", 0);			//text专有、表示是否使用普通文本，0代表没用，使用的是普通文本
						S("create_" . $shortCode,  array("create_qrcode_content" => htmlspecialchars(str_filter(htmlspecialchars_decode($tempData['textValue']))),  "create_qrcode_type" => "text"));		//值使用memcache来缓存、方便用户预览
					} else {
						session("create_text_normal_tag", 1);
						S("create_" . $shortCode, array("create_qrcode_content" => htmlspecialchars($tempData['textValue']), "create_qrcode_type" => "text"));
					}
					
					
				} else {
					session("create_qrcode_value", htmlspecialchars_decode(($tempData['textValue'])));
					session("create_qrcode_content", htmlspecialchars_decode(($tempData['textValue'])));

					//如果存在短地址就把短地址去除掉
					if (session("create_short_code")) {
						D("ShortUrl", "Service")->updateShortUrlData(session("create_short_code"), array("url" => "", "status" => 0));
						S("create_" . session("create_short_code"), null);
						session("create_short_code", null);
					}
				}
				break;
				/**=============================url类型======================**/
			case 'url':
				session("create_qrcode_type", "url");						//规定类型
				
				//shortcode不能浪费啊！！！
				if (session("create_short_code")) {
					$shortCode = session("create_short_code");
				} else {
					$shortCode = D("ShortUrl", "Service")->getOneShortUrlCode($tempData['urlValue']);
				}
				D("ShortUrl", "Service")->updateShortUrlData($shortCode, array("url" => $tempData['urlValue']));
		
				session("create_qrcode_value", C("SHORT_URL_DOMAIN") . $shortCode);			//二维码编码的值
				session("create_short_code", $shortCode);				//短地址的码
				S("create_" . $shortCode, array("create_qrcode_content" => $tempData['urlValue'], "create_qrcode_type" => "url"));								//二维码所指向的内容
				break;
				/**=============================wifi类型======================**/
			case "wifi":
				
				if (!session("uid")) {
					$this->ajaxReturn(array(
						"status" => 0,
						"message" => "登陆后才可使用wifi类型二维码"
					));
				}
				
				session("create_qrcode_type", "wifi");
				
				//账号
				$ssid = preg_replace("/[\"\'\:\?\.]/", "", $_POST['wifiname']);
				//截取到第一个;
				$pos = strpos($ssid, ";");
				if ($pos) {
					$ssid = substr($ssid, 0, $pos);
				}
					
				//密码
				if (preg_match("/[a-zA-Z0-9\-_]{8,20}/", $password)) {
					$this->ajaxReturn(array(
							"status" => 0,
							"message" => "密码应由8-20位英文或数字"
					));
				}
				
				$password = preg_replace("/[\;]/", "", $tempData['wifipassword']);
				//使用增强模式
				if ($tempData['powerMode'] == 1) {
					if (session("create_short_code")) {
						$shortCode = session("create_short_code");
					} else {
						$shortCode = D("ShortUrl", "Service")->getOneShortUrlCode();
						session("create_short_code", $shortCode);//短地址的码
					}
					D("ShortUrl", "Service")->updateShortUrlData($shortCode, array("url" => C("GLOBAL_DIRECT_URL") . $shortCode));
					
					session("create_qrcode_value", C("SHORT_URL_DOMAIN") . $shortCode);
					if (!session("create_wifi_logo_path")) {
						$logoPath = "data/uploads/default/wifi_logo.png";
					} else {
						$logoPath = session("create_wifi_logo_path");
					}

					S("create_" . $shortCode, array(
						"create_qrcode_content" => json_encode(
							array( 
								"ssid" => $ssid, 
								"password" => $password, 
								"logo" => $logoPath, 
								"news" => $tempData['news'],
								"shopname" => $tempData['shopname']
							)), 
						"create_qrcode_type" => "wifi"));
				} else {
					//加密方式
					$encrypt = $tempData['wifiencrypt'];
					switch($encrypt) {
						case "WEP":
							$encrypt = "WEP";
							break;
						case "":
							$encrypt = "";
						default:
							$encrypt = "WPA";
					}
					
					//标准的wifi生成方式
					$wifiContent = "WIFI:T:" . $encrypt . ";S:" . $ssid . ";P:" . $password . ";";
					session("create_qrcode_value", $wifiContent);			//二维码编码的值
					session("create_qrcode_content", json_encode(array("ssid" =>$ssid, "password" => $password, "encrypt" => $encrypt)));
						
					//如果存在短地址就把短地址去除掉
					if (session("create_short_code")) {
						D("ShortUrl", "Service")->updateShortUrlData(session("create_short_code"), array("url" => "", "status" => 0));
						S(session("create_short_code"), null);
						session("create_short_code", null);
					}
				}
				break;
				/*=======================文件类型====================================*/
			case "file":

				if (!session("uid")) {
					$this->ajaxReturn(array(
						"status" => 0,
						"message" => "登陆后才能使用文件类型二维码"
					));
				}

				//文件描述
				$description = htmlspecialchars(str_filter(htmlspecialchars_decode($tempData['description'])));
				
				//文件加密
				$encrypt = $tempData['encrypt'];
				if ($encrypt != "") {
					if (!preg_match("/^[a-zA-Z0-9_-]{4,20}$/", $encrypt)) {
						$this->ajaxReturn(array(
								"status" => 0,
								"message" => "加密密码应为4-20位英文或数字"
						));
					}
				} else {
					$encrypt = "";				//空代表没加密
				}
				
				//文件名
				$filename = $tempData['filename'];
				if (!preg_match("/^.{1,100}$/", $filename)) {
					$this->ajaxReturn(array(
							"status" => 0,
							"message" => "文件名长度应在100个字符内"
					));
				}
				
				//文件用户名
				$username = $tempData['username'];
				if (!preg_match("/^.{1,40}$/", $username)) {
					$this->ajaxReturn(array(
							"status" => 0,
							"message" => "上传者用户名长度应在40个字符内"
					));
				}

				//检查文件是否已经上传
				if (!session("create_file_upload_path")) {
					$this->ajaxReturn(array(
							"status" => 0,
							"message" => "您还未上传文件"
					));
				}

				session("create_qrcode_type", "file");				//规定类型为文件

				if (session("create_short_code")) {
					$shortCode = session("create_short_code");
				} else {
					$shortCode = D("ShortUrl", "Service")->getOneShortUrlCode();
					session("create_short_code", $shortCode);
				}
				D("ShortUrl", "Service")->updateShortUrlData($shortCode, array("url" => C("GLOBAL_DIRECT_URL") . $shortCode));
				
				session("create_qrcode_value", C("SHORT_URL_DOMAIN") . $shortCode);
				S("create_" . $shortCode, array("create_qrcode_content" => json_encode(array(
					"filepath" => session("create_file_upload_path"),
					"filesize" => session("create_file_upload_size"),
					"filename" => $filename,
					"filedescription" => $description,
					"username" => $username,
					"encrypt" => $encrypt
				)), "create_qrcode_type" => "file"));
				
				break;

				/*========================== 名片类型 ======================*/
			case "mp": 
				session("create_qrcode_type", "mp");
				if (session("create_short_code")) {
					$shortCode = session("create_short_code");
				} else {
					$shortCode = D("ShortUrl", "Service")->getOneShortUrlCode();
					session("create_short_code", $shortCode);
				}
				D("ShortUrl", "Service")->updateShortUrlData($shortCode, array("url" => C("GLOBAL_DIRECT_URL") . $shortCode));
				
				session("create_qrcode_value", C("SHORT_URL_DOMAIN") . $shortCode);
				S("create_" . $shortCode, array("create_qrcode_content" => json_encode(array(
					"cardCover" => $tempData["cardCover"],
					"coverImageName" => $tempData["coverImageName"],
					"companyLogo" => $tempData["companyLogo"],
					"logoImageName" => $tempData["logoImageName"],
					"companyName" => $tempData["companyName"],
					"companyAddress" => $tempData["companyAddress"],
					"companySite"	 => $tempData["companySite"],
					"productInfo" => $tempData["productInfo"],
					"personAvatar" => $tempData["personAvatar"],
					"avatarImageName" => $tempData["avatarImageName"],
					"personName" => $tempData["personName"],
					"personJob" => $tempData["personJob"],
					"personPhone" => $tempData["personPhone"],
					"personEmail" =>  $tempData["personEmail"],
					"personSite" => $tempData["personSite"]
				)), "create_qrcode_type" => "mp"));
				break;

			case "phone":
				$phoneNumber = $tempData["phoneNumber"];

				if (!preg_match("/^[0-9-#\*]{3,20}$/", $phoneNumber)) {
					$this->ajaxReturn(array(
						"status" => 0,
						"message" => "电话号码格式错误"
					));
				}

				session("create_qrcode_type", "phone");							//电话类型
				$phoneContent = "{tel:" . $phoneNumber . "}";
				session("create_qrcode_value", $phoneContent);			//二维码编码的值
				session("create_qrcode_content", $phoneNumber);
				
				//如果存在短地址就把短地址去除掉
				if (session("create_short_code")) {
					D("ShortUrl", "Service")->updateShortUrlData(session("create_short_code"), array("url" => "", "status" => 0));
					S("create_" . session("create_short_code"), null);
					session("create_short_code", null);
				}
				break;
			case "message":
				$phoneNumber = $tempData["phoneNumber"];
				if (!preg_match("/^[0-9-#\*]{3,20}$/", $phoneNumber)) {
					$this->ajaxReturn(array(
						"status" => 0,
						"message" => "电话号码格式错误"
					));
				}

				session("create_qrcode_type", "message");
				$messageContent = $tempData["messageContent"];
				$messageValue = "{smsto:".$phoneNumber.":".$messageContent."}";
				
				session("create_qrcode_value", $messageValue);
				session("create_qrcode_content", json_encode(array(
					"phone" => $phoneNumber,
					"message" => $messageContent
				)));
				//如果存在短地址就把短地址去除掉
				if (session("create_short_code")) {
					D("ShortUrl", "Service")->updateShortUrlData(session("create_short_code"), array("url" => "", "status" => 0));
					S("create_" . session("create_short_code"), null);
					session("create_short_code", null);
				}
				break;
			case "video":
				if (!session("uid")) {
					$this->ajaxReturn(array(
						"status" => 0,
						"message" => "使用视频类型二维码必须登录"
					));
				}

				$videoname = $tempData['videoname'];
				$username = $tempData['username'];
				$videodes = $tempData['videodes'];

				if (!preg_match("/^.{1,100}$/", $videoname)) {
					$this->ajaxReturn(array(
						"status" => 0,
						"message" => "视频名长度应在100个字符内"
					));
				}

				if (!preg_match("/^.{1,40}$/", $username)) {
					$this->ajaxReturn(array(
						"status" => 0,
						"message" => "上传者用户名长度应在40个字符内"
					));
				}

				if (!session("create_video_upload_path")) {
					$this->ajaxReturn(array(
						"status" => 0,
						"message" => "您还未上传视频文件"
					));
				}

				session("create_qrcode_type", "video");
				if (session("create_short_code")) {
					$shortCode = session("create_short_code");
				} else {
					$shortCode = D("ShortUrl", "Service")->getOneShortUrlCode();
					session("create_short_code", $shortCode);
				}
				D("ShortUrl", "Service")->updateShortUrlData($shortCode, array("url" => C("GLOBAL_DIRECT_URL") . $shortCode));
				
				session("create_qrcode_value", C("SHORT_URL_DOMAIN") . $shortCode);
				S("create_" . $shortCode, array(
					"create_qrcode_content" => json_encode(array(
						"videoname" => $videoname,
						"videopath" => session("create_video_upload_path"),
						"username" => $username,
						"videosize" => session("create_video_upload_size"),
						"videoimage" => session("create_video_upload_image_path"),
						"videodes" => $videodes
					)),
					"create_qrcode_type" => "video"
				));
				break;
		}
		
        $this->ajaxReturn(array('status' => 1, "message" => "保存成功"));
	 }
	
	 
	 /**
	  * 文件上传
	  */
	 public function upload() {
	 	if (!empty($_FILES)) {
	 		$type = I('post.uploadType');
	 		$result = "";
	 		
	 		switch ($type) {
	 			case "create_qrcode_file":
	 				//使用文件二维码必须登录
	 				if (!session("uid")) {
	 					$this->ajaxReturn(array("response" => 0, "message" => "登陆后才能上传"));
	 				}

	 				$result = $this->uploadFile(array(
	 					"maxSize" => 10485760,			//文件最大50M
	 				));

	 				if ($result['response'] == 0) {
	 					$this->ajaxReturn(array(
	 						"response" => 0,
	 						"message" => $result['message']
	 					));
	 				} else {
	 					//如果之前上传过文件、则先删除
	 					if (session("create_file_upload_path")) {
	 						@unlink(session("create_file_upload_path"));
	 					}

	 					$filesize = $result['info']['size'];
	 					session("create_file_upload_path", $result['info']['filepath']);
	 					session("create_file_upload_size", $filesize);

	 					$this->ajaxReturn(array(
	 						"response" => 1,
	 						"filepath" => C("RESOURCE_DOMAIN") . $result['info']['filepath'],
	 						"filename" => $result['info']['name'],
	 						"filesize" => filesize_format($filesize)
	 					));
	 				}

	 				break;
	 			case "create_video":
	 				if (!session("uid")) {
	 					$this->ajaxReturn(array("response" => 0, "message" => "登陆后才能上传"));
	 				}
	 				$result = $this->uploadVideo(array(
	 					"maxSize" => 52428800,
	 				));

	 				if ($result['response'] == 0) {
	 					$this->ajaxReturn(array(
	 						'response' => 0,
	 						"message" => $result['message']
	 					));
	 				}

	 				//linux下为/usr/local/ffmpeg/ 这边仅作调试用
	 				passthru("/usr/bin/ffmpeg -i " . $result['info']['videopath'] . " -y -f image2 -ss 1 -vframes 1 " . $result['info']['savepath'] . $result['info']['savename'] . ".jpg");
	 				
	 				//重新上传、需要把原本的删除、节约空间
	 				if (session("create_video_upload_path")) {
	 					@unlink(session("create_video_upload_path"));
	 					@unlink(session("create_video_upload_image_path"));
	 				}

	 				session("create_video_upload_path", $result['info']['videopath']);
	 				session("create_video_upload_size", $result['info']['size']);
	 				session("create_video_upload_image_path", $result['info']['savepath'] . $result['info']['savename'] . ".jpg");
	 				$this->ajaxReturn(array(
	 					"response" => 1,
	 					"videopath" => C("RESOURCE_DOMAIN") . $result['info']['videopath'],
	 					"videosize" => filesize_format($result['info']['size']),
	 					"videoname" => $result['info']['name'],
	 					"videoimage" => C("RESOURCE_DOMAIN") . $result['info']['savepath'] . $result['info']['savename'] . ".jpg"
	 				));
	 				break;
	 			case "show_upload_video":
	 				$result = $this->uploadVideo(array(
	 					"maxSize" => 52428800,
	 					"exts" => array(
	 						"mov",
	 						"mp4",
	 						"avi",
	 						"mkv",
	 						"flv",
	 						"mpg",
	 						"swf",
	 						"wmv",

	 						"mp3",
	 						"m4a",
	 						"amr",
	 						"wav",
	 						"wma",
	 						"ape",
	 						"flac",
	 						"aac"
	 					)
	 				));

	 				if($result['response'] == 0) {
	 					$this->ajaxReturn(array(
	 						"response" => 0,
	 						"message" => $result['message']
	 					));
	 				}

	 				//当时音频的时候转换为mp3、删除原音频
	 				$fileExt = $result['info']['ext'];

	 				//去除后缀
	 				$videoPathString = explode(".", $result['info']['videopath']);
	 				array_pop($videoPathString);
	 				$videoPathWithoutExt = implode(".", $videoPathString);

	 				switch ($fileExt) {
	 					case 'mp3':
	 					case 'm4a':
	 					case "amr":
	 					case "wav":
	 					case "wma":
	 					case "ape":
	 					case "flac":
	 					case "aac":
	 					passthru("/usr/bin/ffmpeg -i " . $result['info']['videopath'] . $videoPathWithoutExt . ".mp3");
	 					//passthru("D:\\ffmpeg\\bin\\ffmpeg -i " . $result['info']['videopath'] . $videoPathWithoutExt . ".mp3");
	 					if ($fileExt != "mp3") {
	 						@unlink($result['info']['videopath']);
	 					}
	 					$result['info']['videopath'] = $videoPathWithoutExt . ".mp3";	//改变videoPath
	 					session("show_video_card_upload_type", "mp3");
	 					break;
	 					case "mp4":
	 					case "flv":
	 					case "mov":
	 					case "mpg":
	 					case "avi":
	 					case "swf":
	 					case "wmv":
	 					case "mkv":
	 					passthru("/usr/bin/ffmpeg -i " . $result['info']['videopath'] . $videoPathWithoutExt . ".mp4");
	 					//passthru("D:\\ffmpeg\\bin\\ffmpeg -i " . $result['info']['videopath'] . $videoPathWithoutExt . ".mp4");
	 					
	 					//不是mp4的文件删除
	 					if ($fileExt != "mp4") {
	 						@unlink($result['info']['videopath']);
	 					}
	 					$result['info']['videopath'] = $videoPathWithoutExt . ".mp4";
	 					session("show_video_card_upload_type", "mp4");
	 					//当用windows的时候使用下面/替换成自己的ffmpeg地址
	 					//passthru("D:\\ffmpeg\\bin\\ffmpeg -i " . $result['info']['videopath'] . " -y -f image2 -ss 1 -vframes 1 " . $videoPathWithoutExt . ".jpg");
	 					passthru("/usr/bin/ffmpeg -i " .  $videoPathWithoutExt . ".mp4 -y -f image2 -ss 1 -vframes 1 " .  $videoPathWithoutExt . ".jpg");
	 					break;
	 				}
	 				
	 				
	 				//重新上传、需要把原本的删除、节约空间
	 				if (session("show_video_card_upload_path")) {
	 					@unlink(session("show_video_card_upload_path"));
	 					@unlink(session("show_video_card_image_path"));
	 				}

	 				session("show_video_card_upload_path", $result['info']['videopath']);
	 				session("show_video_card_upload_size", $result['info']['size']);
	 				session("show_video_card_image_path", $videoPathWithoutExt . ".jpg");
	 				$this->ajaxReturn(array(
	 					"response" => 1,
	 					"videopath" => C("RESOURCE_DOMAIN") . $result['info']['videopath'],
	 					"videosize" => filesize_format($result['info']['size']),
	 					"videoimage" => C("RESOURCE_DOMAIN") . $videoPathWithoutExt . ".jpg",
	 					"uploadtype" => session("show_video_card_upload_type")
	 				));
	 				break;
	 			case "modify_background_image":
	 				
	 				if (!session("create_qrcode_type")) {
	 					$this->ajaxReturn(array(
	 						"response" => 1,
	 						"message" => "你还未选择二维码类型"
	 					));
	 				}
	 				
	 				$result = $this->uploadImage();
	 				session("create_modify_background_image", $result['info']['imagepath']);
	 				
	 				$this->ajaxReturn(array(
	 						"response" => 1,
	 						"imagepath" =>  $result['info']['imagepath'],
	 						"width" => $result['info']['width'],
	 						"height" => $result['info']['height']
	 				));
	 				break;
	 			case "modify_logo_image":
	 				if (!session("create_qrcode_type")) {
	 					$this->ajaxReturn(array(
	 							"response" => 1,
	 							"message" => "你还未选择二维码类型"
	 					));
	 				}
	 				
	 				$result = $this->uploadImage();

	 				//若之前上传过图片、则先删除之前的
	 				if (session("create_modify_logo_image")) {
	 					@unlink(session("create_modify_logo_image"));
	 				}

	 				session("create_modify_logo_image", $result['info']['imagepath']);
	 				$this->ajaxReturn(array(
	 						"response" => 1,
	 						"imagepath" => C("RESOURCE_DOMAIN") . $result['info']['imagepath'],
	 						"width" => $result['info']['width'],
	 						"height" => $result['info']['height']
	 				));
	 				break;
	 			case "wifi_logo":
	 				$result = $this->uploadImage();
	 				//上传失败
	 				if ($result['response'] == 0) {
	 					$this->ajaxReturn(array(
	 						"response" => 0,
	 						"message" => $result['message']
	 					));
	 				} else {

	 					//若之前上传过图片、则先删除之前的
		 				if (session("create_wifi_logo_path")) {
		 					@unlink(session("create_wifi_logo_path"));
		 				}
	 					session("create_wifi_logo_path", $result['info']['imagepath']);
		 				$this->ajaxReturn(array(
		 					"response" => 1,
		 					"imagepath" => C("RESOURCE_DOMAIN") . $result['info']['imagepath'],
		 				));
	 				}
	 				break;
	 			default:
	 				$result = array(
	 					"response" => 0,
	 					"message" => "未知的上传类型"
	 				);
	 			break;
	 		}
	 		$this->ajaxReturn($result);
	 	} else {
	 		$this->ajaxReturn(array(
	 			"response" => 0,
	 			"message" => "您未选择文件"
	 		));
	 	}
	 }
	 
	 /**
	  * ======================上传文件（一般概念上的文件）============================
	  */
	 private function uploadFile($options) {
	 	$upload = new \Think\Upload (); // 实例化上传类
	 	$upload->maxSize = $options['maxSize'] ? $options['maxSize'] : 52428800; // 设置附件上传大小
	 	$upload->rootPath = 'data/'; // 设置附件上传目录
	 	$upload->savePath = "uploads/files/";
	 	$upload->saveName = time() . "_" . mt_rand();
	 	$upload->autoSub = true;
	 	$upload->subName = array("date", "Ymd");
	 	$upload->exts = array (
	 			'doc','docx','ppt','pptx','pdf','xls','xlsx','wps','jpg','png','jpeg','gif','bmp','psd','zip','rar','torrent',	
	 	); // 设置附件上传后缀

	 	$info =  $upload->upload();
	 	if (!$info) { // 上传错误提示错误信息
	 		return array (
 				"response" => 0,
 				"message" => $upload->getError()
	 		);
	 	} else { // 上传成功 获取上传文件信息
	 		$info[0]['savepath'] = $upload->rootPath . $info[0]['savepath'];
	 		$savepath = $info[0]['savepath'] .$info[0]['savename'];	 	
	 		$info[0]['filepath'] = $savepath;
	 		
	 		return array(
	 			"response" => 1,
	 			"info" => $info[0]
	 		);
	 	}
	 }
	 
	/**
	 * ======================上传图片========================
	 * @param $options  选择项
	 * @return 
	 */
	private function uploadImage($options) {
		$upload = new \Think\Upload (); // 实例化上传类
		$upload->maxSize = $options['maxSize'] ? $options['maxSize'] : 524800;   // 设置附件上传大小
		$upload->rootPath = "data/";
		$upload->savePath = "uploads/images/";
		$upload->saveName = time() . "_" . mt_rand();
		$upload->autoSub = true;
		$upload->subName = array("date", "Ymd");
		$upload->exts = array (
				'jpg',
				'jpeg',
				'png',
				'gif',
				'bmp'
		); // 设置附件上传后缀
		$upload->type = array(
			"image/png",
			"image/jpeg",
			'image/gif'
		);
			
		$info =  $upload->upload();
		
		if (!$info) { // 上传错误提示错误信息
			return array (
				"response" => 0,
				"message" => $upload->getError () 
			);
		} else { // 上传成功 获取图片信息
			$imgSize = $info[0]['size'];
			$info[0]['savepath'] = $upload->rootPath . $info[0]['savepath'];
			$imagepath = $info[0]['savepath'] .$info[0]['savename'];
			$imgInfo = getimagesize($imagepath);
			
			$info[0]['width'] = $imgInfo[0];
			$info[0]['height'] = $imgInfo[1];
			$info[0]['imagepath'] = $imagepath;

			return array(
				"response" => 1,
				"info" => $info[0]
			);
		}
	}
	/**
	 * =========================上传video============================
	 */
	private function uploadVideo($options) {
		$upload = new \Think\Upload (); // 实例化上传类
		$upload->maxSize = $options['maxSize'] ? $options['maxSize'] : 524800; // 设置附件上传大小
		$upload->rootPath = 'data/'; // 设置附件上传目录
		$upload->savePath = "uploads/videos/";
		$upload->saveName = time() . "_" . mt_rand();
		$upload->autoSub = true;
		$upload->subName = array("date", "Ymd");
		$upload->exts = $options['exts'] ? $options['exts'] : array (
				'wav',
				'mp4',
				'avi',
				'rmvb',
				"mp3"
		); // 设置附件上传后缀

		$info =  $upload->upload();
		if (!$info) {
			return array (
					"response" => 0,
					"message" => $upload->getError()
			);
		} else { // 上传成功 获取上传文件信息
			$info[0]['savepath'] = $upload->rootPath . $info[0]['savepath'];
			$filepath = $info[0]['savepath'] .$info[0]['savename'];
			$info[0]['videopath'] = $filepath;
			return array (
				"response" => 1,
				"info" => $info[0]
			);
		}
	} 
}
