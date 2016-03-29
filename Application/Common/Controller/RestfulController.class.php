<?php

namespace Common\Controller;
use Think\Controller\RestController;
use Common\Controller\BaseController;

class RestfulController extends RestController {

    public function _initialize() {
        $BaseController = new BaseController();
        $BaseController->_initialize();
    }

    /**
     * 上传多张图片，在其他的Controller中，使用$result = $this->uploadPictures();即可监听图片上传，并且得到需要的上传结果
     * @param bool $is_thumb
     * @param bool $is_water
     * @return bool
     */
    protected function uploadPictures($is_thumb = true,$is_water = false)
    {
        $BaseController = new BaseController();
        $BaseController->uploadPictures($is_thumb,$is_water);
    }

    /**
     * 上传单张图片
     * @param string $field
     * @param bool $is_thumb
     * @param bool $is_water
     * @return bool
     */
    protected function uploadPicture($field = 'file',$is_thumb = true,$is_water = false)
    {
        $BaseController = new BaseController();
        $BaseController->uploadPicture($field,$is_thumb,$is_water);
    }
}