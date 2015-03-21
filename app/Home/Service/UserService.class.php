<?php


namespace Home\Service;
use Think\Model;
use Think\Cache\Driver\Memcache;

class UserService extends BaseService{
	/**
	 * 用户普通登录
	 * @param $account 用户帐号（手机、邮箱、有声ID）
	 * @param $password 明文密码
	 * @param $options 附加参数
	 */
	public function login($account, $password, $options) {
		$userData = $this->getUserData($account);			//获取用户数据

		//根据返回的数据判断账号是否存在
		if (!$userData) {
			return array(
					"flag" => 3,
					"message" => "账号不存在"
			);
		}
		
		//未激活用户不能登录帐号
		if ($userData['active'] == 0) {
			return array(
				"flag" => 4,
				"message" => "您的帐户未激活，请进入邮箱进行激活账户。"
			);
		}

		//用户密码+盐值 md5
		if (md5($password . $userData['salt']) == $userData['password']) {
			session_set_cookie_params ( 3600 * 24 * $options['stayTime'], "/" );
			session( '[regenerate]' );
			session( "uid", $userData['uid'], 3600 * 24 * $options['stayTime']); // 用户的uid
			session("nickname", $userData['nickname'], 3600 * 24 * $options['stayTime']);
			return array(
					"flag" => 1,
					"message" => "登陆成功",
					"data" => array(
						"_id" => $userData['uid'],
					    'nickname' => $userData['nickname'],
						'avatar' => $userData['avatar']
					)
			);
		} else {
			return array(
					"flag" => 2,
					"message" => "您的密码错误"
			);
		}
	}
	
	/*
	*  SNS login
	*/
	public function loginSNS($uid, $options) {
		session_set_cookie_params ( 3600 * 24 * $options['stayTime'], "/" );
		session ( '[regenerate]' );
		session ( "uid", $uid, 3600 * 24 * $options['stayTime']); // 用户的uid
		session("nickname", $options['nickname'], 3600 * 24 * $options['stayTime']);
	}

	/**
	 * 确认用户是否有效
	 * @param $password 用户密码
	 * @param $uid 用户id
	 */
	public function checkUserIdentity($uid, $password) {
		$userData = D("User")->where("uid = '$uid'")->find();
		if ($userData) {
			$salt = $userData['salt'];
			if ($userData['password'] == md5($password . $salt)) {
				return array(
					"flag" => 1,
					"message" => "身份认证通过"
				);
			} else {
				return array(
						"flag" => 2,
						"message" => "密码错误，请重新登录"
				);
			}
		} else {
			return array(
				"flag" => 3,
				"message" => "该用户不存在"
			);
		}
	}
	
	/**
	 * 普通方式注册
	 */
	public function register($account, $password, $options) {
		if ($options['register_type'] == "mail") {
			if (!$this->checkEmailRegistered($account)) {
				$result = $this->createNewUser($account, $password, $options);
				if ($result['flag'] == 1) {
					return array(
						"flag" => 1,
						"message" => "注册成功",
						"data" => array(
							"_id" => $result['uid'],
						    "avatar" => $result['avatar'],
							"nickname" => $result['nickname']
						)
					);
				} else {
					return array(
						"flag" => 4,
						"message" => "未知原因注册失败"
					);
				}
			} else {
				return array(
					"flag" => 3,
					'message' => "邮箱已经注册"
				);
			}
		} else if ($options['register_type'] == "phone") {
			if (!$this->checkPhoneRegistered($account)) {
				$result = $this->createNewUser($account, $password, $options);
				if ($result['flag'] == 1) {
					return array(
							"flag" => 1,
							"message" => "注册成功",
							"data" => array(
									"_id" => $result['uid'],
									"avatar" => $result['avatar'],
									"nickname" => $result['nickname']
							)
					);
				} else {
					return array(
						"flag" => 4,
						"message" =>  mysql_error()
					);
				}
			} else {
				return array(
						"flag" => 3,
						'message' => "手机号码已经注册"
				);
			}
		}  else {
			return array(
				"flag" => 7,
				'message' => "未指定账号类型(手机/邮箱)"
			);
		}
	}
	
	/**
	 * 创建新用户
	 *
	 * @param string $account  用户账号
	 * @param string $password   用户设置的密码
	 */
	public function createNewUser($account, $password, $options) {
		$userModel = D("User");
		$salt = $this->createSalt();			//密码加密的盐值
		$uid = 0;
		//暂时不支持usheng_id
		if ($options["register_type"] == "mail") {
			$uid = $userModel->add ( array (
					"email" => $account,
					"password" => md5 ( $password . $salt),
					"ctime" => date ( "Y-m-d H:i:s", time () ),
					"avatar" => "data/uploads/default/head.png",
					"nickname" => "kd" . rand(100000, 200000),	
					"active" => 0,
					"salt" => $salt
			) );
		} else if ($options['register_type'] == "phone") {
			$uid = $userModel->add ( array (
					"phone" => $account,
					"password" => md5 ( $password . $salt),
					"ctime" => date ( "Y-m-d H:i:s", time () ),
					"avatar" => "data/uploads/default/head.png",
					"nickname" => "kd" . rand(100000, 200000),	
					"active" => 1,
					"salt" => $salt
			) );
		} else if ($options['register_type'] == "sns") {
			$uid = $userModel->add ( array (
					"nickname" => "kd" . rand(100000, 200000),	
					"avatar" => $options['avatar'],
					"location" => $options['location'],
					"ctime" => date ( "Y-m-d H:i:s", time () ),
					"active" => 1
			) );
		} else {
			return array(
				"flag" => 0,
				"message" => "账号无效"
			); // 无效
		}
		
		if ($uid) {
			return array(
				"flag" => 1,
				"message" => "注册成功",
				"avatar" => "data/uploads/default/head.png",
				"uid" => $uid,
				"nickname" => "酷多用户",
			);
		} else {
			return array(
				"flag" => 0,
				"message" => "注册失败",
				"avatar" => "data/uploads/default/head.png",
				"uid" => $uid,
				"nickname" => "酷多用户",
			);
		}
		
	}
	
