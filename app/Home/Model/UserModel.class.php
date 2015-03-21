<?php

namespace Home\Model;
use Think\Model;

class UserModel extends Model {
	
	/**
	 * 数据表字段
	 * @var uid   自增主键
	 * @var email  唯一字段
	 */
	protected $fields = array('uid', 'nickname', 'avatar', 'email', 'birthday',
			'password', 'salt', 'phone', 'province', 'usheng_id', 'gender',
			'city', 'location', 'username', 'active', 'ctime', 'introduction'
	);
	
	/**
	 * 数据库主键
	 */
	protected $pk  = 'uid';
}

?>
