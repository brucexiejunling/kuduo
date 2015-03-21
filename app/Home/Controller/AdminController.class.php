<?php

namespace Home\Controller;
use Think\Controller;

class AdminController extends Controller {
	/**
	 * 后台管理首页
	 * 
	 * @author 李展威 <zhanweelee@gmail.com>
	 */
	public function index() {
		$this->display ();
	}
	

	 /*
	  * 如果是有声类型二维码、进入美化页面就自动分配一个短地址
	  * 如果是普通类型的二维码、则直接根据内容来生成
	  */
    public function preview(){
        $type = I("post.qrCodeType");
        $qrCodeValue = I("post.qrCodeValue");             //管理员页面设置批量二维码内容 !?!
        $globalValue = I("post.globalValue");             //用户二维码的设置

        if (!session("create_qrcode_image_name")) {
        	session("create_qrcode_image_name", md5(session_id() . time()) . ".png"); 
        }
        
		$imagePath = D("QRCode", "Service")->createQRCode($globalValue, array(
			"savepath" => "data/preview/",
			"qrcode_value" => $qrCodeValue,
			"qrcode_image_name" => session("create_qrcode_image_name"),
		));
		
		$this->ajaxReturn(array('msg' => '生成成功', "flag" => 1, 'src' => $imagePath));
    }


	/**
	 * 二维码批量生成函数
	 * 
	 * @param
	 * @author 李展威 <zhanweelee@gmail.com>
	 */
	public function batchGenerate() {
		$tempData = I("post.");					//获取前台传递过来的数据
	 	$shortCode = "";

	 	// 设置批量处理时间无限制
	 	set_time_limit(0);

		switch ($tempData['type']) {
			/**=============================text类型======================**/
			case 'lost_card':	
				$qrCodeType = $tempData['type'];
				$codeName = $tempData['code_name'];
		    	$globalValue = I("post.globalValue");             //用户二维码的设置

		    	// 添加印刷批次数据

		    	$printedId = D("QrPrinted", "Service")->getPrintedDataByCodeName($codeName);
		    	if (!$printedId) {
		    		$printedId = D("QrPrinted", "Service")->createPrintedData($qrCodeType, $codeName, 0);
		    	}

		    	if (!file_exists("data/qrcode/printed/" . $codeName)) {
		    		mkdir("data/qrcode/printed/" . $codeName, 0777, true);
		    	}

		    	// 批量生成数目
		    	$batchAmount = $tempData['batchAmount'];
		    	$count = 0;
		    	$qrData = array();
		    	$shortUrlData = D("ShortUrl", "Service")->getMultiShortUrlCode($batchAmount);

		    	// 如果获取到可用短地址数量不足，则不进行批量生成
		    	if (count($shortUrlData) < $batchAmount) {
		    		for ($i = 0; $i < count($shortUrlData); $i ++) {
		    			$shortUrlData[$i]['status'] = 0;
		    		}

		    		// 解除已获取短地址的锁定状态
		    		D("ShortUrl", "Service")->updateMultiShortUrlCode($shortUrlData);
		    		$this->ajaxReturn(array('status' => 0, 'message' => '可用短地址数量不足'));
		    	}

		    	for ($count = 0; $count < $batchAmount; $count ++) {
		    		$shortCode = $shortUrlData[$count]['short_code'];

		    		// 更新短地址状态
		    		$shortUrlData[$count]['status'] = 1;
		    		$shortUrlData[$count]['url'] = C("GLOBAL_DIRECT_URL") . $shortCode;

			    	$qrCodeValue = C("SHORT_URL_DOMAIN") . $shortCode;

			    	//每次生成1000张....这样还会重复我就去见阎王爷！
			        $qrCodeImage = md5(session_id() . time() . mt_rand(0, 10000) . mt_rand(0, 10000) . mt_rand(0, 10000)) . ".png";

			    	$imagePath = D("QRCode", "Service")->createQRCode($globalValue, array(
			    			"savepath" => "data/qrcode/printed/" . $codeName . "/",
			    			"qrcode_value" => $qrCodeValue,
			    			"qrcode_image_name" => $qrCodeImage
			    	));

			    	$qrData[] = array(
						"type" => $qrCodeType,
						"image" => $imagePath,
						"generate_time" => date("Y-m-d H:i:s", time()),
						"expired" => 0,
						"uid" => 0,
						"short_code" => $shortCode,
						"content" => "",
						"visit_count" => 0,
						"status" => 1,
						'code_name' => $codeName,
					);
			    }

		    	$result = D("QRCode", "Service")->batchQRCodeData($qrData);

			    // 批量更新短地址状态已用
				D("ShortUrl", "Service")->updateMultiShortUrlCode($shortUrlData);
		}
		
        $this->ajaxReturn(array('status' => 1, "message" => "总共生成" . $count . "张"));
	}

