<?php

namespace Home\Service;
class ShortUrlService extends BaseService {
	
	/**
	 * ================获取一个短地址、锁定状态=================
	 * @param $url  所指向的长地址
	 */
	public function getOneShortUrlCode($url = "") {
		$urlModel = D("ShortUrl");
		$data = $urlModel->lock(true)->where("status = 0")->find();
		$data['status'] = 2;			//2代表已经被锁定使用、还未正式确定
		$data['url'] = $url;
		$data['update_time'] = date("Y-m-d H:i:s", time());
		$shortCode = $data['short_code'];
		$urlModel->where("short_code = '$shortCode'")->save($data);
		$urlModel->commit();
		return $data['short_code'];
	}
	
	/**
	 * ================批量获取短地址、锁定状态=================
	 * @param $url  所指向的长地址
	 */
	public function getMultiShortUrlCode($amount) {
		$urlModel = D("ShortUrl");
		$data = $urlModel->lock(true)->where("status = 0")->limit($amount)->select();
		foreach ($data as &$value) {
			$value['status'] = 2;
			$value['update_time'] = date("Y-m-d H:i:s", time());
		}

		// 锁定短地址状态
		$this->updateMultiShortUrlCode($data);
		$urlModel->commit();
		return $data;
	}
	
	/**
	 * ================批量更新短地址状态=================
	 * @param $url  所指向的长地址
	 */
	public function updateMultiShortUrlCode($shortUrlData) {
		$amount = count($shortUrlData);
		$shortCodeSet = "(";
		$statusCaseSet = "";
		$urlCaseSet = "";
		$time = date("Y-m-d H:i:s", time());
		for ($i = 0; $i < $amount; $i++) {
			$statusCaseSet .= "WHEN short_code = '" . $shortUrlData[$i]['short_code'] . "' THEN '" . $shortUrlData[$i]['status'] . "' ";
			$urlCaseSet .= "WHEN short_code = '" . $shortUrlData[$i]['short_code'] . "' THEN '" . $shortUrlData[$i]['url'] . "' ";
			if ($i + 1 == $amount) {
				$shortCodeSet .= "'" . $shortUrlData[$i]['short_code'] . "')";
			} else {
				$shortCodeSet .= "'" . $shortUrlData[$i]['short_code'] . "',";
			}
		}
		//使用原生优化进行批量更新
		$result = D()->query("UPDATE `u_short_url` SET status = CASE $statusCaseSet END, url = CASE $urlCaseSet END, update_time = '" . $time . "' WHERE short_code IN $shortCodeSet");
		
		return $result;
	}

	/**
	 * ================批量生成短地址======================
	 * @param $number 要生成的短地址数量
	 * @param $length 短地址的长度
	 * @param $type 0代表字母   1代表数字
	 */
	public function createShortUrl($number, $length, $type = 0) {
		set_time_limit(0);
		$String = new \Org\Util\String();
		$randStr = $String::buildCountRand($number, $length, $type);
		$exsitShortUrl = D("ShortUrl")->field("short_code")->select();
		$randStrLength = count($randStr);
		$exsitShortUrlLength = count($exsitShortUrl);
		$tmp = array();
		for ($i = 0; $i < $randStrLength; $i++) {
			$flag = false;
			for ($j = 0; $j < $exsitShortUrlLength; $j++) {
				if ($randStr[$i] == $exsitShortUrl[$j]['short_code']) {
					$flag = true;
				}
			}
			if (!$flag) {
				$tmp[] = $randStr[$i];
			}
		}
		return $tmp;
	}
	
	/**
	 * =====================保存批量生成的短地址==================
	 * @param $shortUrlArray 批量生成的短地址数组
	 */
	public function saveShortUrl($shortUrlArray) {
		$shortUrlLength = count($shortUrlArray);
		$tmp = "";
		$time = date("Y-m-d H:i:s", time());
		for ($i = 0; $i < $shortUrlLength; $i++) {
			if ($i + 1 == $shortUrlLength) {
				$tmp .= "('" . $shortUrlArray[$i] . "', '" . $time . "', " . 0 . ")";
			} else {
				$tmp .= "('" . $shortUrlArray[$i] . "', '" . $time . "', " . 0 . "),";
			}
			
		}
		//使用原生优化插入
		D()->query("INSERT into `u_short_url` (`short_code`, `ctime`, `status`) values $tmp");
	}
	
	/**
	 * =======================根据短地址获取信息===================
	 * @param $shortUrlCode 短地址的Code
	 */
	public function getShortUrlData($shortUrlCode) {
		return D("ShortUrl")->where("short_code = '$shortUrlCode'")->find();
	}
	
	/**
	 * =================更新短地址====================
	 * @param $shortCode 短地址的码
	 * @param $status 短地址状态    默认为0、表示未使用
	 * @param $url 指向的地址	默认为空
	 */
	public function updateShortUrlData($shortCode, $options) {
		if (!is_null($options['status'])) {
			$data['status'] = $options['status']; 
		}
		
		if (!is_null($options['url'])) {
			$data['url'] = $options['url'];
		}
		
		$data['ctime'] = date("Y-m-d H:i:s", time());								//更新时间
		D("ShortUrl")->where("short_code = '$shortCode'")->save($data);
	}
	
	/**
	 * =======================扫描数据记录（跟短地址有关）============================
	 * @param $shortCode
	 */
	public function scanRecord($shortCode) {
		$device = get_device_data();
		$systemData = get_system_data();
		$browser = get_browser_data();
		
		$data['short_code'] = $shortCode;
		$data['ip'] = get_client_ip(0, 1);
		$data['uid'] = session("uid") ? session("uid") : 0;
		$data['system'] = $systemData['system'];
		$data['system_version'] = $systemData['version'];
		$data['browser'] = $browser['browser'];
		$data['browser_version'] = $browser['version'];
		$data['device'] = $device;
		$data['http_refer'] = $_SERVER["HTTP_REFERER"] ? $_SERVER["HTTP_REFERER"] : "";
		$data['ctime'] = date("Y-m-d H:i:s", time());
		$data['http_user_agent'] = $_SERVER['HTTP_USER_AGENT']; 
		
		$Ip = new \Org\Net\IpLocation('UTFWry.dat'); // 实例化类 参数表示IP地址库文件
		$location = $Ip->getlocation();
		$data['location'] = $location['country'] . " " . $location['area'];
		D("QrScanRecord")->add($data);
	}

	/**
	* 二维码使用情况
	*/
	public function getShortCodeUseInfo() {
		$model = D("ShortUrl");
		if (!S("admin_short_code_status_cache")) {
			$data['total'] = $model->count();
			$data['used'] = $model->where("status = 1")->count();
			$data['unused'] = $model->where("status = 0")->count();
			$data['locked'] = $model->where("status = 2")->count();
			S("admin_short_code_status_cache", $data, 60);
		} else {
			$data = S("admin_short_code_status_cache");
		}
		return $data;
	}
}