<?php
set_time_limit(0);

class database extends command {
    //默认为全部表格
    private $tablenames = 'all';
    public $db,$db_apt;

    function init(){
        //如果进行关于数据的操作,需要先初始化,因为配置文件可能没有引入数据库连接类.
        global $tea;
        $tea->init(array('db','model'));
        $this->db = $tea->db;
        load::file('lib.db.db_apt',TEA_PATH);
        $this->db_apt = new db_apt($tea->db);

        $this->tablenames = $this->dealtablenames();
    }

    function dealtablenames(){
        //接收参数
        if(isArray(CMDARGS)){
            list($tablenames) = CMDARGS;
            //$tablenames = CMDARGS[0];

            if(strpos($tablenames,',') > 0){
                $tablenames = explode(',',$tablenames);
            }else{
                if(strtolower($tablenames) !== 'all'){
                    return [trim($tablenames)];
                }else{
                    return $this->getalltablenames();
                }
            }

            $tablenames = $this->filterTableNameWhitelist($tablenames);
            if(isArray($tablenames)) return $tablenames;
        }else{
            return $this->getalltablenames();
        }
        return false;
    }

    function dump(){
        if(isArray($this->tablenames)){
            foreach ($this->tablenames as $tbname){
                $this->dumpTable($tbname);
            }
        }else{
            msg('Table Names is Empty!');
        }
    }

    //根据 sql 文件 创建数据库
    function restore(){
        if(isArray($this->tablenames)){
            foreach ($this->tablenames as $tbname){
                $this->restoreTable($tbname);
            }
        }else{
            msg('Table Names is Empty!');
        }
    }

    //根据 sql 文件 创建数据库
    function drop(){
        if(isArray($this->tablenames)){
            foreach ($this->tablenames as $tbname){
                $this->dropTable($tbname);
            }
        }else{
            msg('Table Names is Empty!');
        }
    }

    //根据数据库创建 sql 文件
    function dumptables(){
        if(isArray($this->tablenames)){
            $mysql_host = $this->tea->conf->db['host'];
            $mysql_dbname = $this->tea->conf->db['dbname'];
            $mysql_vsersion = $this->getMysqlVersion();
            $mysql_date = date('Y-m-d H:i:s');

            $table_sql = '--  Generator: Teaphp Dump Generator <ding@gong.si>'.PHP_EOL;
            $table_sql .= '--'.PHP_EOL;
            $table_sql .= '-- Host: '.$mysql_host.'    Database: '.$mysql_dbname.PHP_EOL;
            $table_sql .= '-- ------------------------------------------------------'.PHP_EOL;
            $table_sql .= '-- Server version	'.$mysql_vsersion.PHP_EOL;
            $table_sql .= '-- Date: '.$mysql_date.PHP_EOL.PHP_EOL.PHP_EOL;

            foreach ($this->tablenames as $tbname){
                $ret = $this->showCreateTable($tbname);
                $ret = preg_replace('/AUTO_INCREMENT=(\d+) /i', '', $ret);
                $table_sql .= '-- '.PHP_EOL;
                $table_sql .= '--  Structure for table `'.$tbname.'`'.PHP_EOL;
                $table_sql .= '-- '.PHP_EOL.PHP_EOL;
                if($ret) $table_sql .= $ret.';'.PHP_EOL.PHP_EOL.PHP_EOL;
            }

            $backup_path = APP_PATH."/data/sqldata/";
            if (!is_dir($backup_path)) makedir($backup_path);

            $backup_file = $backup_path.'tables.sql';
            if(file_exists($backup_file)) @unlink($backup_file); //保证只有一份
            file_put_contents($backup_file, $table_sql);
        }else{
            msg('Table Names is Empty!');
        }
    }

    function dropTable($tablename){
        $ret = $this->db->query('DROP TABLE IF EXISTS `'.$tablename.'`;');
        if($ret) msg('Table "'.$tablename.'" droped!');
        return $ret;
    }

    function dumpTable($tablename){
        // Use MySQLDump - PHP
        // Url: https://github.com/ifsnop/mysqldump-php
        try {
            $dump = new Ifsnop\Mysqldump\Mysqldump('mysql:host='.$this->tea->conf->db['host'].';dbname='.$this->tea->conf->db['dbname'], $this->tea->conf->db['dbuser'], $this->tea->conf->db['dbpass'],['add-drop-table' => true,'default-character-set'=>$this->tea->conf->db['charset'],'include-tables' => [$tablename]]);
            $dump->start(APP_PATH."/data/sqldata/".$tablename.'.sql');
        } catch (\Exception $e) {
            echo 'mysqldump-php error: ' . $e->getMessage();
        }
    }

    function restoreTable($tablename){
        // Use MySQLDump - PHP
        // Url: https://github.com/ifsnop/mysqldump-php
        try {
            $filepath = APP_PATH."/data/sqldata/".$tablename.'.sql';
            if(file_exists($filepath)) {
                $dump = new Ifsnop\Mysqldump\Mysqldump('mysql:host=' . $this->tea->conf->db['host'] . ';dbname=' . $this->tea->conf->db['dbname'], $this->tea->conf->db['dbuser'], $this->tea->conf->db['dbpass'], ['default-character-set' => $this->tea->conf->db['charset']]);
                $dump->restore(APP_PATH . "/data/sqldata/" . $tablename . '.sql');
                msg('Table "'.$tablename.'" restored.');
            }else{
                msg('File doesn\'t Exist!');
            }
        } catch (\Exception $e) {
            echo 'mysqldump-php error: ' . $e->getMessage();
        }
    }

    function filterTableNameWhitelist($tablenames){
        //判断表名在数据库中是否存在, 不存在就提示一下丢弃.
        $alltablenames = $this->getalltablenames();
        if(isArray($tablenames)){
            foreach ($tablenames as $key=>$tbname){
                if(!in_array($tbname, $alltablenames)){
                    msg('Table "'.$tbname.'" not exist, ignored.');
                    unset($tablenames[$key]);
                }
            }
            return array_values($tablenames);
        }
    }

    function getalltablenames(){
        $db_name = $this->tea->conf->db['dbname'];
        $res = $this->db->query('SELECT table_name FROM information_schema.tables WHERE TABLE_SCHEMA = \''.$db_name.'\'');
        $tablenames = $res->fetchall();
        if(isArray($tablenames)){
            foreach ($tablenames as $tbname){
                $alltablenames[] = $tbname['table_name'];
            }
            return $alltablenames;
        }
        return false;
    }


    function showCreateTable($tablename){
        $res = $this->db->query('SHOW CREATE TABLE `'.$tablename.'`');
        $ret = $res->fetchall();

        if(isArray($ret)){
            msg('Table "'.$tablename.'" create table sql dumped!');
            return $ret[0]["Create Table"];
        }else{
            msg('Table "'.$tablename.'" not exist, Please create table first.');
            return false;
        }
    }

    function getMysqlVersion(){
        $res = $this->db->query('SELECT version() as version');
        $ret = $res->fetchall();

        if(isArray($ret)){
            return $ret[0]["version"];
        }else{
            return 0;
        }
    }


}