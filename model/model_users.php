<?php
//author    : ding@gong.si
//created   : 2015/6/16 10:31
class model_users extends model {
    public $table = 'users';	//数据表名
    public $pk = 'userid';		//数据表的主键
    public $cloumns = array('lastlogin','lastip','regdate','regip','phone'); //字段

    function in($data){
        $key = $val = '';
        $data['lastlogin'] = date('Y-m-d H:i:s');
        $data['lastip'] = getip();
        $data['regdate'] = date('Y-m-d H:i:s');
        $data['regip'] = getip();
        //入库
        foreach($data as $k=>$v){
            if(in_array($k,$this->cloumns)){
                $key .= $k.',';
                $val .= "'".$v."',";
            }
        }
        $key = substr($key,0,-1);
        $val = substr($val,0,-1);
        return $this->db->query('INSERT INTO '.$this->table."($key) VALUES ($val)");
    }

    function up($data,$id){
        $upstr = '';
        $data['lastlogin'] = date('Y-m-d H:i:s');
        $data['lastip'] = getip();
        foreach($data as $k=>$v){
            if(in_array($k,$this->cloumns)){
                $upstr .= $k."='".$v."',";
            }
        }
        $upstr = substr($upstr,0,-1);
        return $this->db->query('UPDATE '.$this->table." SET ".$upstr." WHERE ".$this->pk."=".$id);
    }

    function exist($cond){
        $ret = $this->gets($cond);
        if(!empty($ret)){
            return $ret;
        }else{
            return false;
        }
    }

    function getloggeduser(){
        if(empty($_SESSION['user'])){
            if(!empty($_COOKIE['userauth'])){
                $cinfo = $this->decrypt_auth($_COOKIE['userauth']);
                if($cinfo['ip_right'] == true){
                    $this->userlogin($cinfo['userid'],'',false);
                }else{
                    $this->userlogin($cinfo['userid'],$cinfo['phone'],false);
                }
            }
        }
        return $_SESSION['user'] ?? '';
    }

    function userlogout(){
        unset($_SESSION['user']);
        //设置cookie
        $this->set_cookie('userauth', '', time() + 86400 * 30, '/', TRUE);
    }

    function userlogin($userid,$phone='',$setcookie = true){
        $cond['userid'] = intval($userid);
        if(!empty($phone)){
            $cond['phone'] = $phone;
        }
        $users = $this->gets($cond);
        $this->user = $users[0];
        if(!empty($this->user)){
            //设置session
            $_SESSION['user'] = $this->user;
            //更新记录
            $this->up([],$this->user['userid']);
            //设置cookie
            if($setcookie){
                $this->set_login_cookie($this->user);
            }
        }else{
            return false;
        }
    }

    //设置usercookie
    function set_login_cookie($user) {
        $user_auth = $this->get_user_auth($user);
        $this->set_cookie('userauth', $user_auth, time() + 86400 * 30, '/', TRUE);
    }

    //加密cookie验证
    function get_user_auth($user) {
        if(empty($user)) {
            return '';
        }
        $user_auth = $this->encrypt_auth($user['userid'], $user['phone']);
        return $user_auth;
    }

    //加密方法
    function encrypt_auth($uid, $phone) {
        $key = md5("thisisaverylongkey");	//加解密的key要一致！
        $time = time();
        $ip = getip();

        // 所有项目中，不允许有|，否则可能会被伪造
        include_once(APP_PATH.'/lib/xxtea.func.php');
        $user_auth = encrypt("$uid|$phone|$ip|$time", $key);
        return $user_auth;
    }

    //解密方法
    function decrypt_auth($user_auth) {
        $key = md5("thisisaverylongkey");

        include_once(APP_PATH.'/lib/xxtea.func.php');
        $s = decrypt($user_auth, $key);
        $return =  array('userid'=>0, 'phone'=>'', 'ip_right'=>FALSE);
        if(!$s) {
            return $return;
        }
        $arr = explode("|", $s);
        if(count($arr) < 4) {
            return $return;
        }
        $return = array (
            'userid'=>intval($arr[0]),
            'phone'=>$arr[1],
            'ip_right'=>(getip() == $arr[2]),
        );
        return $return;
    }

    function set_cookie($key, $value, $time = 0, $path = '', $httponly = FALSE) {
        if($value != NULL) {
            if(version_compare(PHP_VERSION, '5.2.0') >= 0) {
                setcookie($key, $value, $time, $path, '', FALSE, $httponly);
            } else {
                setcookie($key, $value, $time, $path, '', FALSE);
            }
        } else {
            if(version_compare(PHP_VERSION, '5.2.0') >= 0) {
                setcookie($key, '', $time, $path, '', FALSE, $httponly);
            } else {
                setcookie($key, '', $time, $path, '', FALSE);
            }
        }
    }


}