<?php
namespace Home\Service;
use Think\Model;

class NotifyService extends BaseService {
	/**
	 * ==================添加单个用户添加单条通知==================
	 * @param $uid 通知用户的uid
	 * @param $content 通知的提醒词
	 */
	public function addNotify($uid,  $content) {
		return D("UserNotify")->add(array(
			"uid" => $uid,
			"content" => $content,
			"ctime" => date("Y-m-d H:i:s", time()),
			"status" => 0
		));		
	}
	
	/**
	 * ===================获取消息状态，有几条消息==================
	 * @return int
	 */
	public function getMessageStatus() {
		$uid = session("uid");
		return D("UserNotify")->where("uid = '$uid' AND status = 0")->count();
	}
}