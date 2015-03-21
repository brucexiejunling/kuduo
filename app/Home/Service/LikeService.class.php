<?php

namespace  Home\Service;
use Think\Model;

class LikeService extends BaseService {
	
	//添加取消赞
	public function like($shortCode) {
		$uid = session("uid");
		$ip = get_client_ip();
		if ($uid) {
			$flag = D("UserLike")->where("uid = '$uid' AND short_code = '$shortCode' AND isdel = 0")->find();
			if ($flag) {
				$curId = $flag['id'];
				D("UserLike")->where("id = $curId")->save(array("isdel" => 1));
				D("Qr")->where("short_code = '$shortCode'")->setInc("like_count", -1);
			} else {
				if  (D("Qr")->where("short_code = '$shortCode'")->setInc("like_count", 1)) {
					//写入数据库
					D("UserLike")->add(array(
					"uid" => $uid,
					"ip" => $ip,
					"short_code" => $shortCode,
					"isdel" => 0,
					"cid" => 0
					));
				}
			}
			return true;
		} else {
			if (!cookie("like_" . $shortCode)) {
				if (D("Qr")->where("short_code = '$shortCode'")->setInc("like_count")) {
					//写入数据库
					D("UserLike")->add(array(
					"uid" => 0,
					"ip" => $ip,
					"short_code" => $shortCode,
					"isdel" => 0,
					"cid" => 0
					));
					cookie("like_" . $shortCode, true, 36000);			//cookie记录防止未登录用户重复点赞
					return true;
				}
			}
			return false;
		}
	}
	
	/**
	 * =======================获取用户赞的状态===========================
	 */
	public function getLikeStatus($uid, $shortCode) {
		return D("UserLike")->where("uid = '$uid' AND isdel = 0 AND short_code = '$shortCode'")->find();
	}
}