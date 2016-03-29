<?php

namespace Admin\Controller;

class PictureController extends __Controller {

    public function index() {
        $this->meta_title = '图片库';
        $this->lists['field'] = 'id,url';
        parent::index();
    }

    public function upload() {
        if(!IS_AJAX)
            $this->error('请求非法。');
        $result = parent::uploadPicture('file');
        if($result)
            $this->ajaxReturn($result);
        $this->error('上传失败。');
    }
}