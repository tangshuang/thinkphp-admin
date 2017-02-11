<?php

return array(
    // 超级管理员
    'ADMINISTRATOR_ID' => 1,
    'ADMINISTRATOR_PASSWORD' => '123456',

    /* 数据缓存设置 */
    'DATA_CACHE_PREFIX'    => MODULE_NAME.'_',

    /* SESSION 和 COOKIE 配置 */
    'SESSION_OPTIONS' => array(
        'expire' => 1800,
        'prefix' => MODULE_NAME.'_'
    ),
    'VAR_SESSION_ID' => 'session_id',
    'COOKIE_PREFIX'  => MODULE_NAME.'_',
    'COOKIE_EXPIRE'  => 7200,

    /* 模板相关配置 == MODULE_NAME仅支持当前模块，因此不能讲这些配置放在Common中 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__' => STATIC_URL.'/Public/Static',
        '__PUBLIC__' => STATIC_URL.'/Public/'.MODULE_NAME,
        '__IMG__'    => STATIC_URL.'/Public/' . MODULE_NAME . '/img',
        '__CSS__'    => STATIC_URL.'/Public/' . MODULE_NAME . '/css',
        '__JS__'     => STATIC_URL.'/Public/' . MODULE_NAME . '/js'
    ),

    /* 后台错误页面模板 */
    /*
    'TMPL_ACTION_ERROR'     =>  MODULE_PATH.'View/Base/error.html',
    'TMPL_ACTION_SUCCESS'   =>  MODULE_PATH.'View/Base/success.html',
    'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/Base/exception.html',
    */

);
