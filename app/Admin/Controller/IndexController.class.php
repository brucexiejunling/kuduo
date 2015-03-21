<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
     //   if (!session("admin_id")) {
   		// 	$this->redirect("/login/");
   		// }
   		$this->display();
    }
}