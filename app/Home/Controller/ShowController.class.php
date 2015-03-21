<?php

namespace Home\Controller;
use Think\Controller;
class ShowController extends Controller {
	
	//展示二维码内容
	public function index() {
		$shortCode = I('get.shortcode');
		if (!is_short_code($shortCode)) {
			$this->display("Show:short_url_error");
			return;
		}

		$shortCodeData = D("ShortUrl", "Service")->getShortUrlData($shortCode);			//用户每次通过这方文的时候都获取一下这个值
		//2代表被锁定, 0代表未使用
		$qrcodeData = "";
		switch ($shortCodeData['status']) {
			case 2:
				$tmpData = S("create_" . $shortCode);
				$qrcodeData['content'] = $tmpData['create_qrcode_content'];
				$qrcodeData['type'] = $tmpData['create_qrcode_type'];
				$this->display("Show:create_undone_notify");				//显示提示还未完成、不影响后续的显示
				break;
			case 1:
				//能够显示的页面他的短地址都是已使用，值为1
				$qrcodeData = D("QRCode", "Service")->getQRCodeData($shortCode);
				if ($qrcodeData) {
					D("QRCode", "Service")->updateVisitRecord($qrcodeData['id']);			//扫描数据记录
				}
				break;
			case 0:
				header("location:" . C("DOMAIN_NAME"));
				return;
			default:
				$this->display("Show:short_url_error");
				return;
		}

		switch ($qrcodeData['type']) {
			case "lost_card": 
				$this->pcNotify();
				session("show_qrcode_short_code", $qrcodeData['short_code']);
				$content = json_decode ( $qrcodeData ['content'], true );
				//如果内容为空、则代表为空卡贴
				if ($content == "") {
					$this->display("Show:lost_card_upload");
				}  else {
					session("katie_owner_uid", $qrcodeData['uid']);
					$this->assign ( "value", $content);
					$this->display ( "Show:lost_card" );
				}
				break;
			case 'text' : // 纯文字
				$this->pcNotify();
				$content = $qrcodeData['content'];
				$this->assign ("text", htmlspecialchars_decode($content)); // 文字内容
				$this->display ("Show:text");
				break;
			//普通视频、含点赞评论、浏览量
			case 'video' :
				$content = json_decode ( $qrcodeData ['content'], true );
				
				//用户是否点赞、登录的用户优先级最高、最先开始
				if ($uid = session("uid")){
					$likeStatus = D("Like", "Service")->getLikeStatus($uid, $qrcodeData['short_code']);
					$this->assign("likePressed", $likeStatus);
				} else if (cookie("like_" . $qrcodeData['short_code'])) {
					$this->assign("likePressed", 1);
				}
				
				
				$this->assign("shortcode", $qrcodeData['short_code']);

				$content['userLike'] = $qrcodeData['like_count'] ? $qrcodeData['like_count'] : 0;
				$content['visitCount'] = $qrcodeData['visit_count'] ? $qrcodeData['visit_count'] : 0;
				$content['ctime'] = substr($qrcodeData['generate_time'], 0, 11);
				$this->assign("value", $content);
				$this->display ( "Show:video" );
				break;
			//留声卡
			case "video_card":
				$this->pcNotify();
				session("show_qrcode_short_code", $qrcodeData['short_code']);
				if ($qrcodeData['content'] == "") {
					//预览
					if (I('get.type') == "preview") {
						//满足预览条件
						if (session("show_video_card_upload_path")) {
							$this->display ( "Show:video_card_preview" );
						} else {
							$this->display("Show:video_card_upload");
						}
					} else {
						$this->display("Show:video_card_upload");
					}
				} else {
					$this->assign("content", json_decode($qrcodeData['content'], true));
					$this->display ( "Show:video_card" );
				}
				break;
			case 'file':
				$this->file($qrcodeData);
				break;
			case "wifi":
				$this->pcNotify();
				$this->wifi($qrcodeData);
				break;
			case "mp": 
				$content = json_decode ( $qrcodeData ['content'], true );
				$this->assign("cardValue", $content);
				$this->display("show:mp");
				break;
		}
	}

