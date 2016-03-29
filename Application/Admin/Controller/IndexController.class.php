<?php

namespace Admin\Controller;

class IndexController extends __Controller {

    public function index(){
        $this->meta_title = '管理首页';
        $this->display();
    }

    public function trashCache() {
        if(!is_administrator())
            $this->error('权限不足。');

        $path = RUNTIME_PATH.'Temp';

        if(is_dir($path)){
            //打开文件
            if ($dh = opendir($path)){
                //遍历文件目录名称
                while (($file = readdir($dh)) != false){
                    //逐一进行删除
                    if(strpos($file,MODULE_NAME.'_') === 0) unlink($path.'/'.$file);
                }
                //关闭文件
                closedir($dh);
            }
        }
        $this->success('缓存已清空');
    }
}