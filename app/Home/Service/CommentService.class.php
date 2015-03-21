<?php
namespace Home\Service;
use Think\Model;
class CommentService extends BaseService {
	/**
	 * ====================添加留言======================
	 * @param $shortCode 短地址的码
	 * @param $uid 留言者id
	 * @param $content 留言内容
	 * 
	 */
	public function addComment($shortCode, $uid, $content) {
		$commentId = D("UserComment")->add(array(
			"short_code" => $shortCode,
			"uid" => $uid,
			"content" => $content,
			"ctime" => date("Y-m-d H:i:s", time()),
			"isdel" => 0,
			"floor" => 0,
			"cid" => 0
		));
		
		return $commentId;
	}
}