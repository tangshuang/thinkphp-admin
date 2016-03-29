<?php

/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */

if(!defined('NOW_TIME')) define('NOW_TIME',$_SERVER['REQUEST_TIME']);
if(!defined('NOW_TIMESTAMP')) define('NOW_TIMESTAMP',gmdate('D, d M Y H:i:s',NOW_TIME).' GMT');
if(!defined('NOW_DATE')) define('NOW_DATE',gmdate('Y-m-d',NOW_TIME + 3600 * 8));
if(!defined('NOW_DATETIME')) define('NOW_DATETIME',gmdate('Y-m-d H:i:s',NOW_TIME + 3600 * 8));

// ---------------- URL -------------------

/**
 * 根据图片的ID，获取图片的URL
 * @param $id
 * @param $size 为true时表示调用原始尺寸图片
 * @param $domain 是否使用static url函数调用带域名的url
 * @return bool|string
 */
function get_image_url_by_id($id,$size = true,$domain = true) {
    $image = M('picture')->where(array('id' => $id))->find();
    if(!$image) return ''; // 不存在该图片

    if(strpos($image['url'],'http') === 0) { // 如果url是以http开头的，那么说明这个url是一个绝对路径，直接返回
        $url = $image['url'];
    }
    else { // 如果是本地存储的图片
        $url = $domain ? STATIC_URL.$image['url'] : $image['url'];
    }

    if($size !== true && is_string($size)) {
        $ext = substr(strrchr($url,'.'),0);
        $url = substr($url,0,strrpos($url,$ext))."_$size$ext";
    }

    return $url;
}

/**
 * 获取图片地址，如果第一个参数是字符串，则不查数据库，如果是数字，则作为图片ID查数据库后返回
 * @param string $data
 * @param bool $size 图片尺寸标识，例如large,small,300x300等，这个得靠开发者自己规定，总之，设置该值后，图片的返回url会跟上这个值作为尾巴
 * @param bool $domain 是否带上域名
 * @return bool|string
 */
function get_image_url($data = '',$size = true,$domain = true) {
    if(!$data) return '';

    if(is_string($data)) {
        if(strpos($data,'http') === 0) {
            $url = $data;
        }
        else {
            $url = $domain ? STATIC_URL.$data : $data;
        }

        if($size !== true && is_string($size)) {
            $ext = substr(strrchr($url,'.'),0);
            $url = substr($url,0,strrpos($url,$ext))."_$size$ext";
        }

        return $url;
    }

    /**
     * 当传入的第一个参数是数字时，用户应该是想通过图片的ID获得图片的具体尺寸的URL
     */
    return get_image_url_by_id($data,$size,$domain);
}

/**
 * 通过id列表，获取图片，以id,url作为键名返回一个二维数组，在模板中使用json_encode或其他方式进行字符串化
 * 举例：
 * 1. get_images(23) -> array('id' => 23, 'url' => 'http:...')
 * 2. get_images(12,34,54,...) -> array(
        array('id' => 12,'url' => '...'),
        array('id' => 34,'url' => '...'),
        array('id' => 54,'url' => '...'),
        ...
 *    )
 * 3. get_images('12,34,54,...') -> 和2效果一样
 * 4. get_images(array(12,34,54,...)) -> 和2效果一样
 */
function get_images() {
    $param_num = func_num_args();
    $param_args = func_get_args();

    if($param_num == 0) {
        return null;
    }
    elseif($param_num == 1 && is_numeric($param_args[0])) {
        $image_id = $param_args[0];
        $image_url = get_image_url_by_id($image_id);
        return $image_url ? array('id' => $image_id,'url' => $image_url) : null;
    }
    elseif($param_num == 1 && is_array($param_args[0])) {
        $image_ids = $param_args[0];
        $images = array();
        foreach($image_ids as $image_id) {
            $image_url = get_image_url_by_id($image_id);
            $images[] = $image_url ? array('id' => $image_id,'url' => $image_url) : null;
        }
        return $images;
    }
    elseif($param_num == 1 && is_string($param_args[0])) {
        $image_ids = $param_args[0];
        // 如果参数是类似 'afdsdffdsa' 这样的字符串的话，直接返回false即可
        if(strpos($image_ids,',') === false)
            return null;

        $image_ids = explode(',',$image_ids);
        return get_images($image_ids);
    }
    elseif($param_num > 1) {
        return get_images($param_args);
    }
    else {
        return null;
    }
}