	/**
	 * 下载批次所有二维码
	 * @param $codeName 批次代号
	 * @author 李展威
	 */
	public function downloadByCodeName() {
		$codeName = I('get.codeName');

		if (!$codeName) {
			$this->ajaxReturn(array('status' => 0, 'message' => '代码不合法'));
		}
		$printedId = D("QrPrinted", "Service")->getPrintedDataByCodeName($codeName);
    	if (!$printedId || !is_dir("data/qrcode/printed/" . $codeName)) {
    		$this->ajaxReturn(array('status' => 0, 'message' => '代码不合法'));
    	}

    	$filename = "data/qrcode/iKuduo_" . $codeName . ".zip";
		Vendor('PclZip.PclZip');
		$archiver = new \PclZip($filename);
		$v_list = $archiver->create("data/qrcode/printed/" . $codeName, PCLZIP_OPT_REMOVE_PATH, 'data/qrcode/printed/');
		if ($v_list == 0) {
			die("Error : ".$archive->errorInfo(true));
		}

		$data = file_get_contents($filename);

		header("Cache-Control: public"); 
		header("Content-Description: File Transfer"); 
		header('Content-disposition: attachment; filename='.basename($filename)); //文件名   
		header("Content-Type: application/zip"); //zip格式的   
		header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件    
		header('Content-Length: '. filesize($filename)); //告诉浏览器，文件大小   
		//@readfile($filename);
		echo $data;
		@unlink($filename);
	}	


	/**
	* 印刷产品管理页面
	* @param get.type 二维码类型，如lost_card，video_card
	* @param get.codename 批次代号 如果没有则显示批次列表
	* @author 李展威
	*/
	public function printed() {
		$type = I('get.type');
		if (I('get.codeName')) {
			$codeName = I('get.codeName');
			if ($codeName) {
				$qrCodeData = D("QRCode", "Service")->fetchQRCodeData(array("code_name" => $codeName));
				$this->assign("qrCodeData", $qrCodeData);
			}
		} else {
			$qrCodeData = D("QRCode", "Service")->fetchQRCodeData(array("type" => $type));
			$state = array(
				"total" => count($qrCodeData),
				"used" => 0,
				"unused" => 0,
				"locked" => 0);
			for ($i = 0; $i < $total; $i ++) {
				if ($qrCodeData[$i]['status'] == 0) {
					++$state['unused'];
				} else if ($qrCodeData[$i]['status'] == 1) {
					++$state['used'];
				} else if ($qrCodeData[$i]['status'] == 2) {
					++$state['locked'];
				}
			}
			$printedData = D("QrPrinted", "Service")->getPrintedDataListByType($type);
			$state['batch'] = count($printedData);
			$this->assign("printedData", $printedData);
			$this->assign("state", $state);
		}
		$this->display();
	}

	/**
	 * 短地址管理的页面
	 * 显示已生成的短地址和使用情况
	 * @author 李展威 <zhanweelee@gmail.com>
	 */
	public function shorturl() {
		$state = D("ShortUrl", "Service")->getShortCodeUseInfo();
		$this->assign("state", $state);
		$this->display ();
	}
	
