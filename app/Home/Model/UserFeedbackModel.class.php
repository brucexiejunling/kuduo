<?php


namespace Home\Model;
use Think\Model;

class UserFeedbackModel extends Model {

	//@param $userID 用户名
	//@param $content 反馈内容
	//@param $contact  联系方式
	//@param $userIP   用户IP
	public function putFeedback($userID, $content, $contact, $userIP) {
		$data['uid'] = $userID;
		$data['content'] = $content;
		$data['contact'] = $contact;
		$data['IP'] = $userIP;
		$data['ctime'] = date("Y-m-d H:i:s", time());			//创建时间
		
		if ($this->add($data)) {
			return true;
		}
		var_dump($this->getDbError());
		return false;
	}	
}