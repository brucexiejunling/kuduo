<?php

namespace Home\Controller;
use Think\Controller;
class RegisterController extends Controller {
	/**
	 * 模板显示、注册页面
	 */
	public function index() {
		// 判断是否是移动端
		if (is_mobile_device()) {
			$this->display ( "Register:mobile_index" );
		} else {
			$this->display ();
		}
	}
	
	/**
	 * 通过注册成功后跳转的验证邮箱页面
	 */
	public function sendmail() {
		$this->display("Register:email_register_success");
	}
	
	/**
	 * 用户注册接口
	 */
	public function getRegister() {
		$account = I("post.account");
		$password = I("post.password");
		$code = I("post.validCode");
		$type = I("post.type");
		$phoneCode = I("post.mobileCode");
		$result;					//返回值
		
		if  (is_mobile_phone($account)) {
				$validCodeResult = D("ValidCode", "Service")->checkPhoneCodeValid($account, $phoneCode, "register");
				if ($validCodeResult['flag'] == 2) {
					$this->ajaxReturn(array(
							"flag" => 6,
							"message" => "验证码不存在请重新发送"
					));
				} else if ($validCodeResult['flag'] == 3) {
					$this->ajaxReturn(array(
							"flag" => 6,
							"message" => "验证码已过期"
					));
				} else if ($validCodeResult['flag'] == 1) {
					$result = D("User", "Service")->register($account, $password, array(
						"register_type" => "phone"
					));
				} else {
					$this->ajaxReturn(array(
							"flag" => 4,
							"message" => "未知原因错误"
					));
				}
		} else if (is_mail($account)) {
			if ($type == "pc" || $type == "web_app_register") {
				//检验验证码
				if (!check_verify($code)) {
					$this->ajaxReturn(array(
							"flag" => 5,
							"message" => "验证码错误",
					));
				}
				
				$result = D("User", "Service")->register($account, $password, array(
						"register_type" => "mail"
				));
			} else if($type == "mobile"){
				$result = D("User", "Service")->register($account, $password, array(
						"register_type" => "mail"
				));
			}
		} else {
			$this->ajaxReturn(array(
				"flag" => 2,
				"message" => "邮箱或手机格式错误"
			));
		}
		
		switch ($result['flag']) {
			case 1:
				$result['flag'] = 1;
				$result['message'] = "注册成功";
					
				if (is_mail($account)) {
					if (!$this->sendRegisterMail($account)) {
						$result['message'] = "注册成功，验证邮件发送失败。";
					}
				} else {
					//用手机注册的用户自动登录
					D("User", "Service")->login($account, $password);
				}
					
				break;
			case 2:
				$result['flag'] = 2;
				$result['message'] = "您的账号必须为邮箱/手机";
				break;
			case 3:
				$result['flag'] = 3;
				$result['message'] = "邮箱已经注册";
				break;
			case 4:
				$result['flag'] = 4;
				$result['message'] = "注册失败";
				break;
		}
		
		$this->ajaxReturn($result);
	}
	