	/**
	*   检测是否为移动端/如果为移动端则直接显示/否则提示用移动端扫描登录
	**/
	private function pcNotify() {
		if (!is_mobile_device()) {
			$this->assign("qrcodeData", $qrcodeData);
			$this->assign("shortcode", $qrcodeData['short_code']);
			$this->display("Show:pc_notify");					//提示用户前往移动端
			exit;
		}
	}
	
	/**
	 * ====================文件类型页面==============================
	 */
	private function file($qrcodeData) {
		session("show_qrcode_short_code", $qrcodeData['short_code']);
		$content = json_decode ( $qrcodeData ['content'], true );
		if ($content == "") {
			$this->display("Show:upload_file");
		} else {
			$this->assign("value", $content);
			$this->assign("visitCount", $qrcodeData['visit_count'] ? $qrcodeData['visit_count'] : 0);
			$this->display("Show:file");
		}
	}
	
	/**
	 * ========================WIFI页面======================
	 */
	private function wifi($qrcodeData) {
		session("short_qrcode_short_code", $qrcodeData['short_code']);
		$content = json_decode($qrcodeData['content'], true);
		if ($content == "") {
			$this->display("Show:upload_wifi");
		} else {
			$this->assign("value", $content);
			
			//用户是否点赞、登录的用户优先级最高、最先开始
			if ($uid = session("uid")){
				$likeStatus = D("Like", "Service")->getLikeStatus($uid, $qrcodeData['short_code']);
				$this->assign("likePressed", $likeStatus);
			} else if (cookie("like_" . $qrcodeData['short_code'])) {
				$this->assign("likePressed", true);
			}
			
			$this->assign("shortCode", $qrcodeData['short_code']);				//短地址赋值到页面上
			$this->assign("userLike", $qrcodeData['like_count'] ? $qrcodeData['like_count'] : 0);				//拥护点赞的数量
			$this->display("Show:wifi");
		}
	}
	
