<?php

namespace Admin\Controller;
use Common\Controller\BaseController;

class LoginController extends BaseController {

    public function _initialize() {
        $this->CONFIG();
    }

    public function index() {
        $this->display();
    }

    public function login($username,$password) {
        $AdministratorModel = D('Administrator');
        $administrator_id = $AdministratorModel->login($username,$password);
        if(!$administrator_id)
            $this->error($AdministratorModel->getError());

        $administrator = $AdministratorModel->where(array('id' => $administrator_id))->find();
        session('is_login',1);
        cookie('administrator',$administrator_id);
        session('administrator',$administrator);

        $this->success('登陆成功。',U('Index/index'));
    }

    public function logout() {
        session('is_login',null);
        cookie('administrator',null);
        session('administrator',null);
        session('[destroy]');
        $this->success('退出成功。',U('index'));
    }

}