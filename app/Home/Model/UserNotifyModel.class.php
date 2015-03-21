<?php

namespace Home\Model;
use Think\Model;

class UserNotifyModel extends Model {
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
}

?>