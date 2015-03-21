<?php
namespace Voice\Controller;
use Think\Controller;

class IndexController extends  Controller {
	public function index() {
		$this->display("index");
	}
	
	//反馈内容接受
	public function feedback() {
		if (isset($_POST)) {
			$value = I("post.value");		//反馈内容
			$contact = I("post.contact");
		}
		
		$ip = get_client_ip();
		M("feedback")->add(array(
			"content" => $value,
			"contact" => $contact,
			'IP' => $ip,
			"ctime" => date("Y-m-d H:i:s", time())
		));
	}
}