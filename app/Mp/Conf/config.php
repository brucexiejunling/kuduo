<?php
return array(
	
		'URL_CASE_INSENSITIVE'  => false,   // 默认false 表示URL区分大小写 true则表示不区分大小写
		'URL_MODEL'             => 2,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
		
		//路由设置
		'URL_ROUTER_ON'   => true,
		'URL_MAP_RULES' => array(
				'login' => "account/login",
				'register' => "account/register",
		),
);