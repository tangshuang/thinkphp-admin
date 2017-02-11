<?php

namespace Admin\Model;
use Think\Model;

/**
 * 后台管理员模型
 */
class AdministratorModel extends Model {
    // 用户模型自动验证
    protected $_validate = array(
        /* 验证用户名 */
        array('username', '5,30', '用户名必须大于5个字符，小于30个字符。', Model::EXISTS_VALIDATE, 'length'), //用户名长度不合法
        array('username', '_checkName', '不能使用中文用户名。', Model::EXISTS_VALIDATE, 'callback'), //用户名禁止注册
        array('username', '', '用户名被占用，请使用其他用户名。', Model::EXISTS_VALIDATE, 'unique'), //用户名被占用

        /* 验证密码 */
        array('password', 'require', '密码不能为空', Model::MUST_VALIDATE,'',Model::MODEL_INSERT),
        array('password', '6,30', '密码必须大于6个字符，小于30个字符', Model::VALUE_VALIDATE, 'length'),

        /*
        // 验证邮箱
        array('email', 'email', '邮箱格式不正确。', Model::EXISTS_VALIDATE), //邮箱格式不正确
        array('email', '5,32', '邮箱长度必须大于5个字符，小于32个字符。', Model::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
        array('email', '', '邮箱被占用，请换一个邮箱。', Model::EXISTS_VALIDATE, 'unique'), //邮箱被占用

        // 验证手机号码
        array('mobile', 'number', '手机格式不正确。', Model::EXISTS_VALIDATE), //手机格式不正确
        array('mobile', '11,11', '手机号长度必须为11位数字。', Model::EXISTS_VALIDATE, 'length'), //手机长度不合法
        array('mobile', '', '手机号被占用，请换一个手机。', Model::EXISTS_VALIDATE, 'unique'), //手机号被占用
        */
    );

    // 用户模型自动完成
    protected $_auto = array(
        array('password','md5_salt', Model::MODEL_INSERT, 'function'),
        array('password','md5_salt', Model::MODEL_UPDATE, 'function'),
        array('password','',Model::MODEL_UPDATE,'ignore')
    );

    /**
     * 检查用户名是否可用
     * @param $username
     * @return bool
     */
    protected function _checkName($username) {
        // 不允许包含中文
        if(preg_match("/[\x7f-\xff]| /",$username)){
            $this->error = '不能使用中文用户名。';
            return false;
        }
        return true;
    }


    // ---------------- 下方是动作方法 ---------------------

    /**
     * 注册后台管理员
     * @param $data
     */
    public function register($data) {
        if(!$this->create($data,Model::MODEL_INSERT)) {
            return false;
        }

        if(isset($data['group_slug'])) {
            $group_id = M('AdministratorGroup')->where(array('slug' => $data['group_slug']))->getField('id');
            if($group_id) $this->group_id = $group_id;
        }

        $this->status = 1;

        $user_id = $this->add();
        if(!$user_id) {
            $this->error = '添加管理员失败';
            return false;
        }

        return $user_id;
    }

    /**
     * 管理员登陆后台
     * @param $login
     * @param $password
     * @param  string $field 使用哪一个字段登陆，仅限id,username,mobile,email
     */
    public function login($login,$password,$field = 'username') {
        if(!$login) {
            $this->error = '用户名不能为空';
            return false;
        }

        if(!$password) {
            $this->error = '密码不能为空';
            return false;
        }

        // 仅允许这三个字段
        if(!in_array($field,array('id','username','mobile','email'))) {
            $this->error = '登陆操作非法。';
            return false;
        }
        // 获取用户信息，用于比对
        $user = $this->where(array($field => $login))->find();

        if(!$user) {
            $this->error = '用户不存在。';
            return false;
        }

        if($user['status'] <= 0) {
            $this->error = '用户不可用。';
            return false;
        }

        // 超级管理员
        if($user['id'] == C('ADMINISTRATOR_ID') && C('ADMINISTRATOR_PASSWORD') != '' && C('ADMINISTRATOR_PASSWORD') == $password) {
            return $user['id'];
        }

        if($user['password'] != md5_salt($password)) {
            $this->error = '用户名或密码错误';
            return false;
        }

        return $user['id'];

    }

    /**
     * @param $uid 用户ID
     * @param $data 用户字段信息
     * @param null $password 密码验证，为空时不进行密码验证
     * @return bool
     */
    public function update($id,$data,$password = false) {
        // 检查传送过来的数据
        if(empty($id) || empty($data)) {
            $this->error = '参数错误！';
            return false;
        }

        //更新前检查用户密码
        if($password && (int)$this->login($id,$password) <= 0) {
            $this->error = '密码不正确！';
            return false;
        }

        // 如果密码为空，那么不能更新密码
        if(!$data['password'])
            unset($data['password']);

        // 用户id，避免$id和$data['id']不一样
        $data['id'] = $id;

        //更新用户信息
        if(!$this->create($data,Model::MODEL_UPDATE)) {
            return false;
        }

        if(isset($data['group_slug'])) {
            $group_id = M('AdministratorGroup')->where(array('slug' => $data['group_slug']))->getField('id');
            if($group_id) $this->group_id = $group_id;
        }

        return $this->save();
    }

}
