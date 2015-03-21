<?php

namespace Home\Service;
use Think\Model;

class ValidCodeService extends BaseService{
	
	/**
	 * 用户验证码的验证
	 * @param $phone  用户手机号码
	 * @param $code   用户验证的字符串
	 * @param $type    验证码的类型
	 * @param $interval   秒数（X秒内验证码有效）
	 */
	public function checkPhoneCodeValid($phone, $code, $type, $interval = 54000) {
		$validModel = D("ValidCode");
		$data = $validModel->where("refer='$phone' and code='$code' and type='$type'")->find();
		if (!!$data) {
			$curTime = time();
			$ctime = $data['ctime'];
			if ($interval < strtotime($curTime) - strtotime($ctime)) {
				return array(
					"flag" => 3,
					"message" => "验证码已过期请重新发送",
				);
			} else {
				$validModel->where("refer='%s' and code='%s' and type='%s'", array($phone, $code, $type))->delete();
				return array(
					"flag" => 1,
					"message" => "验证成功"
				);
			}
		} else {
			return array(
				"flag" => 2,
				"message" => "验证码不存在，请重新发送"
			);
		}
	}
	
	/**
	 * 邮箱链接用户激活验证
	 * @param $code  用户激活的链接地址
	 * @param $interval 多少分钟之后链接失效
	 */
	public function checkEmailCodeValid($code, $type, $interval = 172800) {
		$validModel = D("ValidCode");
		$data = $validModel->where("code = '%s' and type = '%s'", array($code, $type))->find();
		
		//注册类型
		if ($type == "register") {
			if (!!$data) {
				$ctime = $data['ctime'];			//链接创建时间
				$curTime = date("Y-m-d H:i:s", time());					//当前时间
				if ($interval < strtotime($curTime) - strtotime($ctime)) {
					return array(
						"flag" => 3,
						"message" => "链接已过期",
						"email" => $data['refer']
					);
				} else {
					$id = $data['id'];
					$email = $data['refer'];
					$validModel->where("id = '$id'")->delete();
					D("User")->where("email = '$email'")->data(array("active" => 1))->save();			//激活用户
					return array(
						"flag" => 1,
						"message" => "链接确认成功"
					);
					
				}
			} else {
				return array(
					"flag" => 2,
					'message' => '无效的验证链接'
				);
			}
		} else if ($type == "getpassword") {
			if (!!$data) {
				$ctime = $data['ctime'];			//链接创建时间
				$curTime = date("Y-m-d H:i:s", time());					//当前时间
				if ($interval < strtotime($curTime) - strtotime($ctime)) {
					return array(
						"flag" => 3,
						"message" => "链接已过期",
						"email" => $data['refer']
					);
				} else {
					$id = $data['id'];
					$validModel->where("id = '$id'")->delete();	
					return array(
						"flag" => 1,
						"message" => "验证码确认成功"
					);
					
				}
			} else {
				return array(
					"flag" => 2,
					'message' => '验证码无效'
				);
			}
		}
	}
	
	/**
	 * 添加/更新验证码
	 * 	@param $phone  用户手机号码
	 * @param $code   用户验证的字符串
	 * @param $type    验证码的类型
	 */
	public function saveValidCode($refer, $code, $type) {
		$validCodeModel = D("ValidCode");
		$data = $validCodeModel->where("refer = '$refer' and type = '$type'")->find();
		if ($data) {
			$validCodeModel->where("refer = '$refer' and type = '$type'")->save(array("code" => $code, 'ctime' => date("Y-m-d H:i:s")));
		} else {
			$validCodeModel->add(array("refer" => $refer, "code" => $code, "type" => $type, "ctime" => date("Y-m-d H:i:s"), "status" => 0));
		}
	}
}