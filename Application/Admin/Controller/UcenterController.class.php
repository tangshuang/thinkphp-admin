<?php

namespace Admin\Controller;

class UcenterController extends __Controller {

    protected $model = 'Administrator';

    public function profile() {
        if(IS_POST) {
            if($this->administrator['id'] != I('post.id'))
                $this->error('请求非法。');
            parent::edit($this->administrator['id']);
        }
        else {
            $form = array(
                'username' => array(
                    'title' => '登录名',
                    'type' => 'disabled',
                    'value' => $this->administrator['username']
                ),
                'password' => array(
                    'title' => '密码',
                    'type' => 'password',
                    'name' => 'password'
                ),
                'email' => array(
                    'title' => 'Email',
                    'type' => 'email',
                    'name' => 'email',
                    'value' => $this->administrator['email']
                ),
                'id' => array(
                    'type' => 'hidden',
                    'name' => 'id',
                    'value' => $this->administrator['id']
                )
            );
            $this->meta_title = '个人资料';
            $this->assign('_fields_',$form);
            $this->display('Base/form');
        }
    }

    public function _after_profile() {
        if(!IS_POST) return;
        $administrator = $this->model->where(array('id' => $this->administrator['id']))->find();
        session('administrator',$administrator);
    }

}