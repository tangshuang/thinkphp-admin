<?php

namespace Admin\Model;
use Think\Model;
use Admin\Tools\Email;

class EmailModel extends Model {
    protected $_validate = array(
        array('email','require', '必须填写接受者邮箱。', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
        array('title','require', '必须填写邮件标题。', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
        array('content','require', '必须填写邮件内容。', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
    );

    protected $_auto = array(
        array('create_time',NOW_DATETIME, Model::MODEL_INSERT),
    );

    public function _after_insert($data,$options){
        $EmailTool = new Email();
        $EmailTool->send($data->emial,$data->title,$data->content);
    }

}