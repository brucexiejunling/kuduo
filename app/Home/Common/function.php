<?php

/**
 * 邮件发送函数
 * 
 * @param string $host      主机名
 * @param string $user        	邮局用户
 * @param string $userName        	发送人称呼
 * @param string $pasword        	邮局密码
 * @param string $mailName        	邮件名称
 * @param string $reveiver        	接受者邮箱
 * @param string $receiverName        	接受者名称
 * @param string $content        	邮件内容
 * @param file $attachment        	附件
 * @author 李珠刚
 *        
 */
function send_mail($userName, $mailName, $receiver, $receiverName, $content, $attachment) {
	// 引入phpMailer插件
	vendor ( "phpMail.phpmailer" );
	$mail = new \PHPMailer (); // 建立邮件发送类
	$mail->IsSMTP (); // 使用SMTP方式发送
	$mail->CharSet = "utf-8";
	$mail->SMTPAuth = true; // 启用SMTP验证功能
	
	$mail->Host = C("MAIL_HOST");
	$mail->Username = C("MAIL_USER"); // 邮局用户名(请填写完整的email地址)
	$mail->Password = C("MAIL_PASSWORD"); // 邮局密码
	
	$mail->From = C("MAIL_USER"); // 邮件发送者email地址
	
	$mail->FromName = $userName;
	
	$mail->AddAddress ( $receiver, $receiverName ); // 收件人地址，可以替换成任何想要接收邮件的email信箱,格式是AddAddress("收件人email","收件人姓名")
	$mail->IsHTML ( true ); // 是否使用HTML格式
	
	$mail->Body = $content;
	$mail->Subject = $mailName; // 邮件标题
	if (! $mail->Send ()) {
		return array (
				"flag" => 0,
				"message" => $mail->ErrorInfo 
		);
	} else {
		return (array (
				"flag" => 1,
				"message" => "发送成功" 
		));
	}
}

/**
 * 发送手机验证码
 * 
 * @param string $phone        	
 * @param string $msg        	
 * @author 李珠刚
 *        
 */
function sendsmscode($phone, $msg) {
	vendor ( "sms" );
	$sms = new sms ();

	$flag = $sms->sendnote ( $phone, $msg );
	
	if ($flag != 1) {
		return array(
				"flag" => "0",
				"message" => "Error:" . $flag 
		);
	} else {
		return array(
			"flag" => 1,
			"message" => "发送成功"
		);
	}
}

/**
* ====================过滤文本内容中的恶意标签=========================
*/
function str_filter($str) 
{ 
    $farr = array( 
        "/\s+/", //过滤多余空白 
         //过滤 <script>等可能引入恶意内容或恶意改变显示布局的代码,如果不需要插入flash等,还可以加入<object>的过滤 
        "/<(\/?)(script|i?frame|style|html|body|title|link|meta|\?|\%)([^>]*?)>/isU",
        "/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU",//过滤javascript的on事件 
   ); 
 
    $tarr = array(
            "",
            "",//如果要直接清除不安全的标签，这里可以留空
            "",
    );
   $str = preg_replace( $farr,$tarr,$str); 
   return $str; 
}

/**
 * 判断是否是微信浏览器
 * 
 * @author 李珠刚
 */
function is_weixin() {
	$userAgent = $_SERVER ['HTTP_USER_AGENT'];
	if (preg_match ( "/micromessenger/", strtolower ( $userAgent ) )) {
		return true;
	} else {
		return false;
	}
}

/**
 * 根据用户的userAgent等来判断来自移动端还是PC端/需要持续完善
 * 
 * @author 李珠刚
 * @return bool
 */
