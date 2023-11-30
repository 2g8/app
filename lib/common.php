<?php
//分布式发布服务，按用户名
function shared_pub_server($iguser){
    $server_ids = array(1,);
    $sid_index = hexdec(bin2hex(substr($iguser,-3,3))) % count($server_ids);
    return $server_ids[$sid_index];
}

//调试日志函数
function msg($msg){
    echo $msg.chr(13).chr(10).chr(13);
    filelog($msg);
}

function filemsg($msg){
    echo $msg.chr(13).chr(10).chr(13);
    filelog($msg);
}

function retmsg($msg, $is_error = 0){
    return ['is_error'=>$is_error,'msg'=>''.$msg.''];
}

function jsonmsg($msg,$is_error = 0){
    exit(json_encode(['is_error'=>$is_error,'msg'=>''.$msg.'']));
}

function jsondata($data, $is_error = 0){
    $ret['is_error'] = $is_error;
    if(isArray($data)){
        $ret['data'] = $data;
    }
    exit(json_encode($ret));
}

function jsonret($ret, $is_error = 0){
    $ret['is_error'] = $is_error;
    exit(json_encode($ret));
}

function botmsg($msg){
    $webhookurl = 'https://open.feishu.cn/open-apis/bot/v2/hook/cee5db99-dc1a-451f-a009-a9bbcbe1facb';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhookurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{"msg_type":"text","content":{"text":"'.$msg.'"}}');
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        filelog('botmsg : Exception!! URI:'.$webhookurl.' , Error: '.curl_error($ch));
    }
    curl_close($ch);
}

function mb_pathinfo($filepath) {
    preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im',$filepath,$m);
    if($m[1]) $ret['dirname']=$m[1];
    if($m[2]) $ret['basename']=$m[2];
    if($m[5]) $ret['extension']=$m[5];
    if($m[3]) $ret['filename']=$m[3];
    return $ret;
}

function bigid62($strnum,$isdecode = false){
    $id62obj = load::classes('lib.id62',APP_PATH,'LkEhDuyXTViMrcIPNCGQq5msHz2KweafnJS4vYoj7gWbORtF830pA6dZUlBx19');
    if($isdecode){
        return $id62obj->decode($strnum);
    }else{
        return $id62obj->encode($strnum);
    }
}

function id62($strnum,$isdecode = false){
    $id62obj = load::classes('lib.id62',APP_PATH,'LkEhDuyXTViMrcIPNCGQq5msHz2KweafnJS4vYoj7gWbORtF830pA6dZUlBx19');
    if($isdecode){
        $thestr = $id62obj->decode($strnum);
        $thenum = $thestr % 10000000;
        return $thenum;
    }else{
        $pre = $strnum%10;
        $pre = $pre * 10000000;
        $strnum = $pre + $strnum;
        return $id62obj->encode($strnum);
    }
}


function randbg(){
    $colors = ['ui-03','ui-04','gray-300','gray-600','indigo','purple','pink','orange','teal','lightblue','facebook','twitter','primary-light','success-light','warning-light','pink-light','indigo-light'];
    $bg = $colors[array_rand($colors,1)];
    return 'bg-'.$bg;
}

function isDate($date){
    if(date('Y-m-d',strtotime($date)) == $date){
        return true;
    }else{
        return false;
    }
}

function isArray($val){
    if(!empty($val) && is_array($val)){
        return true;
    }else{
        return false;
    }
}

/**
 * 验证手机号是否正确
 * 移动：134、135、136、137、138、139、150、151、152、157、158、159、182、183、184、187、188、178(4G)、147(上网卡)、148、172、198；
 * 联通：130、131、132、155、156、185、186、176(4G)、145(上网卡)；146、166、171、175
 * 电信：133、153、180、181、189 、177(4G)；149、173、174、199
 * 卫星通信：1349
 * 虚拟运营商：170
 * http://www.cnblogs.com/zengxiangzhan/p/phone.html
 * @author lan
 * @param $mobile
 * @return bool
 */
function isMobile($mobile='') {
    return preg_match('#^13[\d]{9}$|^14[5,6,7,8,9]{1}\d{8}$|^15[^4]{1}\d{8}$|^16[6]{1}\d{8}$|^17[0,1,2,3,4,5,6,7,8]{1}\d{8}$|^18[\d]{9}$|^19[8,9]{1}\d{8}$#', $mobile) ? true : false;
}

