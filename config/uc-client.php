<?php

return [

    'url'		=> 'http://bbs.jdt.com/api/uc.php', // 网站UCenter接受数据地址

    'api'		=> 'http://bbs.jdt.com/uc_server', // UCenter 的 URL 地址, 在调用头像时依赖此常量

    /*
    |--------------------------------------------------------------------------
    | 连接 UCenter 的方式
    |--------------------------------------------------------------------------
    |
    | mysql/NULL, 默认为空时为 fscoketopen()
    |
    */
    'connect'	=> 'mysql',

    /*
    |--------------------------------------------------------------------------
    | 连接 UCenter 的应用配置
    |--------------------------------------------------------------------------
    |
    */
    'key'		=> '32182vFikUtPT8pPKMngsZHR/ZS4kWFhE', // 与 UCenter 的通信密钥, 要与 UCenter 保持一致
    'charset'	=> 'utf-8', // UCenter 的字符集
    'ip'		=> '123.56.9.120', // UCenter 的 IP, 当 UC_CONNECT 为非 mysql 方式时, 并且当前应用服务器解析域名有问题时, 请设置此值
    'appid'		=> 1, //当前应用的 ID
    'ppp'		=> 0,

    /*
    |--------------------------------------------------------------------------
    | Ucenter接口调用的数据库配置
    |--------------------------------------------------------------------------
    |
    | 如果连接 UCenter 的方式 connect 被配置为 mysql , 数据库配置就会被使用到
    |
    */
    'dbhost'	=> 'rm-2ze60g5skl492r0.mysql.rds.aliyuncs.com', // UCenter 数据库主机
    'dbuser'	=> 'su_forum', // UCenter 数据库用户名
    'dbpw'		=> 'HPXzTCJJVxvyY7L2', // UCenter 数据库密码
    'dbname'	=> 'db_foum', // UCenter 数据库名称
    'dbcharset'	=> 'utf8',// UCenter 数据库字符集
    'dbtablepre'=> 'db_forum`.pre_ucenter_', // UCenter 数据库表前缀

];