	/**
	 * 快速注册
	 */
	public function quickRegister() {
		$account = I("post.account");

		//随机生成一个密码
		$String = new \Org\Util\String();
		$password = $String::randString();

		if (intval(S("register_" .  get_client_ip(1) . "_count")) > 2) {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "同一个IP一天内不能多次注册"
			));
		}

		if (is_mobile_phone($account)) {
			$result = D("User", "Service")->register($account, $password, array(
					"register_type" => "phone"
			));
		} else if (is_mail($account)) {
			$result = D("User", "Service")->register($account, $password, array(
					"register_type" => "mail"
			));
		} else {
			$result['flag'] = 2;
		}
		
		switch ($result['flag']) {
			case 1:
				$result['flag'] = 1;
				$result['message'] = "注册成功";
				
				if (is_mail($account)) {
					if (!$this->sendRegisterMailWithPwd($account, $password)) {
						$result['message'] = "注册成功，验证邮件发送失败";
					}
				} else if (is_mobile_phone($account)) {
					$message = "快捷注册密码：" . $password . " （请勿泄露给他人）【酷多二维码】";
					$smsResult = sendsmscode($account, $message);

					if (!$smsResult['flag']) {
						$this->ajaxReturn(array(
							"flag" => 0,
							"message" => "注册成功，密码发送失败，请确认手机号码有效性！"
						));
					}
				}

				$registerCount = intval(S("register_" .  get_client_ip(1) . "_count"));
				$registerCount++;
				S("register_" .  get_client_ip(1) . "_count", $registerCount, 3600);
					
				break;
			case 2:
				$result['flag'] = 2;
				$result['message'] = "您的账号必须为邮箱/手机";
				break;
			case 3:
				$result['flag'] = 3;
				$result['message'] = "邮箱/手机号码已经被注册";
				break;
			case 4:
				$result['flag'] = 4;
				$result['message'] = "注册失败";
				break;
		}
		
		$this->ajaxReturn($result);
	}
	
	/**
	 * 给用户发送注册邮件
	 * @param $account 用户邮箱
	 */
	private function sendRegisterMail($account) {
		// 发送邮件、并记录
		$code = md5 ( $account . rand(10000, 20000));
		$this->assign ( "code", $code );
		$mailContent = $this->fetch ( "Register:register_valid_mail" );
			
		$info = send_mail (  "酷多二维码", 
			"酷多网注册验证邮件", $account, $account, $mailContent );
		
		if ($info['flag']) {
			D("ValidCode", "Service")->saveValidCode($account, $code, "register");
			return true;
		} else {
			return false;
		}
	}


	/**
	 * 给一键注册用户发送注册邮件
	 * @param $account 用户邮箱
	 * @param $password 用户密码
	 */
	private function sendRegisterMailWithPwd($account, $password) {
		// 发送邮件、并记录
		$code = md5 ( $account . rand(10000, 20000));
		$this->assign ( "code", $code );
		$this->assign("password", $password);
		$mailContent = $this->fetch ( "Register:register_valid_mail_with_pwd" );
			
		$info = send_mail (  "酷多二维码", 
			"酷多网一键注册验证邮件", $account, $account, $mailContent );
		
		if ($info['flag']) {
			D("ValidCode", "Service")->saveValidCode($account, $code, "register");
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 发送手机验证码
	 */
	public function sendRegisterPhoneCode() {
		$phone = I("post.phone");			//电话号码
		
		//防止刷爆短信
		$sendCount = intval(S("send_register_code_" .  session_id() . "_count"));
		if ($sendCount > 15) {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "您发送验证码的频率过高"
			));
		}

		if (is_mobile_phone($phone)) {
			if (!D("User", "Service")->checkPhoneRegistered($phone)) {
				$code = rand ( 100000, 999999);			//验证码

				$message = "验证码：" . $code . "(仅用于注册，30分钟内有效，请勿泄露给他人)【酷多二维码】";
				$smsResult = sendsmscode($phone, $message);
				if ($smsResult['flag']) { // sendsmscode($phone, $message)
				 
					//保存验证码
					D("ValidCode", "Service")->saveValidCode($phone, $code, "register");
						
					//记录同一IP短信发送次数
					$sendCount++;
					S("send_register_code_" .  session_id() . "_count", $sendCount, 600);

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
					'flag' => 0,
					"message" => "手机号码已被注册"
				));
			}
		} else {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "手机号码格式错误"
			));
		}
	}
	
	/**
	 * 用户注册链接过期、重新发送邮件
	 */
	public function resendRegisterMail() {
		$account = session("resend_email");

		//从session或post中获取值
		if ($account == null) {
			$account = I("post.email");
		}
		
		
		//防止恶意邮件发送
		$sendCount = intval(S("resend_register_mail_" .  session_id() . "_count"));
		
		if ($sendCount > 10) {
			$this->ajaxReturn(array(
				"flag" => 0,
				"message" => "您邮件发送过于频繁，请稍后再试。"
			));
		}

		if ($account == null || $account == "") {
			$this->ajaxReturn(array(
				"flag" => 2,
				"message" => "邮箱错误"
			));
		}
		
		if ($this->sendRegisterMail($account)) {
			$sendCount++;
			S("resend_register_mail_" .  session_id() . "_count", $sendCount, 600);
			$this->ajaxReturn(array(
				"flag" => 1,
				"message" => "发送成功"
			));
		} else {
			$this->ajaxReturn(array(
					"flag" => 3,
					"message" => "邮件发送失败"
			));
		}
	}
}
?>
