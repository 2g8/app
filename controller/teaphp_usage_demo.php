<?php
class main extends controller {
    function init(){
        load::file('xxtea');
    }
    function index(){
        $user = $_SESSION['user'];
        $user['role'] = $_SESSION['role'];
        $user['rolename'] = $this->tea->conf->acl['usertypename'][$user['usertype']];
        $this->user = $user;

        $fc = load::classes('FileCache');	//加缓存
        $lists = $fc->get('guizhen.douban.tops');
        if(!$lists){
            //取推荐位的数据
            $tm = model('douban_pics');
            $gets['select'] = '*';
            $gets['page'] = 1;
            $gets['pagesize'] = 12;
            $gets['status'] = 2;
            $gets['order'] = 'likes desc';
            $gets['fastpaging'] = true;
            $lists = $tm->gets($gets,$pager);
            $fc->set('guizhen.douban.tops',$lists);
        }

        $topskey = array_rand($lists, 4);
        foreach ($topskey as $key){
            $toplists[]=$lists[$key];
        }

        //-------------------------------------- 人气妹纸 start--------------------------------------------
        $um = model('douban_users');
        $sql = 'select * from douban_users where 1=1 order by loves desc limit 0,4';
        $res = $um->db->query($sql);
        $hotmms = $res->fetchall();
        $this->hotmms = $hotmms;

        $this->tops = $toplists;

        $tm = model('douban_pics');
        $gets['select'] = '*';
        $page = intval(id62($_REQUEST['page'],1));
        $gets['page'] = empty($page) ? 1 : $page;
        $gets['pagesize'] = 12;
        $gets['leftjoin'] = array('douban_topics','douban_pics.tid=douban_topics.tid');
        if(!empty($_REQUEST['date'])){
            $thedate = ($_REQUEST['date'].' 23:59:59');
            $gets['creation_date']['<='] = $thedate;
        }
        $gets['status'] = 2;
        $gets['hide'] = 0;
        $gets['order'] = 'creation_date desc';
        $gets['fastpaging'] = true;
        $lists = $tm->gets($gets,$pager);
        $this->pager = $pager->render();
        foreach ($lists as $list){
            $pids[] = $list['pid'];
        }
        if(!empty($this->user['uid'])){
            $tl = model('douban_likes');
            $cond['in'] = array('pid',implode(',',$pids));
            $cond['where']['userid'] = $this->user['uid'];
            $likes = $tl->gets($cond);
            if(!empty($likes) && is_array($likes)){
                foreach ($likes as $like){
                    $ll[$like['pid']] = $like['pid'];
                }
            }
        }
        $this->likes = $ll;
        $this->lists = $lists;
        $this->display('douban_index');

    }
