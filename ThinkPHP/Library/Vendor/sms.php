<?php
class sms {
	public $comid = "1832";
	public $username = "diancanba";
	public $userpwd = "od2014";
	public $smsnumber = "10690";
	public function sendnote($mobtel, $msg) {
		$username = $this->username;
		$userpwd = $this->userpwd;
		$smsnumber = $this->smsnumber;
		$comid = $this->comid;
		$msg = urlencode ( mb_convert_encoding ( $msg, 'gbk', 'utf-8' ) );
		$url = "http://jiekou.56dxw.com/sms/HttpInterface.aspx?comid=$comid&username=$username&userpwd=$userpwd&handtel=$mobtel&sendcontent=$msg&sendtime=&smsnumber=$smsnumber";
		$flag = file_get_contents ( $url );
		return $flag;
	}
}
?>