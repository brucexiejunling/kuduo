<?php

namespace Home\Model;
use Think\Model;

class ValidCodeModel extends Model {
	/**
	 * 数据表字段
	 */
	protected $fields = array('refer', 'code', 'uid', 'type', 'ctime', 'status');
}

?>
