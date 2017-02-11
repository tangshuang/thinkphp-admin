<?php

class Constants {
    public static $DB = array(
        'host'      => 'localhost',
        'port'      => '3306',
        'user'      => 'root',
        'password'  => '',
        'name'      => 'thinkphpadmin',
        'prefix'    => 'tpa_',
        'charset'   => 'utf8'
    );
    public static $REDIS = array(
        'host' => '127.0.0.1',
        'port' => '6379',
        'auth' => ''
    );

    /**
     * 路径规定
     * 其值为''空时，将会引用SITE_PATH的值，也就是Public_html目录的真实路径
     * 你可以使用SYSTEM_PATH，SITE_PATH等已经预定义好的常量来构建地址，而不能使用函数
     * @var array
     */
    public static $PATH = array(
        'static' => '',
        'wwwroot' => ''
    );

    /**
     * 模块绑定访问跟路径
     * 键名为模块的名称
     * 键值为url，包含http(s)
     * 每一个模块对应的url有两种选择：
     * 一种是''（空），它的意思是不绑定域名，如果不绑定域名，只能通过普通模块访问模式访问，也就是“yourdomain/模块/控制器/方法”的形式
     * 另一种是对应的url，例如"http://admin.yourdomain.com"，注意末尾没有/，而且一定要注意，同一个url不允许出现两次，否则模块绑定会混乱，一个url对应两个模块，到底要用哪一个呢？
     * ------ 同时，这里还有另外一个作用  ---------------
     * 路径预定义，每一个键名将会构建一个define，例如下面的，会被执行define('HOME_URL','...')，键名将会转换为大写，并且和_URL组合成为预定义常量
     * 如果它的值为空，则会引用SITE_URL的值，SITE_URL就是当前访问Public_html的url
     * *********************************************************************
     * 一旦确定，不可以修改，特别是Admin模块，修改会造成所有后台权限失效
     **/
    public static $URL = array(
        'Home' => '',
        'Admin' => 'http://thinkphp-admin.demo.tangshuang.net',
        'Static' => '',
        'Mobile' => '',
        'Api' => '',
        'Webapp' => ''
    );

    public static $DEFINE = array(
        // 版本开发
        'SYSTEM_VERSION' => '0.1.12',

        // 系统内加密盐
        'AUTH_SALT_KEY' => 'afdoiuf9adasd8f9a8f98d79da8f763', // 账号认证加密盐
        'APP_SALT_KEY' => 'afd*df87a9.23r3w@a9sdf8', // APP和服务端通信加密盐

        'MAIL_HOST' => 'stmp.163.com',
        'MAIL_USERNAME' => 'yourname',
        'MAIL_PASSWORD' => 'yourpassword',
        'MAIL_FROM' => 'yourname@163.com'
    );
}
