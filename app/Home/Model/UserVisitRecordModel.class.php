<?php

namespace Home\Model;
use Think\Model;

class UserVisitRecordModel extends Model {
	/**
	 * 记录用户扫描时候的信息、扫描人(如果有登录)、扫描的终端、扫描时间、来源ip
	 *
	 * @param
	 *        	string shortUrl 短地址
	 * @var ip IP地址
	 * @var time 扫描时间
	 * @var uid 用户id
	 * @var device 终端设备
	 * @author 李珠刚
	 */
	public function setRecord() {
		$refer = $_SERVER ['HTTP_REFERER'] == null ? "" : $_SERVER ['HTTP_REFERER']; // 上一次的网址
		$url = $_SERVER ["REQUEST_URI"]; // 当前网址路径
		$ip = $_SERVER ['REMOTE_ADDR']; // 来源ip地址
		$time = date ( "Y-m-d h:i:s", time () ); // 时间
		$userAgent = $_SERVER ['HTTP_USER_AGENT']; // 终端设备
		$this->add ( array (
				"ip" => $ip,
				"time" => $time,
				"refer" => $refer,
				"user_agent" => $userAgent,
				"url" => $url 
		) );
	}
}
?>
