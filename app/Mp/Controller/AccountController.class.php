<?php

namespace Mp\Controller;
use Think\Controller;

class AccountController extends Controller {
	//用户登录页面
	public function login() {
		$this->display("Account:login");
	}
	
	//用户注册页面
	public function register() {
		$this->display("Account:register");
	}
}