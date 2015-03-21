<?php

/*
 * 二维码生成相关业务
 * @param $savepath 储存路径 相对地址
 */

namespace Home\Service;

class QRCodeService extends BaseService{
	
	public $savepath = "";
	
	/*
	 * 生成二维码
	 */
	public function createQRCode($globalValue, $options) {
        
		Vendor('phpqrcode.phpqrcode');
		$options['savepath'] = $options['savepath'] . date("Ymd", time()) . "/";			//加个时间
		
		if (!is_dir($imagePath)) {
			mkdir($options['saveroot'] . $options['savepath'], 0777, true);
		}
				
		$imageName = $options['qrcode_image_name'];					//名字是预定义的
		$imagePath = $options['saveroot'] . $options['savepath'] . $imageName;				//以/data为根目录的路径/便于储存
		$absolutePath = C("RESOURCE_DOMAIN") .  $options['savepath'] . $imageName;			//http://m.uscdn.net/xxx/xxx/xx.png
		$value = $options['qrcode_value'];
		
		// 用于判断是否使用效果
		define("QR_EFFECT", $globalValue['enhance'] == "true" ? true : false);
		
		$codeEyeType = $globalValue['codeEyeType'] == null ? "square" : $globalValue['codeEyeType'];
 		
		//二维码边距
		$qrcodeMargin = $globalValue['qrcodeMargin'] == null ? 1 : intval($globalValue['qrcodeMargin']);				
		
		//二维码背景颜色
		$backgroundColorR = $globalValue['backgroundColor']['r'] == null ? 255 : intval($globalValue['backgroundColor']['r']);
		$backgroundColorG = $globalValue['backgroundColor']['g'] == null ? 255 : intval($globalValue['backgroundColor']['g']);
		$backgroundColorB =  $globalValue['backgroundColor']['b'] == null ? 255 : intval($globalValue['backgroundColor']['b']);
		
		//二维码前景颜色
		$foregroundColorR = $globalValue['foregroundColor']['r'] == null ? 0 : intval($globalValue['foregroundColor']['r']);
		$foregroundColorG = $globalValue['foregroundColor']['g'] == null ? 0 : intval($globalValue['foregroundColor']['g']);
		$foregroundColorB =  $globalValue['foregroundColor']['b'] == null ? 0 : intval($globalValue['foregroundColor']['b']);
		
		//码眼的颜色
		$codeEyeColorR = $globalValue['codeEyeColor']['r'] == null ? 0 : intval($globalValue['codeEyeColor']['r']);
		$codeEyeColorG = $globalValue['codeEyeColor']['g'] == null ? 0 : intval($globalValue['codeEyeColor']['g']);
		$codeEyeColorB = $globalValue['codeEyeColor']['b'] == null ? 0 : intval($globalValue['codeEyeColor']['b']);
		
		$imageScale = 1;		                                                                                      //二维码图片放大的倍数
		$errorLevel = $globalValue['errorLevel'] == null ? "M" : $globalValue['errorLevel'];			//二维码容错率
				
        // 幕布背景颜色设定
        define("IMAGE_BACKGROUND_COLOR", true);
        define("IMAGE_BACKGROUND_COLOR_R", $backgroundColorR);
        define("IMAGE_BACKGROUND_COLOR_G", $backgroundColorG);
        define("IMAGE_BACKGROUND_COLOR_B", $backgroundColorB);
        
        // 对背景图片是否存在做判断
        if (!!$globalValue["backgroundImage"]) {
        	define("IMAGE_BACKGROUND_IMAGE", true);		//如果有图片 根据图片大小更改幕布大小
        	define("IMAGE_WIDTH", $globalValue["backgroundImageWidth"]);
        	define("IMAGE_HEIGHT", $globalValue["backgroundImageHeight"]);
        	$imageScale = $globalValue['backgroundImageScale'] == null ? 1 : $globalValue['backgroundImageScale'];
        } else {
        	define("IMAGE_WIDTH", 400);
        	define("IMAGE_HEIGHT", 400);
        }
        
        define("IMAGE_BACKGROUND_IMAGE_URL", $globalValue["backgroundImage"]);
        define("IMAGE_BACKGROUND_IMAGE_POSX", 0);
        define("IMAGE_BACKGROUND_IMAGE_POSY", 0);
        define("IMAGE_BACKGROUND_IMAGE_WIDTH", $globalValue["backgroundImageWidth"]);
        define("IMAGE_BACKGROUND_IMAGE_HEIGHT",$globalValue['backgroundImageHeight']);

        // 生成二维码的大小
        define("QR_WIDTH", $globalValue['qrWidth'] * $imageScale);
        define("QR_HEIGHT", $globalValue['qrHeight'] * $imageScale);

        // 生成二维码的位置
        define("QR_POSX", $globalValue['posX']);
        define("QR_POSY", $globalValue['posY']);

        // 二维码是否采用素材图片进行抠白点,这个会忽视下面眼码和dotfill的设置
        // 素材图片效果 > 背景嵌入效果
        // 设置了素材图片效果，背景嵌入效果会被忽略
        define("QR_MATERIAL", $globalValue['material'] == "true" ? true : false);
        define("QR_MATERIAL_IMG", $globalValue['materialImage']);

        // 二维码是否生成背景图片嵌入式二维码
        define("QR_BGINSERT", $globalValue['bginsert'] == "true" ? true : false);

        // 深色填充物的颜色设定
        define("QR_DOTFILL_FOREGROUND_COLOR", true);
        define("QR_DOTFILL_FOREGROUND_COLOR_R", $foregroundColorR);
        define("QR_DOTFILL_FOREGROUND_COLOR_G", $foregroundColorG);
        define("QR_DOTFILL_FOREGROUND_COLOR_B", $foregroundColorB);
        // 浅色填充物的颜色设定
        define("QR_DOTFILL_BACKGROUND_COLOR", true);
        define("QR_DOTFILL_BACKGROUND_COLOR_R", $backgroundColorR);
        define("QR_DOTFILL_BACKGROUND_COLOR_G", $backgroundColorG);
        define("QR_DOTFILL_BACKGROUND_COLOR_B", $backgroundColorB);
        
        // 眼码填充物的颜色设定
        define("QR_CODEEYE_COLOR", true);
        define("QR_CODEEYE_COLOR_R", $codeEyeColorR);
        define("QR_CODEEYE_COLOR_G", $codeEyeColorG);
        define("QR_CODEEYE_COLOR_B", $codeEyeColorB);
        
        define('QR_CODEEYE_FOREGROUND_ALPHA', 30);
        define('QR_CODEEYE_BACKGROUND_ALPHA', 50);
        
        // 填充物透明度设定 (透明度范围在0 ~ 127之间, 推荐值: 背景20, 前景50)
        if (QR_EFFECT) {
        	define("QR_DOTFILL_FOREGROUND_ALPHA", 40);
        	define("QR_DOTFILL_BACKGROUND_ALPHA", 60);
        } else {
        	define("QR_DOTFILL_FOREGROUND_ALPHA", 0);
        	define("QR_DOTFILL_BACKGROUND_ALPHA", 0);
        }
        
        // 填充物样式设定
        define("QR_DOTFILL_FOREGROUND_TYPE", 'square');
        define("QR_DOTFILL_BACKGROUND_TYPE", 'square');
        
        // 眼码样式设定
        define("QR_CODEEYE_TYPE", $codeEyeType);
        
        // 嵌入文字
        define("INSERT_TEXT",  !!$globalValue['insertText']);
        // 文字内容
        define("INSERT_TEXT_CONTENT", $globalValue['insertTextValue']);
        // 目录static/fonts/下的字体文件名
        define("INSERT_TEXT_FONT_TYPE", "dutch");
        define("INSERT_TEXT_FONT_BOLD", $globalValue['insertTextBold']);
        define("INSERT_TEXT_FONT_SIZE", !!$globalValue['insertTextSize'] ? 14 : $globalValue['insertTextSize']);
        // 字体颜色
        define("INSERT_TEXT_FOREGROUND_COLOR", true);
        define("INSERT_TEXT_FOREGROUND_COLOR_R", $globalValue['insertTextColor']['r']);
        define("INSERT_TEXT_FOREGROUND_COLOR_G", $globalValue['insertTextColor']['g']);
        define("INSERT_TEXT_FOREGROUND_COLOR_B", $globalValue['insertTextColor']['b']);
        // 背景颜色
        define("INSERT_TEXT_BACKGROUND_COLOR", false);
        define("INSERT_TEXT_BACKGROUND_COLOR_R", $globalValue['insertTextBackgroundColor']['r']);
        define("INSERT_TEXT_BACKGROUND_COLOR_G", $globalValue['insertTextBackgroundColor']['g']);
        define("INSERT_TEXT_BACKGROUND_COLOR_B", $globalValue['insertTextBackgroundColor']['b']);
     
        
        // 文字的padding
        define("INSERT_TEXT_FONT_PADDING", 6);
        // 文字的位置
        define("INSERT_TEXT_POSX", $globalValue['insertTextPos']['x']);
        define("INSERT_TEXT_POSY", $globalValue['insertTextPos']['y']);

        
        // 嵌入Logo
        define("INSERT_LOGO", !!$globalValue['logoImage'] ? true : false);
        define("INSERT_LOGO_URL", $globalValue['logoImage']);
        // Logo位置
        define("INSERT_LOGO_POSX", $globalValue['logoImagePos']['x']);
        define("INSERT_LOGO_POSY", $globalValue['logoImagePos']['y']);
        // Logo大小
        define("INSERT_LOGO_WIDTH", $globalValue['logoImageSize']['width']);
        define("INSERT_LOGO_HEIGHT", $globalValue['logoImageSize']['height']);


        // 二维码特效关闭之后的设定:
        // 根据草料二维码, 暂时实现圆形,斜线,反斜线,水平和垂直五种渐变
        if (!QR_EFFECT) {
        	$gradientType = $globalValue['gradientType'];
        	switch ($gradientType) {
        		case "slash":
	        		define("QR_GRADIENT_STYLE", "linear");
	        		// 如果为线性渐变, 则需要设置向量
	        		define("QR_GRADIENT_LINEAR_VECTOR_X", 1);
	        		define("QR_GRADIENT_LINEAR_VECTOR_Y", 1);
	        		 
	        		define("QR_GRADIENT_COLOR_R", $globalValue['gradientColor']['r']);
	        		define("QR_GRADIENT_COLOR_G", $globalValue['gradientColor']['g']);
	        		define("QR_GRADIENT_COLOR_B", $globalValue['gradientColor']['b']);
        			break;
        		case "center":
        			define("QR_GRADIENT_STYLE", "round");
        			define("QR_GRADIENT_COLOR_R", $globalValue['gradientColor']['r']);
        			define("QR_GRADIENT_COLOR_G", $globalValue['gradientColor']['g']);
        			define("QR_GRADIENT_COLOR_B", $globalValue['gradientColor']['b']);
        			break;
        		case "back-slash":
        			define("QR_GRADIENT_STYLE", "linear");
        			// 如果为线性渐变, 则需要设置向量
        			define("QR_GRADIENT_LINEAR_VECTOR_X", -1);
        			define("QR_GRADIENT_LINEAR_VECTOR_Y", -1);
        			
        			define("QR_GRADIENT_COLOR_R", $globalValue['gradientColor']['r']);
        			define("QR_GRADIENT_COLOR_G", $globalValue['gradientColor']['g']);
        			define("QR_GRADIENT_COLOR_B", $globalValue['gradientColor']['b']);
        			break;
        		case "vertical":
        			define("QR_GRADIENT_STYLE", "linear");
        			// 如果为线性渐变, 则需要设置向量
        			define("QR_GRADIENT_LINEAR_VECTOR_X", 1);
        			define("QR_GRADIENT_LINEAR_VECTOR_Y", 0);
        			 
        			define("QR_GRADIENT_COLOR_R", $globalValue['gradientColor']['r']);
        			define("QR_GRADIENT_COLOR_G", $globalValue['gradientColor']['g']);
        			define("QR_GRADIENT_COLOR_B", $globalValue['gradientColor']['b']);
        			break;
        		case "horizontal":
        			define("QR_GRADIENT_STYLE", "linear");
        			// 如果为线性渐变, 则需要设置向量
        			define("QR_GRADIENT_LINEAR_VECTOR_X", 0);
        			define("QR_GRADIENT_LINEAR_VECTOR_Y", 1);
        			
       				define("QR_GRADIENT_COLOR_R", $globalValue['gradientColor']['r']);
       				define("QR_GRADIENT_COLOR_G", $globalValue['gradientColor']['g']);
       				define("QR_GRADIENT_COLOR_B", $globalValue['gradientColor']['b']);
       				break;        		
        	}
        	
        }
       

		
        ob_start();

		\QRcode::png($value, false, $errorLevel, 10, $qrcodeMargin); 
        $qrcode = ob_get_contents();
         ob_end_clean();

        // qrcode图像数据  最终合成图片的输出地址  二维码坐标   二维码大小
        \QRcode::merge($qrcode, $imagePath);
		
		return $absolutePath;
	}
	
	
	/**
	 * 手机客户端自动同步
	 * @param $uid 用户id 
	 * @param $qrcodeArr 客户端传送过来的数组
	 * @return $result 返回给客户端的数据
	 */
	public function syncQRCode($uid, $qrcodeArr) {
		$scanSyncModel = D("UserScanSync");
		
		$result; //对比并返回客户端的结果
		$sqlResult;   //对比并写入或修改数据库的内容
		
		$scanList = $scanSyncModel->where("uid = '$uid'")->find();			//已经存在数据库中的数据
		$scanSyncModel->where("uid = '$uid'")->delete();					//先清空数据库
		$scanListCount = count($scanList);
		$qrcodeArrCount = count($qrcodeArrCount);
		
		for ($scanListIndex = 0; $scanListIndex < $scanListCount; ++$scanListIndex) {
			for ($qrcodeArrIndex = 0; $qrcodeArrIndex < $qrcodeArrCount; ++$qrcodeArrIndex) {
				if ($qrcodeArr[$qrcodeArrIndex]['QRcontent'] == $scanList[$scanListIndex]['content']) {
					$qrcodeArr[$qrcodeArrIndex]['tag'] = true;
					$scanList[$scanListIndex]['tag'] = true;
					if (strtotime($qrcodeArr[$qrcodeArrIndex]['UpdateTime']) > strtotime($scanList[$scanListIndex]['update_time'])) {
						if ($qrcodeArr[$qrcodeArrIndex]['ModifyType'] == 0) {
							 $scanList[$scanListIndex]['update_time'] = $qrcodeArr['UpdateTime'];
							 array_push($result, $qrcodeArr[$qrcodeArrIndex]);
							 array_push($sqlResult, $scanList[$scanListIndex]);
						} else if ($qrcodeArr[$qrcodeArrIndex]['ModifyType'] == 1) {
							$scanList[$scanListIndex]['update_time'] = $qrcodeArr['UpdateTime'];
							$scanList[$scanListIndex]['stored'] = $qrcodeArr['IsStar'];
							array_push($result, $qrcodeArr[$qrcodeArrIndex]);
							array_push($sqlResult, $scanList[$scanListIndex]);
						} else if ($qrcodeArr[$qrcodeArrIndex]['ModifyType'] == 2) {
							$scanList[$scanListIndex]['delete'] = true;
							array_push($sqlResult, $scanList[$scanListIndex]);
						}
					} else {
						$tmpArr['QRcontent'] = $scanList[$scanListIndex]['content'];
						$tmpArr['UpdateTime'] = $scanList[$scanListIndex]['update_time'];
						$tmpArr['QRType'] = $scanList[$scanListIndex]['type'];
						$tmpArr['IsStar'] = $scanList[$scanListIndex]['stored'];
						array_push($result, $tmpArr);
					}
				}
			}
		}
	
		//未标记的部分代表数据库中存在的这条是唯一的，客户端不存在拷贝
		for ($scanListIndex = 0; $scanListIndex < $scanListCount; ++$scanListIndex) {
			if (!$scanList[$scanListIndex]['tag']) {
				$tmpArr['QRcontent'] = $scanList[$scanListIndex]['content'];
				$tmpArr['UpdateTime'] = $scanList[$scanListIndex]['update_time'];
				$tmpArr['QRType'] = $scanList[$scanListIndex]['type'];
				$tmpArr['IsStar'] = $scanList[$scanListIndex]['stored'];
				array_push($result, $tmpArr);
			}
		}
		
		//未标记的部分代表客户端传过来的二维码内容是最新的、数据库中不存在拷贝
		for ($qrcodeArrIndex = 0; $qrcodeArrIndex < $qrcodeArrCount; ++$qrcodeArrIndex) {
			if (!$qrcodeArr[$qrcodeArrIndex]['tag']) {
				array_push($result, $qrcodeArr[$qrcodeArrIndex]);
			}
		}
		
		for ($sqlIndex = 0; $sqlIndex < count($sqlResult); ++$sqlIndex) {
			$scanSyncModel->add($sqlResult[$sqlIndex]);
		}
		
		return $result;
	}
	
	
	/**
	 * ==================新的二维码数据存储===================
	 * @param $data  二维码数据
	 * 	 */
	public function saveQRCodeData($data) {
		$uid = session("?uid") ? session("uid") : 0;				//用户不登陆状态下uid为0
		
		$qrId = D("Qr")->add(array(
			"type" => $data['type'],
			"image" => $data['imagePath'],
			"generate_time" => date("Y-m-d H:i:s", time()),
			"expired" => 0,
			"uid" => $uid,
			"short_code" => $data['shortCode'],
			"content" => $data['content'],
			"visit_count" => 0,
			"status" => 1
		));

		if ($qrId) {
			return true;
		} else {
			return false;
		}
	}
	

