<?php

namespace Admin\Model;
use Think\Model;

class AdminRuleModel extends Model {
    protected $_validate = array(
        array('title','require', '必须填写规则名称。', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
        array('url','require', '必须填写规则路径。', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
        array('md5','require', '必须生成规路径MD5。', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
        array('md5', '', '该规则已经存在。', Model::EXISTS_VALIDATE, 'unique')
    );
}