	/**
	 * 检测邮箱是否已经被注册
	 * @param $email  用户邮箱
	 */
	public function checkEmailRegistered($email) {
		$userModel = D("User");
		return !!$userModel->where("email = '%s'", array($email))->find();
	}
	
	/**
	 * 检验手机号码是否已经被注册
	 * @param $phone 手机号码
	 */
	public function checkPhoneRegistered($phone) {
		$userModel = D("User");
		return !!$userModel->where("phone = '%s'", array($phone))->find();
	}
	
	
	/**
	 * 根据账号获取用户数据
	 * @param $account  用户帐号（邮箱、手机号、有声ID, $uid）
	 * @return array $userData;
	 */
	public function getUserData($account) {
		$userModel = D("User");
		$userData; 			//用户数据
		if (is_mail($account)) {
			$userData = $userModel->where("email = '$account'")->find();
		} else if (is_mobile_phone($account)) {
			$userData = $userModel->where("phone = '$account'")->find();
		} else {
			$userData = $userModel->where("uid = '$account'")->find();
		}
		return $userData;
	}
	
	/**
	 * 获取用户的盐值
	 * @param $account 用户帐号（邮箱、手机、有声ID）
	 * @return $salt  盐值
	 */
	public function getSalt($account) {
		$userModel = D("User");
		if (is_mail($email)) {
			return 	$userModel->where("email = '$account'")->getField("salt");
		} else if (is_mobile_phone($account)) {
			return $userModel->where("phone = '$account'")->getField("salt");
		} else {
			return $userModel->where("usheng_id='$account'")->getField("salt");
		}
	}
	
	/**
	 * 生成用户的盐值
	 * @return $salt 盐值
	 */
	private function createSalt() {
		return mt_rand(10000, 20000);
	}


	/**
	 * 获取用户的基本信息
	 * @param String $uid
	 */
	public function getBasicInfo($uid) {
		$userModel = D("user");
		$basicInfo = $userModel->where("uid = '$uid'")->find();
		$area = M("Area");
		$pid = $basicInfo["province"];
		$cid = $basicInfo["city"];
		$provinceName = $area->where("area_id = '$pid'")->getField("title");
		$cityName = $area->where("area_id = '$cid'")->getField("title");
		$basicInfo["province"] = array("area_id" => $pid, "title" => $provinceName);
		$basicInfo["city"] = array("area_id" => $cid, "title" => $cityName);
		return $basicInfo;
	}


	/**
	 * 保存用户的基本信息
	 * @param String $uid
	 * @param array $basicInfo
	 */

	public function saveBasicInfo($uid, $basicInfo) {
		$userModel = D("User");
		$userModel->where("uid = '$uid'")->save($basicInfo);
	}

	public function loadCitiesOfProvince($provinceId) {
		$area = M("Area");
		$cities = $area->where("pid = '$provinceId'")->select();
		$cache = S("area_cache");
		$cache["$provinceId"] = $cities;
		S("area_cache", $cache);
		return $cities;
	}

	public function savePassword($uid, $password) {
		$userModel = D("User");
		$account = $userModel->where("uid = '$uid'")->getField("account");
		$newSalt = $this->createSalt();
		$newPassword  = md5($password . $newSalt);
		$userModel->where("uid = '$uid'")->save(array("password" => $newPassword, "salt" => $newSalt));
	}

	/**
	* =======================重置密码===========================
	*/
	public function resetPassword($account, $password) {
		$newSalt = $this->createSalt();
		$newPassword = md5($password . $newSalt);
		if (is_mail($account)) {
			D("User")->where("email = '$account'")->save(array("password" => $newPassword, "salt" => $newSalt));
		} else if (is_mobile_phone($account)) {
			D("User")->where("phone = '$account'")->save(array("password" => $newPassword, "salt" => $newSalt));
		}
	}

	public function saveAvatar($uid, $avatarPath) {
		$userModel = D("User");
		$userModel->where("uid = '$uid'")->save(array("avatar" => $avatarPath));
	}

	/**
	* 检验第三方用户是否已经注册
	* @param $openid 第三方id
	* @param $type 类型
	* @return boolean
	*/
	public function checkUserSNSRegister($openid, $type) {
		return D("UserSns")->where("openid = '$openid' and type = '$type'")->find();
	}

	/**
	* 第三方用户信息保存
	*/
	public function saveSNSInfo($openid, $token, $type, $remind, $uid, $other = "") {
		return D("UserSns")->add(array(
			"openid" => $openid,
			"token" => $token,
			"expire" => $remind,
			"uid" => $uid,
			"type" => $type,
			"other" => $other
		));
	}
}