function is_mobile_device() {
	
	// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
	if (isset ( $_SERVER ['HTTP_X_WAP_PROFILE'] )) {
		return true;
	}
	
	// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
	if (isset ( $_SERVER ['HTTP_VIA'] )) {
		// 找不到为flase,否则为true
		if (stristr ( $_SERVER ['HTTP_VIA'], "wap" )) {
			return true;
		}
	}
	
	$userAgent = $_SERVER ['HTTP_USER_AGENT']; // 用户userAgent信息
	                                          
	// userAgent关键词命中
	$deviceKey = array (
			'nokia',
			'sony',
			'ericsson',
			'mot',
			'samsung',
			'htc',
			'sgh',
			'lg',
			'sharp',
			'sie-',
			'philips',
			'panasonic',
			'alcatel',
			'lenovo',
			'iphone',
			'ipod',
			'blackberry',
			'meizu',
			'android',
			'netfront',
			'symbian',
			'ucweb',
			'windowsce',
			'palm',
			'operamini',
			'operamobi',
			'openwave',
			'nexusone',
			'cldc',
			'midp',
			'wap',
			'mobile',
			'phone' 
	);
	
	// 从HTTP_USER_AGENT中查找手机浏览器的关键字
	if (preg_match ( "/(" . implode ( '|', $deviceKey ) . ")/i", strtolower ( $userAgent ) )) {
		return true;
	}
	return false;
}

/**
 * ==========================文件大小格式化=================================
 * @param $filesize kb为单位的数字
 */
function filesize_format($filesize) {
	$size = $filesize . "B";
	if ($filesize > 1024) {
		$size = round($filesize / 1024, 2) . "KB";
	}

	if ($filesize / 1024 > 1024) {
		$size = round($filesize / 1024 / 1024, 2) . "MB";
	}

	if ($filesize / 1024 / 1024 / 1024 > 1024) {
		$size = round($filesize / 1024 / 1024 / 1024, 2) . "GB";
	}
	return $size;
}

/**
 * ==========================获取浏览器信息=============================
 * @return array 
 * array(
 * 		"browser" => "safari",
 * 		"version"  => "531.7"
 * );
 */
function get_browser_data() {
	$http_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$browser = "unknown";
	$browserVersion = "unknown";
	
	//chrome浏览器
	if (preg_match("/chrome\/([0-9\.]+)/i", $http_user_agent, $match) && !preg_match("/safari/", $http_user_agent)) {
		$browser = "chrome";
		$browserVersion = $match[1];
	}
	
	//safari浏览器
	if (preg_match("/safari\/([0-9\.]+)/i", $http_user_agent, $match) && !preg_match("/chome/", $http_user_agent)) {
		$browser = "safari";
		$browserVersion = $match[1];
	}
	
	//UC浏览器
	if (preg_match("/ucbrowser\/([0-9\.]+)/", $http_user_agent, $match)) {
		$browser = "ucbroswer";
		$browserVersion = $match[1];
	}
	
	//微信内置浏览器
	if (preg_match("/micromessenger\/([0-9\.]+)/", $http_user_agent, $match)) {
		$browser = "micromessenger";
		$browserVersion = $match[1];
	}
	
	//火狐浏览器
	if (preg_match("/firefox\/([0-9\.]+)/", $http_user_agent, $match)) {
		$browser = "firefox";
		$browserVersion = $match[1];
	}
	
	//搜狗移动浏览器
	if (preg_match("/sogoumobilebrowser\/([0-9\.]+)/", $http_user_agent, $match)) {
		$browser = "sogoumobilebrowser";
		$browserVersion = $match[1];
	}
	
	//360浏览器
	if (preg_match("/360 aphone browser \(([0-9\.]+)\)/", $http_user_agent, $match)) {
		$browser = "360browser";
		$browserVersion = $match[1];
	}
	
	//360浏览器
	if (preg_match("/opera\/([0-9\.]+)/", $http_user_agent, $match)) {
		$browser = "opera";
		$browserVersion = $match[1];
	}
	
	return array(
		"browser" => $browser,
		"version" => $browserVersion
	);
}


/**
 * =============================获取操作系统信息==========================
 */