/**
 * 验证密码是否正确
 * 密码由6-16位大小写字母、数字和下划线组成
 * @author lan
 * @param string $password
 * @return bool
 */
function isPassword($password=''){
    return preg_match("/^[0-9a-zA-Z_]{6,16}$/", $password) ? true : false;
}

/**
 * 验证邮箱是否正确
 * @author lan
 * @param string $email
 * @return bool
 */
function isEmail($email=''){
    return preg_match("/^[0-9a-z][_.0-9a-z-]{0,31}@([0-9a-z][0-9a-z-]{0,30}[0-9a-z]\.){1,4}[a-z]{2,15}$/i", $email) ? true : false;
}

/**
 * 验证用户名是否正确
 * 用户名由6-24位字母、数字组成，首位不能是数字
 * @param string $username
 * @return bool
 */
function isUserName($username=''){
    return preg_match("/^[a-zA-Z]{1}[0-9a-zA-Z]{5,23}$/", $username) ? true : false;
}

/**
 * 验证身份证号码格式是否正确
 * 仅支持二代身份证
 * @author chiopin
 * @param string $idcard 身份证号码
 * @return boolean
 */
function isIdCard($idcard=''){
    // 只能是18位
    if(strlen($idcard)!=18){
        return false;
    }

    $vCity = array(
        '11','12','13','14','15','21','22',
        '23','31','32','33','34','35','36',
        '37','41','42','43','44','45','46',
        '50','51','52','53','54','61','62',
        '63','64','65','71','81','82','91'
    );

    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $idcard)) return false;

    if (!in_array(substr($idcard, 0, 2), $vCity)) return false;

    // 取出本体码
    $idcard_base = substr($idcard, 0, 17);

    // 取出校验码
    $verify_code = substr($idcard, 17, 1);

    // 加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

    // 校验码对应值
    $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

    // 根据前17位计算校验码
    $total = 0;
    for($i=0; $i<17; $i++){
        $total += substr($idcard_base, $i, 1)*$factor[$i];
    }

    // 取模
    $mod = $total % 11;

    // 比较校验码
    if($verify_code == $verify_code_list[$mod]){
        return true;
    }else{
        return false;
    }
}

function arrayToKV($arr, $keyColumn = 'id', $valColumn = 'val'){
    $result = false;
    if(!$keyColumn) $keyColumn = 'id';
    if(!$valColumn) $valColumn = 'val';
    if(isArray($arr)){
        foreach ($arr as $r){
            $result[$r[$keyColumn]] = $r[$valColumn];
        }
    }
    return $result;
}

function arrayToInString($arr, $isInt = true){
    if($isInt){
        return implode(',',$arr);
    }else{
        return "'".implode("','",$arr)."'";
    }
}

function shmcache($key, $val = null, $expire = 3600) {
    static $_shm = null;
    if ( null === $_shm ) $_shm = @shm_attach(crc32(APP_PATH), 2048, 0755);
    if (($time = time()) && ($k = crc32($key)) && $val && $expire){
        shm_put_var($_shm, $k, array($time + $expire, $val));
        return $val;
    }
    return shm_has_var($_shm, $k) && ($data = shm_get_var($_shm, $k)) && $data[0] > $time ? $data[1] : null;
}

function genfakeip(){
    $ip_long = array(
        array('607649792', '608174079'), //36.56.0.0-36.63.255.255
        array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255
        array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255
        array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255
    );
    $rand_key = mt_rand(0, 9);
    return long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
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


function getBrowserFingerprint() {
    $data = '';
    //$data .= getip(); //去掉IP地址
    $data .= $_SERVER['HTTP_USER_AGENT'] ?? '';
    $data .= $_SERVER['HTTP_ACCEPT'] ?? '';
    $data .= $_SERVER['HTTP_ACCEPT_CHARSET'] ?? '';
    $data .= $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '';
    $data .= $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    /* Apply MD5 hash to the browser fingerprint */
    $hash = md5($data);
    return $hash;
}

//为了设置长期session, 自定义session_name和session_id
session_name('visitor_id');