<?php

namespace Home\Controller;
use Think\Controller;
class OauthController extends Controller {
	
	/**
	 * sns第三方登录返回地址
	 * 
	 * @author 李珠刚
	 */
	public function index() {
		$type = I ( "get.type" ); // sns类型
		                               
		// 新浪微博
		if ($type == "sina") {
			Vendor( "SaeTOAuthV2");
			$sinaSNS = new \SaeTOAuthV2 ( C ( "SNS_OPEN_SINA.APP_KEY" ), C ( "SNS_OPEN_SINA.APP_SECRET" ) );
			$keys ["code"] = I ( "get.code" );
			$keys ["redirect_uri"] = C ( "SNS_OPEN_SINA.CALLBACK_URL" );
			try {
				$token = $sinaSNS->getAccessToken ( 'code', $keys );
			} catch ( OAuthException $e ) {
			}
			
			// 获取token
			if ($token) {
				$this->createUser ( $token, $type );
			} else {
				echo "授权失败";
			}
		}
	}
	
	/**
	 * 根据accessToken来新建或刷新用户
	 * 
	 * @param array $token
	 *        	第三方信息
	 * @param string $type
	 *        	第三方类型
	 */
	public function createUser($token, $type) {
		Vendor("SaeTOAuthV2"); // 再次引入、防止缺失
		$openid = $token ['uid'];
		$registerInfo = D("User", "Service")->checkUserSNSRegister( $openid, $type ); // 检验该第三方用户是否已经注册过

		$redirectURL = session ( "sns_redirect" ) == null ? C("DOMAIN_NAME") : session ( "sns_redirect" ); // 将要跳转的页面
		if ($registerInfo) {
			$userDetailInfo = D("User", "Service")->getUserData($registerInfo['uid']);
			D("User", "Service")->loginSNS($registerInfo['uid'], $userDetailInfo);
			header( "location: " . urldecode ( $redirectURL ) );
		} else {
			if ($type == "sina") {
				$sns = new \SaeTClientV2 ( C ( "SNS_OPEN_SINA.APP_KEY" ), C ( "SNS_OPEN_SINA.APP_SECRET" ), $token ['access_token'] );
				$userInfo = $sns->show_user_by_id ( $token ['uid'] );
				$nickName = $userInfo ['screen_name']; // 用户昵称
				$avatarSavepath = "data/uploads/images/" . date("Ymd", time()) . "/";
				$avatarSavename = time() . ".jpg";
				download_file($userInfo ['profile_image_url'], $avatarSavepath, $avatarSavename);
				$location = $userInfo ['location']; // 用户所在地
				
				$userData = D ( "User", "Service" )->createNewUser( "", "", array(
					"register_type" => "sns",
					"avatar" => $avatarSavepath . $avatarSavename,
					"location" => $location,
				));
				if ($userData['flag'] == 1) {
					// 当创建成功、保存sns信息
					if (D ("User", "Service" )->saveSNSInfo( $openid, $token ['access_token'], $type, $token ['remind_in'], $userData['uid'], json_encode($userInfo))) {
						//$sns->update ( "测试、.....苍老师" ); // 第一次绑定发一条微博
						D("User", "Service")->loginSNS($userData['uid'], $userData);
						header ( "location: " . urldecode ( $redirectURL ) );
					} else {
						echo "创建失败";
					}
				} else {
					echo "授权失败2";
				}
			}
		}
	}
	
	/**
	 * 发送新浪微博
	 * 
	 * @author 李珠刚
	 */
	public function sendSinaWeibo() {
		$refer = I ( "post.refer" ); // 来源地址
		$shortURL = I ( "post.qr_url_short" ); // 短地址
		$qrType = I ( "post.qr_type" ); // 短地址
		$uid = session ( "uid" );
		
		// 用户登录才可以分享
		if (! $uid) {
			$this->ajaxReturn ( array (
					"response" => false,
					"message" => "请使用微博登录后再分享" 
			) );
		}
		
		// 检查用户是否绑定微博
		$registerInfo = D ( "UserSns" )->checkUserSNSRegisterByUid ( $uid, "sina" );
		
		if (! $registerInfo) {
			$this->ajaxReturn ( array (
					"response" => false,
					"message" => "请使用微博登陆后再分享" 
			) );
		}
		
		// 判断expire是否过期、过期则提示用户重新授权
		if (date ( "Y-m-d H:i:s", time () ) > $registerInfo ['expire']) {
			$this->ajaxReturn ( array (
					"response" => false,
					"message" => "您的微博授权已经过期，请重新使用微博登陆" 
			) );
		}
		
		// 根据短地址返回对应二维码信息
		$qrInfo = D ( "Qr" )->getQRInfoByShortURL ( $shortURL );
		
		vendor ( "SaeTOAuthV2" );
		$sns = new SaeTClientV2 ( C ( "SNS_OPEN_SINA.APP_KEY" ), C ( "SNS_OPEN_SINA.APP_SECRET" ), $registerInfo ['token'] );
		
		// 根据短地址获取对应二维码内容
		// $qrContent = D("QrContent")->getQRContentByShortURL($shortURL); //还没有高级接口、且视频还是相对路径、暂时不做这一步
		
		switch ($qrInfo ['qr_type']) {
			case "video" :
				$sns->upload ( "#有声二维码#这段视频很有意思→_→ 不用感谢我、请叫我雷锋好么！附链接" . $refer . "（分享自 @有声二维码）", $qrInfo ['qr_image'] ); // 发送微博
				break;
			case "text" :
				$sns->upload ( "#有声二维码#这段文字很有意思→_→ 不用感谢我、请叫我雷锋好么！附链接" . $refer . "（分享自 @有声二维码）", $qrInfo ['qr_image'] );
				break;
			case "audio" :
				$sns->upload ( "#有声二维码#这段文字很有意思→_→ 不用感谢我、请叫我雷锋好么！附链接" . $refer . "（分享自 @有声二维码）", $qrInfo ['qr_image'] );
				break;
			default :
				$sns->upload ( "#有声二维码#小伙伴们来跟我一起看这个东西吧→_→" . $refer . "（分享自 @有声二维码）", $qrInfo ['qr_image'] );
		}
		
		$this->ajaxReturn ( array (
				"response" => true,
				"message" => "分享成功" 
		));
	}
}
?>