// ----------------- string & array ---------------

/**
 * @https://github.com/tangshuang.com/array_tree.php
 * 构建层级（树状）数组
 * @param array $array 要进行处理的一维数组，经过该函数处理后，该数组自动转为树状数组
 * @param string $pid 父级ID的字段名
 * @return array|bool
 */
function array_tree($array,$pid = 'pid') {
    // 子元素计数器
    $array_children_count = function($array,$pid) {
        $counter = array();
        foreach($array as $item) {
            $count = isset($counter[$item[$pid]]) ? $counter[$item[$pid]] : 0;
            $count  ++;
            $counter[$item[$pid]] = $count;
        }
        return $counter;
    };
    // 把元素插入到对应的父元素children字段
    $array_child_append = function($parent,$pid,$child) {
        foreach($parent as &$item) {
            if($item['id'] == $pid) {
                if(!isset($item['children']))
                    $item['children'] = array();
                $item['children'][$child['id']] = $child;
            }
        }
        return $parent;
    };
    // 开始程序
    $counter = $array_children_count($array,$pid);
    // 如果顶级元素为0个，那么直接返回false
    if($counter[0] == 0)
        return false;
    // 过滤原始数组，把其键名和id字段等同（保险起见，一定要操作这一步）
    $temp = array();
    foreach($array as $i => $item) {
        unset($array[$i]);
        $temp[$item['id']] = $item;
    }
    $array = $temp;
    // 准备顶级元素
    $tree = array();
    // 位移
    while(isset($counter[0]) && $counter[0] > 0) { // 如果顶级栏目的子元素计数器仍然大于0，那么仍然往下执行循环
        $temp = array_shift($array);
        if(isset($counter[$temp['id']]) && $counter[$temp['id']] > 0) { // 如果数组的第一个元素的子元素个数大于0，那么把该元素放置到数组的末端
            array_push($array,$temp);
        }
        else { // 相反，如果该数组的第一个元素没有子元素，那么把该元素移动到其父元素的children字段中，同时，该元素从原数组中被删除
            if($temp[$pid] == 0)
                $tree[$temp['id']] = $temp;
            else
                $array = $array_child_append($array,$temp[$pid],$temp);
        }
        $counter = $array_children_count($array,$pid);
    }
    return $tree;
}

/**
 * @https://github.com/tangshuang/array_orderby.php
 * 对二维数组进行按字段排序
 * @param array $array 要排序的二维数组
 * @param bool $orderby 根据该字段（二维数组单个元素中的键名）排序
 * @param string $order 排序方式，asc:升序；desc:降序（默认）
 * @param string $children 子元素字段（键名），当元素含有该字段时，进行递归排序
 * @return array
 */
function array_orderby($array,$orderby = null,$order = 'desc',$children = false) {
    if($orderby == null)
        return $array;
    $key_value = $new_array = array();
    foreach($array as $k => $v) {
        $key_value[$k] = $v[$orderby];
    }
    if($order == 'asc') {
        asort($key_value);
    }
    else {
        arsort($key_value);
    }
    reset($key_value);
    foreach($key_value as $k => $v) {
        $new_array[$k] = $array[$k];
        // 如果有children
        if($children && isset($new_array[$k][$children])) {
            $new_array[$k][$children] = array_orderby($new_array[$k][$children],$orderby,$order,$children);
        }
    }
    //$new_array = array_values($new_array); // 使键名为0,1,2,3...
    return $new_array;
}

/**
 * 从数组中返回所指元素的层级关系
 * @param $id
 * @param $items
 * @param string $pid
 * @return array 返回值为从根节点到所指节点的 id 列表
 */
