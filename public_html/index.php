<?php

/**
 * 调试模式
 */
define('APP_DEBUG',true);
if(APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

/*
 * 可以预先预定义SITE_PATH常量，默认使用public_html目录，注意使用绝对地址
 */
define('SITE_PATH',dirname(__FILE__));

/*
 * 可以预先定义全局配置文件的路径，默认使用上一层的config.php文件，注意使用绝对地址
 * define('CONFIG_PATH',dirname(__FILE__).'/../config.my.php');
 */

/**
 * 入口基本配置加载
 */
require SITE_PATH.'/../load.php';
