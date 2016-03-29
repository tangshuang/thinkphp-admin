<?php

namespace Common\Model;
use Think\Model;

class ConfigModel extends Model
{
    protected $_validate = array(
        array('name', 'require', '配置标识不能为空', Model::EXISTS_VALIDATE, 'regex', Model::MODEL_BOTH),
        array('name', '', '配置标识已经存在', Model::VALUE_VALIDATE, 'unique', Model::MODEL_BOTH),
        array('remark', 'require', '配置说明不能为空', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
        array('value', 'require', '配置值不能为空', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
    );

    protected $_auto = array(
        array('name', 'strtoupper', Model::MODEL_BOTH, 'function'),
    );

    public function lists() {
        $data   = $this->field('type,name,value')->select();
        $config = array();
        if($data && is_array($data)){
            foreach ($data as $value) {
                $config[$value['name']] = $this->parse($value['type'], $value['value']);
            }
        }
        return $config;
    }

    private function parse($type, $value)
    {
        switch ($type) {
            case 3: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if(strpos($value,':')){
                    $value  = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k]   = $v;
                    }
                }else{
                    $value =    $array;
                }
                break;
        }
        return $value;
    }

}
