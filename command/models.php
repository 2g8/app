<?php
set_time_limit(0);

class models extends command {
    //默认为全部表格
    private $tablenames = 'all';

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
        //接受参数
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

            if(isArray($tablenames)) return $tablenames;
        }else{
            return $this->getalltablenames();
        }
        return false;
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

    //根据数据库创建 model 文件
    function create(){
        if(isArray($this->tablenames)){
            foreach ($this->tablenames as $tbname){
                $this->create_model($tbname);
            }
        }else{
            msg('Table Names is Empty!');
        }
    }

    //根据数据库更新 model 文件
    function update(){
        if(isArray($this->tablenames)){
            foreach ($this->tablenames as $tbname){
                $this->update_model($tbname);
            }
        }else{
            msg('Table Names is Empty!');
        }
    }

    function create_model($tablename){
        $db_name = $this->tea->conf->db['dbname'];
        $res = $this->db->query('select column_name,column_key from information_schema.columns where TABLE_SCHEMA = \''.$db_name.'\' and TABLE_NAME = \''.$tablename.'\'');
        $columns = $res->fetchall();
        if(isArray($columns)){
            $str_cols = '';
            foreach ($columns as $column){
                if(strtolower($column['column_key']) == 'pri'){
                    $pri_key = $column['column_name'];
                }
                $str_cols .= '\''.$column['column_name'].'\',';
            }
            $str_cols = substr($str_cols,0,-1);
            $datenow = date('Y-m-d H:i');
            $content = base64_decode('PD9waHAKLy9hdXRob3IgICAgOiBUZWFwaHAgTW9kZWwgR2VuZXJhdG9yIDxkaW5nQGdvbmcuc2k+Ci8vY3JlYXRlZCAgIDogeyRkYXRlbm93fQpjbGFzcyBtb2RlbF97JHRhYmxlbmFtZX0gZXh0ZW5kcyBtb2RlbCB7CiAgICBwdWJsaWMgJHRhYmxlID0gJ3skdGFibGVuYW1lfSc7CS8v5pWw5o2u6KGo5ZCNCiAgICBwdWJsaWMgJHBrID0gJ3skcHJpX2tleX0nOwkJLy/mlbDmja7ooajnmoTkuLvplK4KICAgIHB1YmxpYyAkY2xvdW1ucyA9IFt7JHN0cl9jb2xzfV07IC8v5a2X5q61CgogICAgZnVuY3Rpb24gaW4oJGRhdGEpewogICAgICAgIC8vJGRhdGFbJ2NyZWF0ZV90aW1lJ10gPSBkYXRlKCdZLW0tZCBIOmk6cycpOwogICAgICAgIC8vJGRhdGFbJ3VwZGF0ZV90aW1lJ10gPSBkYXRlKCdZLW0tZCBIOmk6cycpOwogICAgICAgIC8vJGRhdGFbJ2NsaWVudF9pcCddID0gZ2V0aXAoKTsKICAgICAgICBmb3JlYWNoKCRkYXRhIGFzICRrPT4kdil7CiAgICAgICAgICAgIGlmKGluX2FycmF5KCRrLCR0aGlzLT5jbG91bW5zKSl7CiAgICAgICAgICAgICAgICAka2V5IC49ICRrLicsJzsKICAgICAgICAgICAgICAgICR2YWwgLj0gIiciLiR2LiInLCI7CiAgICAgICAgICAgIH0KICAgICAgICB9CiAgICAgICAgJGtleSA9IHN1YnN0cigka2V5LDAsLTEpOwogICAgICAgICR2YWwgPSBzdWJzdHIoJHZhbCwwLC0xKTsKICAgICAgICByZXR1cm4gJHRoaXMtPmRiLT5xdWVyeSgnSU5TRVJUIElOVE8gJy4kdGhpcy0+dGFibGUuIigka2V5KSBWQUxVRVMgKCR2YWwpIik7CiAgICB9CgogICAgZnVuY3Rpb24gdXAoJGRhdGEsJGlkKXsKICAgICAgICAvLyRkYXRhWyd1cGRhdGVfdGltZSddID0gZGF0ZSgnWS1tLWQgSDppOnMnKTsKICAgICAgICBmb3JlYWNoKCRkYXRhIGFzICRrPT4kdil7CiAgICAgICAgICAgIGlmKGluX2FycmF5KCRrLCR0aGlzLT5jbG91bW5zKSl7CiAgICAgICAgICAgICAgICAkdXBzdHIgLj0gJGsuIj0nIi4kdi4iJywiOwogICAgICAgICAgICB9CiAgICAgICAgfQogICAgICAgICR1cHN0ciA9IHN1YnN0cigkdXBzdHIsMCwtMSk7CiAgICAgICAgcmV0dXJuICR0aGlzLT5kYi0+cXVlcnkoJ1VQREFURSAnLiR0aGlzLT50YWJsZS4iIFNFVCAiLiR1cHN0ci4iIFdIRVJFICIuJHRoaXMtPnBrLiI9Ii4kaWQpOwogICAgfQoKICAgIGZ1bmN0aW9uIGJhdGNoaW4oJGRhdGEpewogICAgICAgICRpc0ZpcnN0ID0gMTsKICAgICAgICBmb3JlYWNoICgkZGF0YSBhcyAkcm93KXsKICAgICAgICAgICAgLy8kdXBkYXRlX3RpbWUgPSBkYXRlKCdZLW0tZCBIOmk6cycpOwogICAgICAgICAgICAvLyRyb3dbJ3VwZGF0ZV90aW1lJ10gPSAkdXBkYXRlX3RpbWU7CiAgICAgICAgICAgIGZvcmVhY2goJHJvdyBhcyAkaz0+JHYpewogICAgICAgICAgICAgICAgaWYoaW5fYXJyYXkoJGssJHRoaXMtPmNsb3VtbnMpKXsKICAgICAgICAgICAgICAgICAgICBpZigkaXNGaXJzdCkgJGtleSAuPSAkay4nLCc7CiAgICAgICAgICAgICAgICAgICAgJHZhbCAuPSAiJyIuYWRkc2xhc2hlc19kZWVwKCR2KS4iJywiOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICB9CiAgICAgICAgICAgIGlmKCRpc0ZpcnN0KSAka2V5ID0gc3Vic3RyKCRrZXksMCwtMSk7CiAgICAgICAgICAgICR2YWwgPSBzdWJzdHIoJHZhbCwwLC0xKTsKICAgICAgICAgICAgaWYoIWVtcHR5KCR2YWwpKXsKICAgICAgICAgICAgICAgICR2YWxzW10gPSAnKCcuJHZhbC4nKSc7CiAgICAgICAgICAgIH0KICAgICAgICAgICAgdW5zZXQoJHZhbCk7CiAgICAgICAgICAgICRpc0ZpcnN0ID0gMDsKICAgICAgICB9CiAgICAgICAgJHZhbHN0ciA9IGltcGxvZGUoJywnLCR2YWxzKTsKICAgICAgICByZXR1cm4gJHRoaXMtPmRiLT5xdWVyeSgnSU5TRVJUIElHTk9SRSBJTlRPICcuJHRoaXMtPnRhYmxlLiIoJGtleSkgVkFMVUVTICR2YWxzdHIiKTsKICAgIH0KCiAgICBmdW5jdGlvbiBiYXRjaEluc2VydE9uRHVwbGljYXRlS2V5KCRkYXRhKXsKICAgICAgICBpZihpc0FycmF5KCRkYXRhKSl7CiAgICAgICAgICAgIGZvcmVhY2ggKCRkYXRhIGFzICRyb3cpewogICAgICAgICAgICAgICAgJHVwc3RyID0gW107CiAgICAgICAgICAgICAgICAvLyDmoLnmja7lrp7pmYXmm7TmlrDlrZfmrrXkv67mlLnkuIvmlrkKICAgICAgICAgICAgICAgIC8vaWYoaXNzZXQoJHJvd1snY291bnRzJ10pKSAkdXBzdHJbXSA9ICJjb3VudHMgPSBjb3VudHMrMSAiOwogICAgICAgICAgICAgICAgLy9pZihpc3NldCgkcm93Wydpc19hY3RpdmUnXSkpICR1cHN0cltdID0gImlzX2FjdGl2ZSA9ICIgLiAkcm93Wydpc19hY3RpdmUnXTsKICAgICAgICAgICAgICAgIC8vaWYoaXNzZXQoJHJvd1snc29ydCddKSkgJHVwc3RyW10gPSAic29ydCA9ICIgLiAkcm93Wydzb3J0J107CiAgICAgICAgICAgICAgICAvL2lmKGlzc2V0KCRyb3dbJ3VwZGF0ZV90aW1lJ10pKSAkdXBzdHJbXSA9ICJ1cGRhdGVfdGltZSA9ICciIC4gJHJvd1sndXBkYXRlX3RpbWUnXSAuICInIjsKICAgICAgICAgICAgICAgIGlmKGlzQXJyYXkoJHVwc3RyKSkgJHRoaXMtPmRiX2FwdC0+aW5zZXJ0T25EdXBsaWNhdGVLZXkoJHJvdywgaW1wbG9kZSgnLCcsJHVwc3RyKSk7CiAgICAgICAgICAgIH0KICAgICAgICB9CiAgICAgICAgcmV0dXJuIHRydWU7CiAgICB9Cgp9');
            $content = str_replace('{$datenow}',$datenow, $content);
            $content = str_replace('{$tablename}',$tablename, $content);
            $content = str_replace('{$pri_key}',$pri_key, $content);
            $content = str_replace('{$str_cols}',$str_cols, $content);
            file_put_contents(APP_PATH.'/model/model_'.$tablename.'.php',$content);
            msg('Model File '.APP_PATH.'/model/model_'.$tablename.'.php created!');
        }else{
            msg('Table "'.$tablename.'" not exist, Please create table first.');
        }
    }

    function update_model($tablename){
        $model_file = APP_PATH.'/model/model_'.$tablename.'.php';
        if(file_exists($model_file)){
            $content = file_get_contents($model_file);
            $db_name = $this->tea->conf->db['dbname'];
            $res = $this->db->query('select column_name,column_key from information_schema.columns where TABLE_SCHEMA = \''.$db_name.'\' and TABLE_NAME = \''.$tablename.'\'');
            $columns = $res->fetchall();
            if(!empty($columns) && is_array($columns)){
                foreach ($columns as $column){
                    if(strtolower($column['column_key']) == 'pri'){
                        $pri_key = $column['column_name'];
                    }
                    $str_cols .= '\''.$column['column_name'].'\',';
                }
            }
            $str_cols = substr($str_cols,0,-1);
            $content = preg_replace('/\$cloumns = \[(.*?)\];/','$cloumns = ['.$str_cols.'];',$content);
            file_put_contents($model_file,$content);
            msg('Model File '.$model_file.' Updated!');
        }else{
            msg('Model File not exist, Please run "php run model:create '.$tablename.'" to create Model File first.');
        }
    }


}