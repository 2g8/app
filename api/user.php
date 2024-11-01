<?php
include_once 'base.php';
class user extends base {
    function init(){
        parent::init();
    }

    function auth_with_password(){
        $requestdata = $this->tea->api->get_data();
        if(!isArray($requestdata)) apiResponse([],2001,403);
        if(empty($requestdata['identity'])) apiResponse([],2001,403);
        if(empty($requestdata['password'])) apiResponse([],2002,403);
        if(isEmail($requestdata['identity'])){
            $cond['email'] = $requestdata['identity'];
        }else{
            $cond['username'] = $requestdata['identity'];
        }
        $cond['password'] = $requestdata['password'];
        $mu = new model_users();
        $user = $mu->exist($cond);
        if(!$user) apiResponse([],2003,403);
        $autheduser['user_id'] = $user['user_id'];
        $autheduser['create_time'] = $user['create_time'];
        $autheduser['update_time'] = $user['update_time'];
        $autheduser['avatar'] = $user['avatar'];
        $autheduser['email'] = $user['email'];
        $token = jwt_encode(['userid'=>$user['user_id']]);
        apiResponse(['user'=>$autheduser,'token'=>$token]);
    }

    function auth_with_oauth(){

    }

    function me(){      // GET/PATCH

    }

    function profile(){ // GET/PUT/PATCH/DELETE

    }


}