	/**
	 * ==================新的二维码批量数据存储===================
	 * @param $data  二维码数据
	 * @author 李展威
	 * 2014-10-18
	 */
	public function batchQRCodeData($data) {
		$result = D('Qr')->addAll($data);
		return $result;
		if ($result) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * ==================二维码数据批量读取===================
	 * @param $condition  查询条件
	 * @param $order  排序
	 * @param $page  开始位置
	 * @param $num  查询数目
	 * @author 李展威
	 * 2014-10-18
	 */
	public function fetchQRCodeData($condition, $order = "", $start = 0, $num = 0) {
		if ($num == 0) {
			$result = D('Qr')->where($condition)->order($order)->select();
		} else {
			$result = D('Qr')->where($condition)->order($order)->limit($start, $num)->select();
		}
		
		return $result;
	}


	/**
	 * ==================更新已有二维码状态===================
	 * @param $qrId  二维码id
	 * @param $shorturl  二维码短网址
	 * @param $status  状态
	 * @author 李展威
	 * 2014-10-18
	 */
	public function updateQRCodeStatus($status, $qrId = 0, $shortCode = "") {
		if ($qrId == 0) {
			$result = D("Qr")->where("short_code = '$shortCode'")->save(array("status" => $status));
		} else {
			$result = D("Qr")->where("id = '$qrId'")->save(array("status" => $status));
		}

		return $result;
	}

	/**
	 * =================更新已有二维码数据=======================
	 */
	public function updateQRCodeData($shortCode, $data) {
		D("Qr")->where("short_code = '$shortCode'")->save(array(
			"uid" => $data['uid'],
			"content" => $data['content']
		));
	}
	
	/**
	 * 根据用户id获取QRCode信息
	 */
	public function getQRCodeList($uid) {
		return D("Qr")->where("uid = '$uid' AND status <> 0")->order("generate_time desc")->select();
	}
	
	/**
	 * 获取二维码信息/只需要其中一个就可以获取二维码信息
	 * @param $shortCode 短地址
	 * @param $qrId 二维码ID   
	 */
	public function getQRCodeData($shortCode = "", $qrId = 0) {
		if ($shortCode != "") {
			return D("Qr")->where("short_code = '$shortCode'")->find();
		} else if ($qrId != 0) {
			return D("Qr")->where("id = '$qrId'")->find();
		} else {
			return false;
		}
	}
	
	/**
	 * ===================删除二维码=========================
	 * @param $shortCode 二维码短地址
	 * @param $qrId 二维码在数据中的ID
	 */
	public function deleteQRCode($shortCode = "", $qrId = 0, $uid) {
		if ($shortCode != "") {
			return D("Qr")->where("short_code = '$shortCode' AND uid = '$uid'")->save(array(
				"status" => 0
			));
		} else if ($qrId != 0 && $qrId != "") {
			return D("Qr")->where("id = '$qrId' AND uid = '$uid'")->save(array(
					"status" => 0
			));
		} else {
			return false;
		}
	}

	/**
	* =====================更新二维码具体页面访问数量（跟扫描无关）===================================
	*/
	public function updateVisitRecord($id) {
		$device = get_device_data();
		$systemData = get_system_data();
		$browser = get_browser_data();
		
		$data['short_code'] = $shortCode;
		$data['ip'] = get_client_ip(0, 1);
		$data['uid'] = session("uid") ? session("uid") : 0;
		$data['system'] = $systemData['system'];
		$data['system_version'] = $systemData['version'];
		$data['browser'] = $browser['browser'];
		$data['browser_version'] = $browser['version'];
		$data['device'] = $device;
		$data['http_refer'] = $_SERVER["HTTP_REFERER"] ? $_SERVER["HTTP_REFERER"] : "";
		$data['ctime'] = date("Y-m-d H:i:s", time());
		$data['http_user_agent'] = $_SERVER['HTTP_USER_AGENT']; 
		
		$Ip = new \Org\Net\IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
		$location = $Ip->getlocation();
		$data['location'] = $location['country'] . " " . $location['area'];
		D("Qr")->where("id = '$id'")->setInc("visit_count", 1);			//增加浏览量
		D("QrVisitRecord")->add($data);
	}
}