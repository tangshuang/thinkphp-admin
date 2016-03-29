<?php

/**
 * 判断用户是否登陆
 * @return bool|int
 */
function is_login(){
    return session('is_login');
}

/**
 * 检测当前登录用户是否为管理员
 * @return boolean true-管理员，false-非管理员
 */
function is_administrator($uid = null){
    if(!is_login())
        return false;
    if(is_null($uid)) {
        $administrator = session('administrator');
        $uid = $administrator['id'];
    }
    return $uid && (intval($uid) === C('ADMINISTRATOR_ID'));
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}