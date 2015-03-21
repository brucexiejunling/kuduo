<?php
namespace Home\Controller;
use Think\Controller;

//短地址指向专用控制器
class UrlController extends Controller {
	public function _empty() {
		$shortCode =  ACTION_NAME;
		
		if (is_short_code($shortCode)) {
			$result = D("ShortUrl", "Service")->getShortUrlData($shortCode);
			if ($result) {
				$url = $result['url'];
				D("ShortUrl", "Service")->scanRecord($shortCode);			//扫描数据记录
				header("location:" . $url);
			} else {
				header("location: " . C("DOMAIN_NAME"));
			}
		} else {
			header("location:" . C("DOMAIN_NAME"));
		}
	}
}