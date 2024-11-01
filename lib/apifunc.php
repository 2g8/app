<?php

define('API_JWT_SECRET_KEY', 'this_is_a_very_long_secret_for_upswing');
define('API_JWT_ALG', 'HS256');

//API权限类
function get_app($appid){
    //TODO : 加缓存
    $ma = new model_applications();
    return $ma->get($appid)->get();
}

function get_userinfo($userid,$appid){
    //TODO : 加缓存
    $cond['user_id'] = $userid;
    $cond['app_id'] = $appid;
    $mu = new model_users();
    return $mu->exist($cond);
}
function get_apikey($appid, $apikey = ''){
    //TODO : 加缓存
    $ma = new model_apikeys();
    $cond['app_id'] = $appid;
    if(!empty($apikey)) $cond['apikey'] = $apikey;
    return $ma->gets($cond);
}

//API通用函数

// API限流函数
// 是否超过频率限制
function isRateLimit($key, $limit = 20, $seconds = 60){
    global $apiThrottle;
    if(!isset($apiThrottle)){
        $apiThrottle = new Stiphle\Throttle\LeakyBucket(); // 桶，或者 时间窗口 \TimeWindow;
        $storage = new \Stiphle\Storage\Apcu();
        $apiThrottle->setStorage($storage);
    }
    return $apiThrottle->throttle($key, $limit, $seconds * 1000) > 0 ? true: false;
}
// 默认60秒钟20次
function apiRateLimit($key, $limit = 20, $seconds = 60){
    if(isRateLimit($key, $limit, $seconds)) apiResponse([],429,429);
}

// API输出函数
function apiResponse($data = [], $code = 1, $http_status = 200){
    $apiCodeTexts = [
        1 => 'success',
        404 => 'not found',
        429 => 'too many requests',
        500 => 'internal server error',
        //API网关类
        1000 => 'must provide an x-app-id in header/query/cookie',
        1001 => 'must provide an x-api-key in header/query/cookie',
        1002 => 'wrong x-app-id',
        1003 => 'wrong x-api-key format',
        1004 => 'wrong x-api-key',
        1005 => 'must provide an auth token(Token or Bearer) in header',
        1006 => 'wrong auth token',
        1007 => 'user doesn\'t exist',
        //user相关
        2001 => 'identity(email or username) is empty',
        2002 => 'password is empty',
        2003 => 'identity(email or username) doesn\'t exist or password doesn\'t match',
    ];
    try {
        $result['code'] = $code;
        $result['message'] = $apiCodeTexts[$code];
        if(!empty($data)){
            $result['data'] = $data;
        }
        $response = new Symfony\Component\HttpFoundation\JsonResponse($result, $http_status);
    } catch (\TypeError $e){
        //debug::error('Response Error',$e->getMessage());
        //内部错误，返回500
        $response = new Symfony\Component\HttpFoundation\JsonResponse(['code'=>500,'message'=>$apiCodeTexts[500]], 500);
    }
    $response->headers->set('Server', 'Upswing Backend Server');
    $response->send();
    exit;
}

//API id生成类
function gen_id(){
    $snowflake = new \Godruoyi\Snowflake\Snowflake;
    return $snowflake->setSequenceResolver(function ($currentMillisecond) {
        static $lastTime;
        static $sequence;

        if ($lastTime == $currentMillisecond) {
            ++$sequence;
        } else {
            $sequence = 0;
        }

        $lastTime = $currentMillisecond;

        return $sequence;
    })->id();
}

//校验token格式
function check_apikey_format($token){
    $tokenstr = str_replace('-', '', $token);
    $idstr = substr($tokenstr, 0, 24);
    $signstr = substr($tokenstr, 24, 8);
    $checkstr = substr(md5($idstr.'upswing'),0,8);
    if($checkstr == $signstr){
        return true;
    }
    return false;
}

//生成token
function gen_apikey(){
    load::file('lib.uuid');
    $sn = uuid::v4();
    $idstr = str_replace('-', '', $sn);
    $idstr = substr($idstr,0,24);
    $signstr = md5($idstr.'upswing');
    $idstr = substr($idstr,0,8)."-".substr($idstr,8,4)."-".substr($idstr,12,4)."-".substr($idstr,16,8).substr($signstr,0,8);
    return strtolower($idstr);
}


//JWT系列函数
function jwt_encode($payload){
    $jwt = new \SocialConnect\JWX\JWT($payload);
    $encodeOptions = new \SocialConnect\JWX\EncodeOptions();
    $encodeOptions->setExpirationTime(600);
    return $jwt->encode(API_JWT_SECRET_KEY, API_JWT_ALG, $encodeOptions);
}

function jwt_decode($token){
    $decodeOptions = new \SocialConnect\JWX\DecodeOptions([API_JWT_ALG]);
    try {
        $token = \SocialConnect\JWX\JWT::decode($token,API_JWT_SECRET_KEY, $decodeOptions);
        return $token->getPayload();
    } catch (SocialConnect\JWX\Exception\RuntimeException $e){
        //debug::error('JWT Error',$e->getMessage());
        return false;
    }
}


