<?php

namespace Home\Model;
use Think\Model;

class UserSnsModel extends Model {
	/**
	 * 根据openid/type查询用户是否已注册
	 * 
	 * @param string $openid
	 *        	公共id
	 * @param string $type
	 *        	sns类型
	 * @return int $uid 如果已经注册、则返回对应的用户id
	 */
	public function checkUserSNSRegister($openid, $type) {
		return $this->where ( "openid = '$openid' AND type = '$type'" )->find ();
	}
	
	/**
	 * 根据uid/type查询用户是否已经注册
	 * 
	 * @param int $uid
	 *        	用户id
	 * @param string $type
	 *        	第三方平台类型
	 * @return time 过期时间
	 */
	public function checkUserSNSRegisterByUid($uid, $type) {
		return $this->where ( "uid = '$uid' AND type = '$type'" )->find ();
	}
	
	/**
	 * 保存sns信息
	 * 
	 * @param string $openid
	 *        	第三方id
	 * @param string $type
	 *        	第三方类型
	 * @param string $token
	 *        	access_token
	 * @param
	 *        	int expire 多久后过期、保存时采用 time() + expire
	 * @param
	 *        	int uid 本站用户id
	 * @return int 新建的id
	 */
	public function createSNS($openid, $token, $type, $expire, $uid) {
		$expire = date ( "Y-m-d H:i:s", time () + $expire ); // 过期的时间
		return $this->add ( array (
				"openid" => $openid,
				"token" => $token,
				"type" => $type,
				"expire" => $expire,
				"uid" => $uid 
		) );
	}
	
	/**
	 * 更新sns信息
	 * 
	 * @param string $openid
	 *        	第三方id
	 * @param string $type
	 *        	第三方类型
	 * @param string $token
	 *        	access_token
	 * @param
	 *        	int expire 多久后过期、保存时采用 time() + expire
	 */
	public function updateSNS($openid, $token, $type, $expire) {
		$expire = date ( "Y-m-d H:i:s", time () + $expire ); // 过期的时间
		$this->where ( "type = '$type' AND openid = '$openid'" )->save ( array (
				"token" => $token,
				"expire" => $expire 
		) );
	}
}
?>
