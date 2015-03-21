<?php

namespace Home\Model;
use Think\Model;

class QrContentModel extends Model {
	
	/**
	 *
	 * 把二维码内容插入到qr_content中
	 *
	 * @access public
	 * @param
	 *        	string 短地址
	 * @param
	 *        	string 要插入的内容
	 * @author 李珠刚
	 * @version 1.0.0
	 * @link checkShortUrlExsit
	 */
	public function insertQRContent($shortUrl, $content) {
		// 先判断是否存在该短地址对应的内容
		$data = array (
				"qr_url_short" => $shortUrl,
				"content" => $content 
		);
		if ($this->checkShortUrlExsit ( $shortUrl )) {
			return $this->where ( "qr_url_short = '$shortUrl'" )->save ( $data ); // 保存即覆盖
		} else {
			return $this->add ( $data ); // 添加
		}
	}
	
	/**
	 * 判断该短地址在content中是否存在、如果存在则该操作为覆盖
	 *
	 * @param string $shortUrl
	 *        	短地址
	 * @author 李珠刚
	 * @return void
	 */
	private function checkShortUrlExsit($shortUrl) {
		return $this->where ( "qr_url_short = '$shortUrl'" )->find () === null ? false : true;
	}
	
	/**
	 * 向数据库添加一条记录
	 * 
	 * @author 李展威
	 */
	public function append($data) {
		return $this->data ( $data )->add ();
	}
	
	/**
	 * 移除数据库中某条记录
	 * 
	 * @author 李展威
	 */
	public function remove($data) {
		return $this->where ( $data )->delete ();
	}
	
	/**
	 * 更新数据库中的记录
	 * 
	 * @author 李展威
	 */
	public function update($data) {
		return $this->data ( $data )->save ();
	}
	
	/**
	 * 查询多条记录
	 * 
	 * @author 李展威
	 */
	public function multiQuery($data) {
		return $this->where ( $data )->select ();
	}
	
	/**
	 * 查询一条记录
	 * 
	 * @author 李展威
	 */
	public function singleQuery($data) {
		return $this->where ( $data )->find ();
	}
	
	/**
	 * 查询数据库总记录数
	 * 
	 * @author 李展威
	 */
	public function sum() {
		return $this->count ();
	}
	
	/**
	 * 根据短地址获取二维码信息
	 * 
	 * @param string $url
	 *        	二维码短地址
	 * @return array
	 */
	public function getQRContentByShortURL($url) {
		return $this->where ( "qr_url_short = '$url'" )->find ();
	}
}

?>