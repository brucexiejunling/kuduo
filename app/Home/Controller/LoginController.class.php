<?php

namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
	
	/**
	 * 模板显示、登录页面
	 */
	public function index() {
		// 新浪微博、产生认证链接
		Vendor ( "SaeTOAuthV2" );
		$sinaSNS = new \SaeTOAuthV2 ( C ( "SNS_OPEN_SINA.APP_KEY" ), C ( "SNS_OPEN_SINA.APP_SECRET" ) );
		$sinaSNSURL = $sinaSNS->getAuthorizeURL ( C ( "SNS_OPEN_SINA.CALLBACK_URL" ) );
		
		session ( "sns_redirect", $redirect );
		$this->assign ( "sina_url", $sinaSNSURL );
		                                      
		// 判断是否是移动端、若为移动端则显示移动端的模板
		if (is_mobile_device()) {
			if (session("uid")) {
				$continue = I("get.continue");
				if (!I("get.sess")) {
					if (strpos($continue, "?") > -1) {
						header("location:" . urldecode($continue) . "&sess=" . session_id());
					} else {
						header("location:" . urldecode($continue) . "?sess=" . session_id());
					}
				}
			}
			$this->display ( "Login:mobile_index" );
		} else {
			$this->display ();
		}
	}
	
	/**
	 * 用户登录函数
	 * 
	 * @author 李珠刚
	 */
	public function getLogin() {
		$account = I ( "post.account" );				//用户的账号
		$password = I ("post.password" );			//用户的明文密码
		$options = I("post.options");
		$stayTime = $options['stayTime']? intval($options['stayTime']) : 7;		       //保持登录的天数
		
		$result = D("User", "Service")->login($account, $password, 
				array("stayTime" => $stayTime));
		
		$this->ajaxReturn($result);
	}
	
	/**
	 * sns登录调用API
	 * 
	 * @param int $uid
	 *        	用户id
	 */
	public function getLoginSNS($uid) {
		$flag = D ( "User" )->getLoginSNS ( $uid ); // 这边使用了复杂的手法、虽然只用到了uid、但这种写法具有
		                                      // 一定的扩展性、以后session值增加更易于扩展
		if ($flag) {
			session_set_cookie_params ( 3600 * 24, "/" );
			session ( '[regenerate]' );
			session ( "uid", $flag ['uid'] ); // 用户的id
			cookie ( "uid", $flag ['uid'], time () + 3600 * 24, "/" ); // 设置Cookie
			return true;
		}
		return false;
	}
}

?>