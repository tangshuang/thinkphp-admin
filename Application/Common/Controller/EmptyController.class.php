<?php

namespace Common\Controller;

class EmptyController extends __Controller{

    public function index(){
        $this->notFound();
    }

    public function _empty($action) {
        $file = MODULE_PATH.'View/'.CONTROLLER_NAME.'/'.$action.'.html';
        if(strpos($action,'_') !== 0 && file_exists($file)) {
            $this->display();
            exit;
        }
        $this->notFound();
    }

    private function notFound() {
        header("HTTP/1.0 404 Not Found");

        $this->assign('error','抱歉，页面未找到！');
        $this->display();
    }
}
