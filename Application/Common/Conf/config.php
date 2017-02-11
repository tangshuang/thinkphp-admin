<?php

return array(
    /* 模块相关配置 */
    'DEFAULT_MODULE'     => 'Home',
    'MODULE_DENY_LIST'   => array('Common'), // 禁止访问这几个模块

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => false, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => '', //全局过滤函数

    // 数据缓存
    'DATA_CACHE_TYPE'      => 'file',
    'DATA_CACHE_TIME'      => 1800,

    /* 数据库配置 */
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => DB_HOST, // 服务器地址
    'DB_NAME'   => DB_NAME, // 数据库名
    'DB_USER'   => DB_USER, // 用户名
    'DB_PWD'    => DB_PASSWORD,  // 密码
    'DB_PORT'   => DB_PORT, // 端口
    'DB_PREFIX' => DB_PREFIX, // 数据库表前缀

    // 上传驱动配置
    'UPLOAD_DRIVER_LOCAL_CONFIG' => array(), // 上传到服务器本地
    // 图片上传相关配置
    'PICTURE_UPLOAD_DRIVER' => 'LOCAL', // 使用什么驱动上传图片，默认保存到服务器本地
    'PICTURE_UPLOAD' => array(
        'mimes'    => 'image/gif,image/jpeg,image/png', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //2M，上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Picture/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => true, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),
    'PICTURE_THUMBNAIL_SIZES' => array( // 图片上传后缩略图尺寸
        '300x300' => '300x300'
    ),
    //'PICTURE_WATER_PATH' => STATIC_PATH.'/picture_water.png', // 图片上传后水印

);
