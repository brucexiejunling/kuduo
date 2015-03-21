<?php

namespace Home\Controller;
use Think\Controller;
use Think\Session\Driver\Memcache;
class UserController extends Controller {
	/**
	 * 用户首页
	 */
	public function index() {
		// 用户登录才能继续操作
		if (session ( "uid" )) {
			$qrCodeList = D("QRCode", "Service")->getQRCodeList(session("uid"));
			$this->assign ( "qrList", $qrCodeList ); // 用户二维码列表
			$this->display ();
		} else {
			$this->display ( "User:ask_user_login" );
		}
	}
	
	/**
	 * 用户设置页面
	 */
	public function settings() {
		$basicInfo = D("User", "Service")->getBasicInfo(session("uid"));
		$this->assign("basicInfo", $basicInfo);
		$this->display();
	}

	/**
	 * =============================用户消息状态读取、判断有多少新消息======================
	 */
	public function getMessageStatus() {
		if (session("uid")) {
			$messageNumber = D("Notify", "Service")->getMessageStatus();
			$this->ajaxReturn(array(
				"flag" => 1,
				"number" => $messageNumber
			));
		} else {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "您尚未登录"
			));
		}
	}

	/**
	* ============================= 重置密码==========================
	*/
	public function resetpassword() {
		if (session("forget_password_reset") == 10000) {
			$this->display();
		} else {
			echo "页面已过期";
		}
	}

	/**
	* ============重置密码接口===================
	*/
	public function resetpwd() {
		if (session("forget_password_reset") == 10000) {
			$password = I("post.password");
			if (is_password_valid($password)) {
				if (session("forget_password_account")) {
					D("User", "Service")->resetPassword(session("forget_password_account"), $password);
					session("forget_password_reset", null);
					session("forget_password_account", null);
					$this->ajaxReturn(array(
						"flag" => 1,
						"message" => "修改成功"
					));
				} else {
					$this->ajaxReturn(array(
						"flag" => 0,
						"message" => "账号不存在"
					));
				}
			} else {
				$this->ajaxReturn(array(
					"flag" => 0,
					"message" => "密码必须为6-16位英文/数字组成"
				));
			}
			
		}
	}
	
	/**
	 * =============================用户注册激活=======================
	 */
	public function verify() {
		//验证用户
		if ($verifyCode = I ('get.code')) {
			$result = D("ValidCode", "Service")->checkEmailCodeValid($verifyCode, "register", 172800);
			
			//链接已过期
			if ($result['flag'] == 3) {
				session("resend_email", $result['email']);				//临时保存session值
			}
			
			$this->assign("result", $result);
			$this->display ();
		} else {
			header("location :" . C("DOMAIN_NAME"));
		}
	}
	
	/**
	* ==========================忘记密码时，验证码确认，确认成功即可允许用户重置密码============
	*/
	public function verify_reset_pwd_code() {
		$code = I("post.resetcode");
		if (!preg_match("/^\d{4,10}$/", $code)) {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "验证码格式错误"
			));
		}

		if (session("forget_password_type") == "mail") {
			$result = D("ValidCode", "Service")->checkEmailCodeValid($code, "getpassword", 3600);
			if ($result['flag'] == 1) {
				session("forget_password_reset", 10000);
				$this->ajaxReturn(array(
					"flag" => 1,
					"message" => "验证成功"
				));
			} else {
				$this->ajaxReturn(array(
					"flag" => 2,
					"message" => $result['message']
				));
			}

		} else if (session("forget_password_type") == "phone") {
			$result = D("ValidCode", "Service")->checkPhoneCodeValid(session("forget_password_account"), $code, "getpassword", 3600);
			if ($result['flag'] == 1) {
				session("forget_password_reset", 10000);
				$this->ajaxReturn(array(
					"flag" => 1,
					"message" => "验证成功"
				));
			} else {
				$this->ajaxReturn(array(
					"flag" => 2,
					"message" => $result['message']
				));
			}
		}
		
	}

	/**
	 * ===========================二维码详细信息查看==========================
	 */
	public function qrcode() {
		if (session ( "uid" )) {
			$this->display ();
		} else {
			$this->display ( "User:ask_user_login" );
		}
	}
	
	
	/**
	 * =======================手机端用户扫描记录 同步==========================
	 * 
	 */
	public function scanSync() {
		$uid = intval(I("post.userId"));					//用户ID
		$password = I("post.password");			//用户密码
		$qrcodeArray = json_decode(htmlspecialchars_decode(I("post.qrcode")));
		
		$this->ajaxReturn(array(
			"postContent" => I("post."),
			"userId" => I("post.userId"),
			"jsondecodeqrcode" => $qrcodeArray,
			"jsondecodeqrcodeOne" => $qrcodeArray[0]	
		));
		
		
		$checkResult = D("User", "Service")->checkUserIdentity($uid, $password);
		if ($checkResult['flag'] == 1) {
			
			$qrCodeResult = D("QRCode", "Service")->syncQRCode($uid, $qrcodeArray);		//同步客户端数据
			$this->ajaxReturn(array(
				"flag" => 1,
				"qrcode" => $qrCodeResult,
				"message" => "同步成功"
			));
		} else if ($checkResult['flag'] == 2) {
			$this->ajaxReturn(array(
				"flag" => 2,
				"message" => "密码错误，请重新登录"
			));
		} else if ($checkResult['flag'] == 3) {
			$this->ajaxReturn(array(
				"flag" => 3,
				"message" => "用户不存在"
			));
		}
	}
	
	
	
	/**
	 * 对二维码或者评论内容进行点赞
	 * 
	 * @param string $type
	 *        	点赞对象的类型: 'COMMENT': 评论, 'QR': 二维码
	 * @param int $uid
	 *        	点赞人的用户uid
	 * @param int $cid
	 *        	评论的id, 对应表u_user_comment中cid
	 * @param array $commentInfo
	 *        	被点赞的评论的信息
	 * @param array $praise
	 *        	点赞的数据记录
	 * @param array $praiseInfo
	 *        	用于查询是否已经点赞
	 * @param int $commentUid
	 *        	被点赞评论的人的用户uid
	 * @param string $shortUrl
	 *        	被点赞的二维码短地址
	 * @param array $qrInfo
	 *        	被点赞的二维码的信息
	 * @param int $qrUid
	 *        	被点赞评论的人的用户uid
	 * @author 李展威
	 * @return 返回数组 msg: 提示信息, status: 操作成功与否
	 */
	public function praiseQrComment() {
		// 赞的类型: 二维码或者评论
		$type = I ( 'post.type' );
		if (! session ( 'uid' )) {
			$this->ajaxReturn ( Array (
					'msg' => '请先登陆',
					'status' => false 
			) );
		}
		$uid = session ( 'uid' );
		
		if ($type == "COMMENT") {
			$cid = I( 'post.cid' );
			// 查询该条评论的信息
			$commentInfo = D ( 'UserComment' )->singleQuery ( Array (
					"id" => $cid 
			) );
			if (! $commentInfo) {
				$this->ajaxReturn ( Array (
						'msg' => "该评论不存在,无法点赞",
						'status' => false 
				) );
			}
			
			// 判断评论所属二维码是否有效
			// ******
			
			$praise = Array (
					'uid' => $uid,
					'cid' => $cid,
					'isqr' => 0 
			);
			
			// 查询是否已经点赞
			$praiseInfo = D ( 'UserPraise' )->singleQuery ( $praise );
			if ($praiseInfo) {
				$this->ajaxReturn ( Array (
						'msg' => '已经对该条评论点赞, 请勿重复操作',
						'status' => false 
				) );
			} else {
				$result = D ( 'UserPraise' )->append ( $praise );
				if ($result) {
					// 点赞成功, 则给被点赞的人发通知
					$commentUid = $commentInfo ['uid'];
					D ( 'UserNotify' )->append ( Array (
							'uid' => $commentUid,
							'notify_type' => 'PRAISE' 
					) );
					$this->ajaxReturn ( Array (
							'msg' => "点赞成功",
							'status' => true 
					) );
				}
			}
		} else if ($type == "QR") {
			$shortUrl = I ( 'post.qr_url_short' );
			// 查询该条二维码的信息
			$qrInfo = D ( 'Qr' )->singleQuery ( Array (
					"qr_url_short" => $shortUrl 
			) );
			if (! $qrInfo) {
				$this->ajaxReturn ( Array (
						'msg' => "该二维码不存在, 无法点赞",
						'status' => false 
				) );
			}
			
			// 判断评论所属二维码是否有效
			// ******
			
			$praise = Array (
					'uid' => $uid,
					'qr_url_short' => $shortUrl,
					'isqr' => 1 
			);
			
			// 查询是否已经点赞
			$praiseInfo = D ( 'UserPraise' )->singleQuery ( $praise );
			if ($praiseInfo) {
				$this->ajaxReturn ( Array (
						'msg' => '已经对该二维码点赞, 请勿重复操作',
						'status' => false 
				) );
			} else {
				$result = D ( 'UserPraise' )->append ( $praise );
				if ($result) {
					// 点赞成功, 则给被点赞的人发通知
					$qrUid = $qrInfo ['qr_uid'];
					D ( 'UserNotify' )->append ( Array (
							'uid' => $qrUid,
							'notify_type' => 'PRAISE' 
					) );
					$this->ajaxReturn ( Array (
							'msg' => "点赞成功",
							'status' => true 
					) );
				}
			}
		}
	}
	
	/**
	 * 根据用户uid
	 * 
	 * @param int $uid
	 *        	用户id
	 * @return array 二维码信息列表
	 */
	private function getUserQrList($uid) {
		return D ( "Qr" )->getQrListByUid ( $uid );
	}
	
	/**
	 * ==========================删除用户的二维码====================
	 */
	public function deleteQRCode() {
		// 用户登录状态下
		if ($uid = session ( "uid" )) {
			$shortcode = I( "post.shortcode" ); // 二维码短地址 
			// 检测二维码和用户是否对应
			$qrcodeData = D ( "QRCode", "Service")->getQRCodeData($shortcode);
			if ($uid == $qrcodeData['uid']) {
				
				if (D("QRCode", "Service")->deleteQRCode ($shortcode, "", $uid)) {
					$this->ajaxReturn ( array (
							"flag" => 1,
							"message" => "二维码删除成功" 
					) );
				} else {
					$this->ajaxReturn ( array (
							"flag" => 0,
							"message" => "删除失败，请确认后重试" 
					) );
				}
			} else {
				$this->ajaxReturn ( array (
						"flag" => 0,
						"message" => "你不存在此二维码，请确认后重试" 
				) );
			}
		} else {
			$this->ajaxReturn ( array (
					"flag" => 0,
					"message" => "请登陆后再进行操作" 
			) );
		}
	}
	
	/**
	 * =========用户退出登录、可直接请求函数、也可采用ajax方式发送post请求传递任意数据退出===============
	 */
	public function quit() {
		// 采用ajax方式退出、
		if (isset ( $_POST ['exit'] )) {
			session ( "[destroy]" );
			$this->ajaxReturn ( array (
					"flag" => 1 
			) );
		} else {
			// 采用http方式退出
			session ( "[destroy]" );
			header ( "location:" . C("DOMAIN_NAME") );
		}
	}
	
	/**
	 * =====================重新获取密码，发送验证邮件/验证短信码==============
	 */
	public function getPassword() {
		$account = I("post.account");
		
		/**
		* 检验验证码
		*/
		if (!check_verify(I("post.code"))) {
			$this->ajaxReturn(array(
				"flag" => 6,
				"message" => "验证码错误"
			));
		}
		if (is_mail($account)) {
				
			//检查邮箱用户是否存在
			if (!D("User", "Service")->checkEmailRegistered($account)) {
				$this->ajaxReturn(array(
					"flag" => 4,
					"message" => "该邮箱尚未注册酷多二维码"
				));
			}
			
			$code = rand(100000, 999999);				//随机码
			$this->assign("code", $code);
			$content = $this->fetch("User:password_email");
			$result = send_mail("酷多二维码", "酷多二维码密码找回邮件", $account, $account, $content);
			if ($result["flag"]) {

				//标记为邮箱
				session("forget_password_account", $account);
				session("forget_password_type", "mail");
				D("ValidCode", "Service")->saveValidCode($account, $code, "getpassword");
				$this->ajaxReturn(array(
						"flag" => 1,
						"message" => "发送成功"
				));
			} else {
				$this->ajaxReturn(array(
					"flag" => 3,
					"message" => $result['message']
				));
			}
		} else if (is_mobile_phone($account)) {

			 if (!D("User", "Service")->checkPhoneRegistered($account)) {
			 	$this->ajaxReturn(array(
					"flag" => 4,
					"message" => "该手机号码尚未注册酷多二维码"
				));
			 }

			 $code = rand(100000, 999999);

			 $message = "验证码：" . $code . "(您正通过手机找回密码，30分钟内有效)【酷多二维码】";
			 $smsResult = sendsmscode($account, $message);
			if ($smsResult['flag']) { // sendsmscode($phone, $message)
			 	//标记为手机号码
				session("forget_password_account", $account);
				session("forget_password_type", "phone");
				//保存验证码
				D("ValidCode", "Service")->saveValidCode($account, $code, "getpassword");
					
				$this->ajaxReturn(array(
					"flag" => 1,
					"message" => "验证码已发送至您的手机"
				));
			} else {
				$this->ajaxReturn(array(
						"flag" => 0,
						"message" => "验证码发送失败"
				));
			}
		} else {
			$this->ajaxReturn(array(
		 			"flag" => 2,
		 			"message" => "请输入正确的手机或者邮箱"
		 	));
		}
	}

	public function saveBasicInfo() {
		$nickName = I("post.nickName");
		$gender = I("post.gender");
		$birthday = I("post.birthday");
		$province = I("post.province");
		$city = I("post.city");
		$introduction = I("post.introduction");

		if (!preg_match("/^[a-zA-Z0-9\x4e00-\x9fa5_]{2,20}$/", $nickName)) {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "昵称须是字母,数字,汉字或下划线组成的2到20个字符!"
			));
		}

		// validate
		// 更新session中用户昵称
		session("nickname", $nickName);
		D("User", "Service")->saveBasicInfo(session("uid"), array(
			"nickname" => $nickName, 
			"gender" => $gender,
			"birthday" => $birthday,
			"province" => $province,
			"city" => $city,
			"introduction" => $introduction
		));

		$this->ajaxReturn(array("flag" => 1, "message" => "ok"));
	}

	public function loadCities() {
		$provinceId = I("post.province");
		if(!S("area_cache")) {
			S("area_cache", array());
		}
		if(S("area_cache")["$provinceId"]) {
			$cities = S("area_cache")["$provinceId"];
		} else {
			$cities = D("User", "Service")->loadCitiesOfProvince($provinceId);
		}
		$this->ajaxReturn(array(
			"flag" => 1,
			"message" => "ok",
			"data" => $cities
		));
	}

	public function validatePassword() {
		$password = I("post.password");
		$validateResult = D("User", "Service")->checkUserIdentity(session("uid"), $password);
		$this->ajaxReturn($validateResult);
	}

	public function savePassword() {
		$password = I("post.password");
		if(is_password_valid($password)) {
			D("User", "Service")->savePassword(session("uid"), $password);
			$this->ajaxReturn(array("flag" => 1, "message" => "ok"));
		} else {
			$this->ajaxReturn(array("flag" => 0, "message" => "invalid password"));
		}
	}

	public function uploadAvatar() {
		$result = $this->uploadImage();
		if($result["response"] == 1) {
			D("User", "Service")->saveAvatar(session("uid"), $result["info"]["imagepath"]);
			$this->ajaxReturn(array("flag" => 1, "imagePath" => $result["info"]["imagepath"]));
		} else {
			$this->ajaxReturn(array("flag" => 0));
		}
	}

	private function uploadImage($options) {
		$upload = new \Think\Upload (); // 实例化上传类
		$upload->maxSize = $options['maxSize'] ? $options['maxSize'] : 524800;   // 设置附件上传大小
		$upload->rootPath = "data/uploads/";
		$upload->savePath = "images/";
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
			$imagepath = $upload->rootPath . $info[0]['savepath'] .$info[0]['savename'];
			$imgInfo = getimagesize($imgSavePathAndName);
			
			$imgWidthHeight = array(
				"width" => $imgInfo[0],
				"height" => $imgInfo[1]
			);
			
			$info[0]['width'] = $imgInfo[0];
			$info[0]['height'] = $imgInfo[1];
			$info[0]['imagepath'] = $imagepath;
			
			return array(
				"response" => 1,
				"info" => $info[0]
			);
		}
	}
	
	public function cropImg() {
    $selection = I("post.selection");
    $imgPath = I("post.imgPath");
     
    if ($selection == "" || $imgPath == ""){
        $returnData["msg"] = "上传文件错误，请检查您的文件";
        $this->ajaxReturn($returnData);
    }
    
    $img = new \Think\Image(\Think\Image::IMAGE_GD, $imgPath);
     
    $scale = $img->width() / 500;
    $flag = $img->crop($selection["width"] * $scale, $selection["height"] * $scale, 
    									$selection["x1"] * $scale, $selection["y1"] * $scale, 
    									$selection["width"] * $scale, $selection["height"] * $scale)->save($imgPath);
     
    $this->ajaxReturn($flag);
  }

}
	
?>
