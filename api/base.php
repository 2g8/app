<?php
class base extends controller {
    function init(){
        //全局变量
        //0.1   是否API请求频率超限制（IP）
        apiRateLimit('ip-'.getip(),100);
        //0.2   是否API请求频率超限制（UA）
        //apiRateLimit('ua-'.$_SERVER['HTTP_USER_AGENT'],1000); //如果是php/go sdk，客户端可能是一样的
        //1.1   获取app id
        $app = $this->tea->api->get_app_id();
        if($app){
            $this->appid = $app['appid'];
        }else{
            apiResponse([],1000,403);
        }
        //1.2   是否API请求频率超限制（appid）
        apiRateLimit('app-id-'.$this->appid,100,10);
        //1.3   判断 app 是否存在
        $this->appinfo = get_app($this->appid);
        if(!$this->appinfo){  //app不存在
            apiResponse([],1002,403);
        }
        //2.1   获取api key
        $app = $this->tea->api->get_api_key();
        if($app){
            $this->apikey = $app['apikey'];
        }else{
            apiResponse([],1001,403);
        }
        //2.2   api key格式检查
        if(!check_apikey_format($this->apikey)) apiResponse([],1003,403);
        //2.3   是否API请求频率超限制（api key）
        apiRateLimit('api-key-'.$this->apikey,100,10);
        //2.4   判断api key是否有效
        $apikeyinfo = get_apikey($this->appid,$this->apikey);
        if(!$apikeyinfo) apiResponse([],1004,403); //该apikey不存在

        //3.0   开始user auth
        $this->_auth();
        //4.0   预处理data
        $this->_data();


    }

    function _auth(){
        $no_auth_routes = [
            'user/auth_with_password',
            'user/auth_with_oauth',
            'user/auth_with_oauth_callback',
            'user/auth_refresh',
            'user/create',
        ];
        //判断是否为无需认证的action
        $cap = $this->tea->uri->cap;
        $ca = $cap['controller'].'/'.$cap['action'];

        if(!in_array($ca, $no_auth_routes)){    //不在无需认证的里面，获取auth
            // 3.1 获取auth
            $auth = $this->tea->api->get_auth();
            if($auth){
                $this->authtoken = $auth['token'];
            }else{
                apiResponse([],1005,403);
            }
            //jwt decode一下
            $jwt_payload = jwt_decode($this->authtoken);
            if(!$jwt_payload) apiResponse([],1006,403);
            //判断用户是否存在
            if(!isset($jwt_payload['userid'])) apiResponse([],1006,403);
            $this->userinfo = get_userinfo($jwt_payload['userid'],$this->appid);
            //判断session是否存在
            //判断用户是否有权限
        }
    }

    function _data(){
        $this->conditions = [];
        $rdata = $this->tea->api->get_data();
        $conditions['page'] = isset($rdata['page']) ? intval($rdata['page']) : 1;
        $conditions['pagesize'] = isset($rdata['perpage']) ? intval($rdata['perpage']) : 1;
        if(isset($rdata['sort'])){
            $asc_char = substr($rdata['sort'],0,1);
            if($asc_char == '+'){
                $asc = ' asc';
            }else{
                $asc = ' desc'; //默认
            }
            $asc_column = substr($rdata['sort'],1);
            $conditions['order'] = $asc_column.$asc;
        }
        $this->conditions = $conditions;
    }

}