	/**
	 * =============================给失物贴发送留言===========================
	 */
	public function sendKatieComment() {
		$comment = I ( "post.comment" );
		$shortCode = session("show_qrcode_short_code");
		$ownerId = session("katie_owner_uid");
		
		if (!$shortCode || !$ownerId) {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "留言失败"
			));
		}

		//防止留言骚扰
		$commentFlag = intval(S("send_katie_comment_time_" .  session_id() . "_flag"));
		if ($commentFlag) {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "请一分钟后再进行留言。"
			));
		}

		$sendCount = intval(S("send_katie_comment_" .  session_id() . "_count"));
		if ($sendCount > 15) {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "您留言过于频繁，请隔段时间。"
			));
		}
	
		//获取用户邮箱和手机号码
		$userData = D("User", "Service")->getUserData($ownerId);
		$phone = $userData['phone'];
		$email = $userData['email'];
		
		if (is_mail($email)) {
			$this->assign("nickname", $userData['nickname']);
			$this->assign("comment", $comment);
			$content = $this->fetch("Show:lost_card_email_tip");
			$info = send_mail (  "酷多失物贴拾到者留言提醒",
					"酷多失物贴留言邮件提醒", $email, $email, $content );
		} else if (is_mobile_phone($phone)) {
			
			// 发送短信给物主
			sendsmscode($phone,  "有拾到者给您的酷多失物贴留言了，请登录网站http://www.ikuduo.com/用户中心查看。【酷多二维码】" );
		}

		//保证一分钟后才能提交
		S("send_katie_comment_time_" .  session_id() . "_flag", 1, 60);
	
		//发送次数统计
		$sendCount++;
		S("send_katie_comment_" .  session_id() . "_count", $sendCount, 3600);		


		$cuid = session("uid") ? session("uid") : 0;
		if (D("Comment", "Service")->addComment($shortCode, $cuid, $comment)) {
			$flag = D("Notify", "Service")->addNotify($ownerId, "您有酷多失物贴有新的留言");
			if ($flag) {
				$this->ajaxReturn(array("flag" => 1, "message" => "发送已成功发送给失主，60秒后可继续发送留言"));
			} else {
				$this->ajaxReturn(array("flag" => 0, "message" => "留言失败，请联系管理员"));
			}
		} else {
			$this->ajaxReturn(array("flag" => 0, "message" => "留言失败，请联系管理员"));
		}
	}
	
	/**
	 * =========================卡贴类型的二维码提交========================
	 */
	public function katieSubmit() {
		$nickname = I ( "post.nickname" ); // 物主昵称
		$introduce = I ( "post.introduce" ); // 物品介绍
		$thingname = I ( "post.thingname" ); // 物品名称
		$phone = I("post.phone") ? I("post.phone") : "";			//手机
		$shortCode = session("show_qrcode_short_code");
	
		$regxNickname = "/^.{1,40}$/";
		$regxthingname = "/^.{1,40}$/";
	
		// 检验昵称的规范性
		if (! preg_match ( $regxNickname, $nickname )) {
			$this->ajaxReturn(array("flag" => 0, "message" => "物主名称长度过长或未填写"));		
		}

		// 检验昵称的规范性
		if (! preg_match ( $regxthingname, $thingname )) {
			$this->ajaxReturn(array("flag" => 0, "message" => "物品名称过长或未填写"));		
		}

		$uid = session ( "uid" ) ? session ( "uid" ) : 0;
		
		$content = json_encode(array(
			"nickname" => $nickname,
			"introduce" => $introduce,
			"thingname" => $thingname,
			"phone" => $phone
		));
		
		D("QRCode", "Service")->updateQRCodeData($shortCode, array(
			"uid" => $uid,
			"content" => $content
		));
		
		$this->ajaxReturn(array("flag" => 1, "message" => "提交成功"));		
	}

	/**
	 * ======================对于视频卡、语音卡等保存函数======================
	 */
	public function video_card_finish() {
		$shortCode = session("show_qrcode_short_code");
		
		if ($shortCode) {
			$videoDescription = I("post.description");
			$videoPath = session("show_video_card_upload_path");
			$videoImage = session("show_video_card_image_path");
			$videoSize = session("show_video_card_upload_size");
			
			//防止没有文件就提交
			if ($videoImage == "" || $videoImage == null || $videoPath == "" || $videoPath == null) {
				$this->ajaxReturn(array("flag" => 0, "message" => "请先上传文件后再进行提交"));
			}

			$data['content'] = json_encode(array(
				"videopath" => $videoPath,
				"videoimage" => $videoImage,
				"videosize" => $videoSize,
				"description" => $videoDescription,
				"mediaType" => session("show_video_card_upload_type")
			));
			
			$data['uid'] = session("uid") ? session("uid") : 0;
			
			D("QRCode", "Service")->updateQRCodeData($shortCode, $data);

			//成功后清楚数据
			session("show_video_card_upload_size", null);
			session("show_video_card_image_path", null);
			session("show_video_card_upload_path", null);
			session("show_video_card_description", null);

			$this->ajaxReturn(array(
				"flag" => 1,
				"message" => "成功！"
			));


		} else {
			$this->ajaxReturn(array("flag" => 0, "message" => "您的短地址不能为空"));	
		}
	}

	/**
	*   酷多留声卡检查是否符合预览状态
	*/
	public function videoCardPreview() {
		$description = I("post.description");		//描述
		session("show_video_card_description", $description);
		if (session("show_video_card_upload_path")) {
			$this->ajaxReturn(array(
				"flag" => 1,
				"redirect" => C("DOMAIN_NAME") . "s/" . session("show_qrcode_short_code") . "?type=preview"
			));
		} else {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "您还未上传音频或视频"
			));
		}
	}
}

?>