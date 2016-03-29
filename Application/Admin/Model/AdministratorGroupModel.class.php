<?php

namespace Admin\Model;
use Think\Model;

/**
 * 后台管理员模型
 */
class AdministratorGroupModel extends Model {
    // 用户模型自动验证
    protected $_validate = array(
        array('title','require','组名必须填写。',Model::EXISTS_VALIDATE),
        array('slug','require','别名必须填写。',Model::EXISTS_VALIDATE),
        array('slug','','别名已被占用。',Model::EXISTS_VALIDATE,'unique')
    );
}