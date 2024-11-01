<?php
include_once 'base.php';
class apps extends base {
    function init(){
        parent::init();
    }

    function index(){
        $request_method = $this->tea->api->request_method;
        //$rdata = $this->tea->api->get_data();
        switch ($request_method) {
            //获取当前用户的全部app
            case 'GET':
            default:
                $ma = new model_applications();
                $cond = $this->conditions;
                $cond['user_id'] = $this->userinfo['user_id'];
                $cond['is_delete'] = 0;
                $apps = $ma->gets($cond,$pager);
                array_walk($apps, function (&$item){unset($item['is_delete']);unset($item['user_id']);});
                $ret['items'] = $apps;
                $ret['page'] = $cond['page'];
                $ret['perpage'] = $pager->pagesize;
                $ret['totalItems'] = $pager->total;
                $ret['totalPages'] = $pager->totalpages;

                apiResponse($ret);
                break;
        }
    }

    function info(){
        //TODO:如果提交过来app_id，判断app_id是否归auth所有，再返回info
        //默认直接返回当前app_id的info
        $info['app_name'] = $this->appinfo['app_name'];
        $info['app_url'] = $this->appinfo['app_url'];
        $info['hideControls'] = false;
        apiResponse($info);
    }


}