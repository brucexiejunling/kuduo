<?php

namespace Home\Controller;
use Think\Controller;
class EmptyController extends Controller {
	function _empty() {
		$this->display("Common:404");
	}
	function index() {
		$this->display("Common:404");
	}
}
?>