function get_system_data() {
	$system = "unknown";
	$systemVersion = "unknown";
	
	$http_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if (preg_match("/android ([0-9\.]+)/", $http_user_agent, $match)) {
		$system = "android";
		$systemVersion = $match[1];
	}
	
	return array(
		"system" => $system,
		"version" => $systemVersion
	);
}

/**
 * ===========================获取设备信息=================================
 */
function get_device_data() {
	$device = "unknown";
	$http_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	if (preg_match("/mi 2/", $http_user_agent)) {
		$device = "MI 2";
	}
	return $device;
}

/**
 * 十六进制颜色和RGB进行转换
 * 
 * @param [string] $hex
 *        	[十六进制]
 * @return [array] [RGB]
 */
function hex2rgb($hex) {
	$hex = str_replace ( "#", "", $hex );
	
	if (strlen ( $hex ) == 3) {
		$r = hexdec ( substr ( $hex, 0, 1 ) . substr ( $hex, 0, 1 ) );
		$g = hexdec ( substr ( $hex, 1, 1 ) . substr ( $hex, 1, 1 ) );
		$b = hexdec ( substr ( $hex, 2, 1 ) . substr ( $hex, 2, 1 ) );
	} else {
		$r = hexdec ( substr ( $hex, 0, 2 ) );
		$g = hexdec ( substr ( $hex, 2, 2 ) );
		$b = hexdec ( substr ( $hex, 4, 2 ) );
	}
	// return implode(",", $rgb);
	$rgb = array (
			$r,
			$g,
			$b 
	);
	// returns the rgb values separated by commas
	return $rgb; // returns an array with the rgb values
}

/**
 * 二维码配置信息转换
 * 
 * @param [type] $post
 *        	[description]
 * @return [type] [description]
 */
function convertToConfig($post, $data) {
	$border = Array (
			"color" => hex2rgb ( $post ['borderColor'] ),
			"type" => $post ['borderType'],
			'alpha' => Array (
					"black" => $post ['borderBlackAlpha'],
					"white" => $post ['borderWhiteAlpha'] 
			) 
	);
	$fill = Array (
			"color" => hex2rgb ( $post ['fillColor'] ),
			"black_type" => $post ['fillBlackType'],
			"white_type" => $post ['fillWhiteType'],
			'alpha' => Array (
					"black" => $post ['fillBlackAlpha'],
					"white" => $post ['fillWhiteAlpha'] 
			) 
	);
	
	$config = Array (
			"data" => $post ['data'],
			"border" => $border,
			"fill" => $fill,
			"imageURL" => $post ['imageURL'],
			"posX" => $post ['posX'],
			"posY" => $post ['posY'],
			"qrWidth" => $post ['qrWidth'],
			"qrHeight" => $post ['qrHeight'] 
	);
	
	return $config;
}

/**
 * 把image输出为文件(待更新)
 * 
 * @param base64 $data
 *        	base64图像数据
 * @param string $filename
 *        	文件存储位置的相对路径
 * @return string $output 文件存储的位置
 * @author 李展威
 */
function saveImageToFile($data, $filename) {
	// 若目标路径已经存在, 则不可进行生成
	if (file_exists ( $filename )) {
		return array (
				'msg' => '目标文件已存在',
				'status' => false 
		);
	}
	
	// 检查目标路径所在文件夹是否存在, 若不存在则创建该文件夹
	$pathinfo = pathinfo ( $filename );
	if (! file_exists ( $pathinfo ['dirname'] )) {
		$result = mkdir ( $pathinfo ['dirname'], 0755, true );
		if (! $result) {
			return array (
					'msg' => '目标路径文件夹不存在, 无法创建',
					'status' => false 
			);
		}
	}
	
	// 打开文件准备写入
	$file = fopen ( $filename, "w" );
	
	// 若无法写入
	if (! fwrite ( $file, $data )) {
		return array (
				'msg' => '写入失败',
				'status' => false 
		);
	}
	
	fclose ( $file );
	return array (
			'msg' => '写入成功',
			'status' => true,
			'data' => $filename 
	);
}

