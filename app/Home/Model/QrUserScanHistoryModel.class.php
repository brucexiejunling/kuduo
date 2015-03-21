<?php

namespace Home\Model;
use Think\Model;

class QrUserScanHistoryModel extends Model {
	/**
	 * 对已经登陆的用户, 记录其扫描过的二维码
	 *
	 * @param
	 *        	int uid 用户id
	 * @param
	 *        	string shortUrl 短地址
	 * @var record 用户此前扫描此二维码次数, 无则设置为0
	 * @var scanCount 用户此前扫描此二维码次数, 无则设置为0
	 * @author 李展威
	 *        
	 */
	public function setRecord($uid, $shortUrl) {
		$record = $this->where ( "uid = $uid AND qr_url_short = '$shortUrl'" )->find ();
		if (! ! $record) {
			$record ['scan_count'] += 1;
			$this->data ( $record )->save ();
		} else {
			$record = Array (
					'qr_url_short' => $shortUrl,
					'uid' => $uid,
					'scan_count' => 1,
					'isdel' => 0 
			);
			$this->add ( $record );
		}
		return $record;
	}
}
?>
