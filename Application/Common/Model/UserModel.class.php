<?php

namespace Common\Model;
use Think\Model;

class UserModel extends Model {

    /**
     * 用户模型自动验证
     */
    protected $_validate = array(
        /* 验证用户名 */
        array('username', '5,30', '用户名必须大于5个字符，小于30个字符。', Model::EXISTS_VALIDATE, 'length'),
        array('username', '', '用户名被占用，请使用其他用户名。', Model::EXISTS_VALIDATE, 'unique'),

        /* 验证密码 */
        array('password', '6,30', '密码长度必须大于6个字符，小于30个字符。', Model::EXISTS_VALIDATE, 'length'),

        /* 验证邮箱 */
        array('email', 'email', '邮箱格式不正确。', Model::EXISTS_VALIDATE),
        array('email', '5,32', '邮箱长度必须大于5个字符，小于32个字符。', Model::EXISTS_VALIDATE, 'length'),
        array('email', '', '邮箱被占用，请换一个邮箱。', Model::EXISTS_VALIDATE, 'unique'),

    );

    /**
     * 用户模型自动完成
     */
    protected $_auto = array(
        array('password', 'md5_salt', Model::MODEL_BOTH, 'function'),
        array('register_time', 'date', Model::MODEL_INSERT, 'function', 'Y-m-d H:i:s'),
        array('register_ip', 'get_client_ip', Model::MODEL_INSERT, 'function')
    );

    /**
     * 注册前台用户
     * @param $data
     */
    public function register($data = false) {
        if(!$data)
            $data = $this->data;
        if(!$this->create($data,Model::MODEL_INSERT)) {
            return false;
        }
        return $this->add();
    }

    /**
     * 验证登录
     * @param $login
     * @param $password 如果为 false 表示不需要密码就可以登录
     * @param string $field
     * @return bool
     */
    public function login($login,$password = false,$field = 'username') {
        // 仅允许这些字段
        if(!in_array($field,array('id','username','email'))) {
            $this->error = '登陆操作非法。';
            return false;
        }
        // 获取用户信息，用于比对
        $user = $this->where(array($field => $login))->find();
        // 用户是否可用
        if(is_array($user) && !empty($user)) {
            if($user['status'] < 0) {
                $this->error = '用户不可用。';
                return false;
            }
            if($password && $user['password'] != md5_salt($password)) {
                $this->error = '用户名或密码错误。';
                return false;
            }
            return $user;
        }
        $this->error = '该用户不存在。';
        return false;
    }

    /**
     * 更新用户信息
     * @param $uid 用户ID
     * @param $data 用户字段信息
     * @param null $password 密码验证，为空时不进行密码验证
     * @return bool
     */
    public function update($id,$data,$password = null) {
        // 检查传送过来的数据
        if(empty($id) || empty($data)) {
            $this->error = '参数错误！';
            return false;
        }
        //更新前检查用户密码
        if($password && !$this->login($id,$password,'id')) {
            return false;
        }
        // 用户id，避免$id和$data['id']不一样
        $data['id'] = $id;
        //更新用户信息
        if(!$this->create($data,Model::MODEL_UPDATE)) {
            return false;
        }
        return $this->save();
    }

    public function get($action = 'select',$where = false,$fields = array(),$order = false) {
        $this->join('__USER_INFO__ ON __USER_INFO__.uid=__USER__.id');
        if($where) $this->where($where);
        if(!empty($fields) && $action != 'get') $this->field($field);
        if($order) $this->order($order);
        switch($action) {
            case 'get':
                if(is_array($fields)) $fields = implode(',',$fields);
                $result = $this->getField($fields);
                break;
            case 'find':
                $result = $this->find();
                break;
            default:
                $result = $this->select();
                break;
        }
        return $result;
    }

    /**
     * 获取某个用户的所有信息
     * @param $id
     */
    public function getData($id) {
        $user = $this->join('__USER_INFO__ ON __USER_INFO__.uid=__USER__.id')->where(array('id' => $id))->find();
        $user_meta = M('UserMeta')->where(array('uid' => $id))->getField('key,value');
        if($user_meta) $user = array_merge($user_meta,$user);
        return $user;
    }

    /**
     * 获取用户附加信息
     * @param int $id 用户ID
     * @param bool $key 字段名，如果为false，则返回所有字段
     * @return mixed
     */
    public function getInfo($id,$key = false) {
        $Model = M('UserInfo');
        if($key === false) $result = $Model->where(array('uid' => $id))->find();
        else $result = $Model->where(array('uid' => $id))->getField($key);
        return $result;
    }

    /**
     * 获取用户meta
     * @param $id 用户ID
     * @param $key 用户的meta key
     * @param bool $mulit 是否以数组的形式返回多个，为false的时候，仅返回找到的第一个
     * @return mixed
     */
    public function getMeta($id,$key,$mulit = false) {
        $Model = M('user_meta');
        $Model->where(array('uid' => $id,'key' => $key));
        $result = $Model->getField('value',$mulit);
        return $result;
    }

    public function getMetas($id) {
        $Model = M('user_meta');
        $result = $Model->where(array('uid' => $id))->getField('key,value');
        return $result;
    }

    /**
     * 添加meta
     * @param $id 用户ID
     * @param $key meta_key
     * @param $value meta_value
     * @param bool $unique 是否让该值为唯一值，如果是true，会删掉以往添加的所有meta key，确保数据库中只有一个user meta
     * @return mixed
     */
    public function addMeta($id,$key,$value,$unique = true) {
        $Model = M('user_meta');
        $data = array('uid' => $id,'key' => $key,'value' => $value);
        if($unique)
            $Model->where(array('uid' => $id,'key' => $key))->delete();
        $Model->startTrans();
        $result = $Model->data($data)->add();
        if($result === false)
            $Model->rollback();
        $Model->commit();
        return $result;
    }

    /**
     * 更新meta
     * @param $id 用户ID
     * @param $key meta key
     * @param $value meta value
     * @param int $offset 更新的位移，同一个meta key，可能会有多个value，它们会有一个先后顺序，因此，你可以通过offset来确保更新的是第几个。想要获得顺序，可以通过getMeta来了解，本方法仅能更新一个值，不能一次性把所有值都更新
     * @return bool
     */
    public function updateMeta($id,$key,$value,$offset = 0) {
        $Model = M('user_meta');
        $data = array('value' => $value);
        $id = $Model->where(array('uid' => $id,'key' => $key))->limit($offset,1)->getField('id');
        if(!$id) {
            $this->error = '不存在该记录';
            return false;
        }
        return $Model->where(array('id' => $id))->data($data)->save();
    }

    /**
     * 删除meta
     * @param $id 用户ID
     * @param $key meta key
     * @param bool $offset 偏移量
     * @param int $number 表示删除多少个
     * @return mixed
     */
    public function deleteMeta($id,$key,$offset = 0,$number = 1) {
        return M('user_meta')->where(array('uid' => $id,'key' => $key))->limit($offset,$number)->delete();
    }
}