function array_ancestry($id,$items,$pid = 'pid') {
    $results = array();
    foreach($items as $item) {
        $results[$item['id']] = $item;
    }
    $items = $results;
    unset($results);

    $ancestry_ids = array();
    array_unshift($ancestry_ids,$id);

    $item = $items[$id];
    while(isset($item[$pid]) && $item[$pid] > 0) {
        $item = $items[$item[$pid]];
        array_unshift($ancestry_ids,$item['id']);
    }

    return $ancestry_ids;
}

/**
 * 以第一个数组的值作为键名，去第二个数组中去挑选
 * @param $keys
 * @param $array
 */
function array_pick_by_keys($keys = array(),$array = array()) {
    if(!is_array($list) || empty($list)) return null;
    foreach($array as $i => $s) {
        if(!in_array($i,$keys)) unset($array[$i]);
    }
    return $array;
}

/**
 * 用以实现通过id列表和原始数据而实现的列表转换。与array_pick_by_keys不同的是，第一个参数是字符串
 * @param string $list key列表，例如“1,3,5,12,56”
 * @param array $array 用于挑选的原始数据，如果id不存在于这个原数数组中，那么该id不返回任何值
 */
function array_pick_by_list($list = '',$array = array()) {
    // 如果不存在$list，直接返回空
    if(!$list) return null;
    // 如果$list中不存在逗号，那么直接作为键进行查看
    if(strpos($list,',') === false) {
        if(isset($array[$list])) return $array[$list];
        else return null;
    }
    // 进行切分和处理
    $list = explode(',',$list);
    foreach($array as $i => $s) {
        if(!in_array($i,$list)) unset($array[$i]);
    }
    return $array;
}

// ------------------- 加密 ------------------------

/**
 * MD5带盐加密
 * @param $data
 * @param $salt
 * @return string
 */
function md5_salt($str,$salt = AUTH_SALT_KEY) {
    return !$str ? '' : md5(sha1($str).$salt);
}

/**
 * 返回16位小写结果的md5值
 * @param $str
 * @return string
 */
function md5_16($str) {
    $str = md5($str);
    $str = substr($str,8,16);
    return $str;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $salt  加密密钥
 * @param int $expire  过期时间 (单位:秒)
 * @return string
 */
function encrypt_salt($data, $salt = AUTH_SALT_KEY, $expire = 0) {
    $salt  = md5($salt);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($salt);
    $char =  '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x=0;
        $char  .= substr($salt, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data,$i,1)) + (ord(substr($char,$i,1)))%256);
    }
    $str = base64_encode($str);
    $str = str_replace(array(' ','+','/'),array('O0O0O','o000o','oo00o'),$str);
    return $str;
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串
 * @param string $salt  加密密钥
 * @return string
 */
function decrypt_salt($data, $salt = AUTH_SALT_KEY) {
    $data = str_replace(array('O0O0O','o000o','oo00o'), array(' ','+','/'),$data);
    $salt    = md5($salt);
    $x      = 0;
    $data   = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data   = substr($data, 10);
    if($expire > 0 && $expire < time()) {
        return false;
    }
    $len  = strlen($data);
    $l    = strlen($salt);
    $char = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char  .= substr($salt, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

// ------------------- 其他辅助函数 --------------------


/**
 * 浏览器缓存
 */
function http_header_cache($expire = '+15 minutes') {
    header("Cache-Control: public");
    header("Pragma: cache");
    // 如果存在缓存，则使用缓存
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $last_modified = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
        $expire = strtotime(trim("$last_modified $expire"));
        if($expire > NOW_TIME) {
            header("Expires: ".gmdate("D, d M Y H:i:s",$expire)." GMT");
            header("Last-Modified: $last_modified",true,304);
            exit;
        }
    }
    // 如果不存在缓存，则增加上次更新时间，从而加入缓存
    header("Expires: ".gmdate("D, d M Y H:i:s",strtotime($expire))." GMT");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
}