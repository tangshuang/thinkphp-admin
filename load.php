<?php

/**
 * 检测PHP环境
 */
if(version_compare(PHP_VERSION,'5.3.0','<')) die('require PHP > 5.3.0 !');

/**
 * 自定义的一些常量
 * @author 否子戈
 */
define('SYSTEM_PATH', dirname(__FILE__)); // 系统根目录

/**
 * 应用目录设置
 * 安全期间，建议安装调试完成后移动到非WEB目录
 */
define('APP_PATH', SYSTEM_PATH.'/Application/');

/**
 * 缓存目录设置
 * 此目录必须可写，建议移动到非WEB目录
 */
define('RUNTIME_PATH', SYSTEM_PATH.'/Runtime/');

/**
 * 不生成index.html
 */
define('BUILD_DIR_SECURE', false);

/**
 *****************************************************************************************************
 *****************************************************************************************************
 *****************************************************************************************************
 */



// ************************************ SITE ***********************************************************

/**
 * SITE_PATH
 */
if(!defined('SITE_PATH')) define('SITE_PATH',dirname(__FILE__).'/public_html');

/**
 * site_url()
 * @param $uri
 * @return string
 */
function site_url($uri = '',$base_url = false) {
    if(!$base_url) {
        $root = $_SERVER['DOCUMENT_ROOT'];
        $base = SITE_PATH;
        $subdir = str_replace($root,'',$base);

        if(isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))){
            $scheme = 'https://';
        }
        elseif(isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'] )) {
            $scheme = 'https://';
        }
        else {
            $scheme = 'http://';
        }

        $domain = $_SERVER['HTTP_HOST'];
        $base_url = $scheme.$domain.$subdir;
    }

    if($uri == '')
        return $base_url;

    if(substr($uri,0,1) != '/')
        $base_url .= '/';
    $url = $base_url.$uri;
    return $url;
}

/**
 * SITE_URL
 */
define('SITE_URL',site_url());

// ******************************************************************************************************

// 引入常量配置类，同时通过循环实现define
if(!defined('CONFIG_PATH')) define('CONFIG_PATH',SYSTEM_PATH.'/config.php');
if(file_exists(CONFIG_PATH)) include CONFIG_PATH;
else include SYSTEM_PATH.'/config-default.php';

// 动态预定义数据库信息
foreach(Constants::$DB as $var => $value) {
    define('DB_'.strtoupper($var),$value);
}

// 动态预定义PATH
foreach(Constants::$PATH as $var => $path) {
    if($path == '') $path = SITE_PATH;
    define(strtoupper($var).'_PATH',$path);
}

// 动态URL处理
foreach(Constants::$URL as $var => $url) {
    if($url == '') $url = SITE_URL;
    // 动态预定义常量
    $def = strtoupper($var);
    define($def.'_URL',$url);
    // 动态声明函数
    $fun = strtolower($var).'_url';
    eval('function '.$fun.'($uri = \'\') {return site_url($uri,'.$def.'_URL);}'); // 使用eval动态声明函数，但是eval函数在大部分系统中被屏蔽掉，不能使用
}

// 动态预定义常量
foreach(Constants::$DEFINE as $key => $value) {
    define($key,$value);
}

/**
 * 绑定模块
 */
foreach(Constants::$URL as $module => $url) {
    if($url == '') continue;
    if($_SERVER['SERVER_NAME'] == parse_url($url,PHP_URL_HOST)) {
        define('BIND_MODULE',ucfirst($module));
        break;
    }
}

if(defined('BIND_MODULE') && BIND_MODULE == 'Api') {
    //define('MODE_NAME','rest');
    header('Access-Control-Allow-Origin:'.webapp_url());
    header('Access-Control-Allow-Headers:X-Requested-With');
    header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE');
    if("OPTIONS" == $_SERVER['REQUEST_METHOD']) {
        header("HTTP/1.1 200 OK");
        exit;
    }
    //echo $_SERVER['HTTP_ACCEPT'];exit;
}


/**
 * 引入ThinkPHP核心入口
 */
require SYSTEM_PATH.'/ThinkPHP/ThinkPHP.php';
