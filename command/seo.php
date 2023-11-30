<?php
set_time_limit(0);

class seo extends command {
    //默认为全部表格
    private $tablenames = 'all';
    public $db,$db_apt;

    function init(){
        //如果进行关于数据的操作,需要先初始化,因为配置文件可能没有引入数据库连接类.
        global $tea;
        $tea->init(['db','model']);
        $this->db = $tea->db;
        load::file('lib.db.db_apt',TEA_PATH);
        $this->db_apt = new db_apt($tea->db);
    }

    function sitemap(){
        seo_sitemap();
        msg('sitemap created!');
    }
    function ping(){
        seo_ping();
        msg('pinged search engine!');
    }

}