	/**
	 * 设置某个二维码状态
 	 * 
	 */
	public function updateStatus() {
		$status = I('post.status');
		if (!is_numeric($status)) {
			$this->ajaxReturn(array("status" => false, "message" => "二维码状态不合法"));
		}
		$qrId = I('post.qrId', null, 0);
		$shortCode = I('post.shortCode', "", "");
		$result = D('QRCode', 'Service')->updateQRCodeStatus($status, $qrId, $shortCode);
		if (false !== $result) {
			$this->ajaxReturn(array("status" => true, "message" => "更新二维码状态成功"));
		} else {
			$this->ajaxReturn(array("status" => false, "message" => "更新二维码状态失败"));
		}
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
	 				));

	 				if($result['response'] == 0) {
	 					$this->ajaxReturn(array(
	 						"response" => 0,
	 						"message" => $result['message']
	 					));
	 				}

	 				//当用windows的时候使用下面
	 				//passthru("D:\\ffmpeg\\bin\\ffmpeg -i " . $result['info']['videopath'] . " -y -f image2 -ss 1 -vframes 1 " . $result['info']['savepath'] . $result['info']['savename'] . ".jpg");
	 				passthru("/usr/bin/ffmpeg -i " . $result['info']['videopath'] . " -y -f image2 -ss 1 -vframes 1 " . $result['info']['savepath'] . $result['info']['savename'] . ".jpg");
	 				
	 				//重新上传、需要把原本的删除、节约空间
	 				if (session("show_video_card_upload_path")) {
	 					@unlink(session("show_video_card_upload_path"));
	 					@unlink(session("show_video_card_image_path"));
	 				}

	 				session("show_video_card_upload_path", $result['info']['videopath']);
	 				session("show_video_card_upload_size", $result['info']['size']);
	 				session("show_video_card_image_path", $result['info']['savepath'] . $result['info']['savename'] . ".jpg");
	 				$this->ajaxReturn(array(
	 					"response" => 1,
	 					"videopath" => C("RESOURCE_DOMAIN") . $result['info']['videopath'],
	 					"videosize" => filesize_format($result['info']['size']),
	 					"videoname" => $result['info']['name'],
	 					"videoimage" => C("RESOURCE_DOMAIN") . $result['info']['savepath'] . $result['info']['savename'] . ".jpg"
	 				));
	 				break;
	 			case "modify_background_image":
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
	 * 短地址批量生成函数
	 * 请求方式： post
	 * 请求参数：num - 批量生成数量
	 * 返回值：status 代表成功或失败 1成 0失败
	 * data 生成之后短地址总数量
	 * 
	 * @author 李展威 <zhanweelee@gmail.com>
	 */
	public function produceShortUrl() {
		$shortNumber = intval(I("post.number"));			//新生成的短地址
		$length = intval(I("post.length"));				//短地址长度
		$result = D("ShortUrl", "Service")->createShortUrl($shortNumber, $length);	
		D("ShortUrl", "Service")->saveShortUrl($result);
		$this->ajaxReturn ( Array (
				"validLength" => count($result),
				"status" => true
		));
	}
	
	/**
	 * 短地址批量生成函数
	 * 请求方式： post
	 * 请求参数：qr_id - 二维码id
	 * qr_uid - 所有者uid
	 * qr_scan - 扫描次数
	 * qr_image - 扫描图片
	 * qr_experid - 是否过期 0未过期 1已过期
	 * 返回值：status 代表成功或失败 1成 0失败
	 * 
	 * @author 李展威 <zhanweelee@gmail.com>
	 */
	public function qrUpdate() {
		$qrData = Array (
				'qr_id' => $_POST ['qr_id'],
				'qr_uid' => $_POST ['qr_uid'],
				'qr_scan' => $_POST ['qr_scan'],
				'qr_image' => $_POST ['qr_image'],
				'qr_expired' => $_POST ['qr_expired'] 
		);
		
		$QrModel = D ( 'Qr' );
		$changed = $QrModel->singleQuery ( $qrData );
		if (! $changed) {
			$QrModel->update ( $qrData );
		}
		
		$qrContentData = Array (
				'qr_id' => $_POST ['qr_id'],
				'content' => $_POST ['qr_content'],
				'ctime' => date ( 'Y-m-d H:i:s', time () ) 
		);
		$QrContentModel = D ( 'QrContent' );
		$result = $QrContentModel->update ( $qrContentData );
		$this->ajaxReturn ( Array (
				'status' => ! ! $result 
		) );
	}
	
	/**
	 * 二维码删除函数
	 * 请求方式： post
	 * 请求参数：qr_id - 二维码id
	 * 返回值：status 代表成功或失败 1成 0失败
	 * 
	 * @author 李展威 <zhanweelee@gmail.com>
	 */
	public function qrDelete() {
		$qrData = Array (
				'qr_id' => $_POST ['qr_id'] 
		);
		
		$QrModel = D ( 'Qr' );
		$result = $QrModel->remove ( $qrData );
		
		$this->ajaxReturn ( Array (
				'status' => ! ! $result 
		) );
	}
	
	
	/**
	  * 用户反馈写入
	  */
	 
	 public function putUserFeedback() {
		$userID = I("post.userID", 0);			//用户Id
		$content = I("post.content");				//反馈内容
		$contact = I("post.contact");				//联系方式
		$userIP = I("post.ip");				//用户IP
		
		if ($content == "" || $userIP == "") {
			$this->ajaxReturn(array("flag" => false, "message" => "建议内容不可以为空"));
		}
		
		// 写入反馈
		if (D("UserFeedback")->putFeedback($userID, $content, $contact, $userIP)) {
			$this->ajaxReturn(array("flag" => true, "message" => "反馈提交成功"));
		} else {
			$this->ajaxReturn(array("flag" => false, "message" => "反馈提交失败"));
		}
	 }
	 
	 
	 public function checkVersionUpdate() {
	 	$this->ajaxReturn($this->where("id = 1")->field("app_url, version")->find());
	 }
}