<?php

namespace Home\Controller;
use Think\Controller;

class ApiController extends Controller {
	/**
	 * ==============================产生验证码=================================
	 */
	public function createVerify() {
		create_verify();
	}
	/**
	 * ================================二维码API介绍首页=====================
	 */
	public function index() {
		$this->display();
	}
	
	/**
	 * ======================点赞、取消赞====================================
	 */
	public function like() {
		$shortCode = I("post.code");			//获取短地址
		
		if (is_short_code($shortCode)) {
			if (D("Like", "Service")->like($shortCode)) {
				$this->ajaxReturn(array(
						"code" => 100000,
						"message" => "处理成功"
				));
			} else {
				$this->ajaxReturn(array(
						"code" => 200000,
						"message" => "处理成功"
				));
			}
		} else {
			$this->ajaxReturn(array(
					"code" => 200000,
					"message" => "处理失败"
			));
		}
	}
	/**
	* ==============================点击直接下载文件=====================
	*/
	public function download() {
		$url = I("get.code");
		if (file_exists($url)) {
			$filesize = filesize($url);
			$filenameArray = explode("/", $url);
			$filename = $filenameArray[count($filenameArray) - 1];			//分割字符串获取文件名
			header("Content-Type: application/force-download");; 
			header("Accept-Length:" . $filesize); 
			header("Content-Disposition: attachment; filename=" . $filename); 
			readfile($url);
		} else {
			$file = file_get_contents($url);
			$filenameArray = explode("/", $url);
			$filename = $filenameArray[count($filenameArray) - 1];			//分割字符串获取文件名
			header("Content-type: application/octet-stream"); 
			header("Content-Disposition: attachment; filename=" . $filename); 
			echo $file;
		}
	}
}