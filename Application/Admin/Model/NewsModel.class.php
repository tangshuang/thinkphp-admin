<?php

namespace Admin\Model;
use Think\Model;

class NewsModel extends Model {
    protected $_validate = array(
        array('title','require', '必须填写资讯标题。', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
        array('cover_image_id','number', '必须上传资讯封面图。', Model::MUST_VALIDATE , '', Model::MODEL_BOTH),
        array('excerpt','10,150', '资讯摘要必须在10-150字之间。', Model::MUST_VALIDATE , 'length', Model::MODEL_BOTH),
        array('detail','require', '必须填写资讯内容。', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
        array('category_id','number', '必须选择资讯分类。', Model::MUST_VALIDATE , '', Model::MODEL_BOTH),
    );

    protected $_auto = array(
        array('create_time',NOW_DATETIME, Model::MODEL_INSERT),
    );
}