/**
 * 音频转换函数 把其他格式函数转换为MP3格式, 需要ffmpeg及其相关解码支持
 * 
 * @param [string] $str
 *        	源文件路径 如./data/audio/*.wav
 * @return [string] msg 转换成功与否
 *         [string] data 输出文件路径
 */
function audioConvert($str) {
	if (! file_exists ( $str )) {
		return Array (
				"msg" => "文件不存在",
				"status" => false 
		);
	}
	
	$info = pathinfo ( $str );
	if ($info ['basename'] == "mp3") {
		return Array (
				"msg" => "已经是mp3格式, 无需转换",
				"status" => true,
				"data" => $str 
		);
	}
	$filename = $info ['filename'];
	$dirname = $info ['dirname'] . "/";
	
	$output = $filename . ".mp3";
	
	exec ( "ffmpeg -i $str -vn -ar 44100 -ac 2 -ab 192 -f mp3 " . $dirname . $output );
	return Array (
			"msg" => "转换成功",
			"status" => true,
			"data" => $dirname . $output 
	);
}

/**
 * 视频转换函数 把其他格式函数转换为mp4格式, 需要ffmpeg及其相关解码支持
 * 
 * @param [string] $str
 *        	源文件路径 如./data/audio/*.wav
 * @return [string] msg 转换成功与否
 *         [string] data 输出文件路径
 */
function videoConvert($str) {
	set_time_limit ( 0 );
	if (! file_exists ( $str )) {
		return Array (
				"msg" => "文件不存在",
				"status" => false 
		);
	}
	
	$info = pathinfo ( $str );
	if ($info ['basename'] == "mp4" || $info ['basename'] == "webm") {
		return Array (
				"msg" => "已经是mp4格式, 无需转换",
				"status" => true,
				"data" => $str 
		);
	}
	$filename = $info ['filename'];
	$dirname = $info ['dirname'] . "/";
	
	$output = $filename . ".mp4";
	exec ( "ffmpeg -i $str -vcodec libx264 -acodec aac -strict -2 " . $dirname . $output );
	return Array (
			"msg" => "转换成功",
			"status" => true,
			"data" => $dirname . $output 
	);
}


/**
 * 验证码验证
 */
function check_verify($code, $id = "") {
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 生成验证码
 */
function create_verify($setting) {
	$setting = array(
		"length"  => 4,			//长度
		"useImgBg" => false
	);
	
	$Verify = new \Think\Verify($setting);
	//$Verify->useImgBg = true;
	$Verify->entry();
}

/**
 * 检验邮箱合法性
 * @param $email 用户邮箱
 * @return boolean 是否为合格邮箱
 */
function is_mail($email) {
	$regxMail = "/^[a-zA-Z0-9-._]{1,50}@[a-zA-Z0-9-]{1,65}.(com|net|org|info|biz|([a-z]{2,3}.[a-z]{2}))$/i";
	return !!preg_match ( $regxMail, $email );
}

/**
 * 检查是否为手机号码
 * @param $phone 用户手机
 * @return boolean
 */
function is_mobile_phone($phone) {
	$regxPhone = "/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/";
	return !!preg_match ( $regxPhone, $phone );
}

/**
 * 检查是否符合短地址格式、即short_code
 * @param $shortCode 短地址的码
 */
function is_short_code($shortCode) {
	$regxCode = "/^[a-zA-Z0-9_]{3,8}$/";
	return preg_match($regxCode, $shortCode) ? true : false;
}

/**
 * 检查密码格式是否正确
 * @param $password 密码
 */

function is_password_valid($password) {
	$regxCode = "/^[a-zA-Z0-9_]{6,16}$/";
	return preg_match($regxCode, $password) ? true : false;
}

/**
* 下载远程文件并保存到本地
* @param $savepath 保存地址
* @param $url 地址
*/
function download_file($url, $savepath = "", $savename = "") {
	$file = file_get_contents($url);
	mkdir($savepath, 0777, true);
	file_put_contents($savepath . $savename, $file);
}
?>