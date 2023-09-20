<?php
class main extends controller {
	function init(){
		$um = model('model_users');
		$this->user = $um->getloggeduser();
	}
		
	function index(){
		//生成decsrf
		$c = load::classes('lib.util.captcha',TEA_PATH);
		$c->setlength(32);
		$this->csrftoken = $c->string();
		$this->display('index');
	}
	
	function login(){
		$c = load::classes('lib.util.captcha',TEA_PATH);
		if(!$c->valid($_REQUEST['csrftoken'],false)){
			$this->jsonmsg('安全验证字符串已过期，请刷新网页后再试！');
		}
		$phone = $_POST['phone'];
		if(!$this->isMobile($phone)){
			$this->jsonmsg('手机号码格式有误，请输入11位中国手机号码。');
		}
		$um = model('model_users');
		$userid = $um->userexist($phone);
		if($userid){
			//老用户是否有邮件，如果无邮件，随便进
			$malb = model('user_mail');
			$albcount = $malb->count(array('userid'=>$userid));
			if($albcount > 0){
				//有邮件的老用户验证短信之后登录
				$_SESSION['verifyphone'] = $phone;
				$this->sendsmscode();
			}
			$um->userupdate(array('lastip'=>getip()),$userid);
		}else{
			//不存在
			unset($um);
			$um = model('model_users');
			//判断ip是不是短时间内进行过操作
			//if(!$um->isipsafe(getip())){
			//	$_SESSION['verifyphone'] = $phone;
			//	$this->jsonmsg(makeurl('main','verify'),1);
			//}
			//插入新用户
			$userid = $um->userinsert(array('phone'=>$phone));
		}
		//登录
		unset($um);
		$um = model('model_users');
		$um->userlogin($userid);
		//销毁csrftoken
		unset($_SESSION['seccode']);
		
		//登录成功，进入管理界面
		$this->jsonmsg(makeurl('dashboard','index'),2);
		exit;
	}
	
	function sendsmscode(){
		//每个session只发两遍
    	if(isset($_SESSION['smscount'])){
    		$_SESSION['smscount'] = $_SESSION['smscount']+1;
    		if($_SESSION['smscount'] > 2){
    			$this->jsonmsg("验证码已经发送到你填写的号码",1);
    		}
    	}else{
    		$_SESSION['smscount'] = 1;
    	}
		$sms = load::classes('sms');
		$ret = $sms->sendcode($_SESSION['verifyphone']);
        if($ret->code == '200'){
        	$this->jsonmsg("验证码已经发送到你填写的号码",1);
        }else{
        	$this->jsonmsg($ret->body->error);
        }
	}
	
	function checksmscode(){
		//检查手机验证码是否正确		
		$sms = load::classes('sms');
		$ret = $sms->checkcode($_SESSION['verifyphone'],$_REQUEST['code']);
		if(!$ret){
        	$this->jsonmsg('手机验证码不正确，请核实后重新输入');
		}
		//验证成功，登录
		$um = model('model_users');
		$userid = $um->userexist($_SESSION['verifyphone']);
		$um->userupdate(array('lastip'=>getip()),$userid);
		unset($um);
		$um = model('model_users');
		$um->userlogin($userid);
		
		//登录成功，进入管理界面
		$this->jsonmsg(makeurl('dashboard','index'),2);
		exit;
	}
	
	
	function isMobile($mobile) {
	    if (!is_numeric($mobile)) {
	        return false;
	    }
	    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,1,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
	}
	
	function jsonmsg($msg,$code = 0){
		exit(json_encode(array('code'=>$code,'msg'=>''.$msg.'')));
	}
	
	function logout(){
		$mu = model('model_users');
		$mu->userlogout();
		redirect(makeurl('main'));
	}
	
}