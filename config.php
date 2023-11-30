<?php
defined('APP_PATH') or exit('No permission!');

define('STATIC_PATH', '/assets/');
define('STATIC_VERSION', '8');

$config = [
    'mode'=>'debug',
    'timezone' => 'Asia/Shanghai',                      // 设置时区
    //自动加载
    'autoload' => ['db','view','lib.common','session','lib.seo','vendor.autoload'],	//自动加载的类
    //Session
    'session' => [
        'driver' => 'tea_db', 			// 设置session驱动类型
        'options' => [
            'maxlifetime' => 3600*24*365*5,
            'cache_expire' => 3600*24*365*5,
            'cookie_lifetime' => 3600*24*365*5,
            'session_path' => '/tmp', 		// 默认session_path
        ]
    ],
    //路由
    'uri' => [  									// 路由配置
        'type' => 'rewrite', 							// 路由方式. 默认为default方式，可选default,pathinfo,rewrite,tea
        'default_controller' => 'main', 				// 默认的控制器名称
        'default_action' => 'index',  					// 默认的动作名称
        'para_controller' => 'c',  						// 请求时使用的控制器变量标识
        'para_action' => 'a',  							// 请求时使用的动作变量标识
        'suffix' => '.htm',									// 末尾添加的标记，一般为文件类型,如".html"，有助SEO
    ],

    //模板视图
    'view' => [ 									// 视图配置
        'engine' => 'teamplate', 						// 视图驱动名称teamplate,smarty
        'config' => [
            'tplext' => '.htm', 						// 模板文件后缀
            'template_dir' => APP_PATH.'/tpl', 			// 模板目录
            'compile_dir' => APP_PATH.'/data/cache', 	// 编译目录
            'cache_dir' => APP_PATH.'/data/cache', 		// smarty缓存目录
            'left_delimiter' => '{',  					// smarty左限定符
            'right_delimiter' => '}', 					// smarty右限定符
        ]
    ],
    //数据库
    'db' => [  							// 数据库连接配置
        'driver' => 'tea_mysqli',   	// 驱动类型 tea_pdo,tea_mysql,tea_mysqli,tea_adodb
        'dbtype' => 'mysql', 			// 数据库类型
        'host' => '127.0.0.1', 			// 数据库地址
        'dbuser' => 'app',       	// 数据库用户名
        'dbpass' => '7412369',      	// 数据库密码
        'dbname' => 'app',      	// 数据库名
        'prefix' => '',           		// 表前缀
        'charset' => 'utf8mb4',      	// 数据库编码 utf8mb4,utf8,gbk,gb2312
    ],

];
