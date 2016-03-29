<?php

namespace Common\Model;
use Think\Model;

class AdminMenuModel extends Model {
    protected $_validate = array(
        array('title','require', '必须填写菜单名称。', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH),
        array('url','require', '必须填写菜单路径。', Model::MUST_VALIDATE , 'regex', Model::MODEL_BOTH)
    );

    public function all($order = 'desc') {
        $menus = $this->order("sort $order,id asc")->select();
        $results = array();
        foreach($menus as &$menu) {
            $results[$menu['id']] = $menu;
        }
        return $results;
    }
    public function tree($order = 'desc') {
        $menus = $this->all($order);

        if(!$menus)
            return false;


        $menus = array_tree($menus,'pid');
        $menus = array_orderby($menus,'sort',$order,'children');
        return $menus;
    }
    public function ancestry($id) {
        $menus = $this->all();
        $ancestry_ids = $this->ancestry_ids($id);
        $ancestry = array();
        foreach($ancestry_ids as $menu_id) {
            $ancestry[] = $menus[$menu_id];
        }

        return $ancestry;
    }
    public function ancestry_ids($id) {
        $menus = $this->all();

        if(!$menus)
            return false;

        $ancestry_ids = array();
        $menu = $menus[$id];
        array_unshift($ancestry_ids,$menu['id']);
        while($menu['pid'] && $menu['pid'] != 0) { // TODO: 如果存在某个菜单找不到顶级菜单为pid=0的怎么办？
            $menu = $menus[$menu['pid']];
            array_unshift($ancestry_ids,$menu['id']);
        }

        return $ancestry_ids;
    }
    public function root($id)
    {
        $ancestry = $this->ancestry($id);

        if(!$ancestry)
            return false;

        $root = current($ancestry);
        return $root;
    }
    public function root_id($id)
    {
        $ancestry = $this->ancestry_ids($id);

        if(!$ancestry)
            return false;

        $root = current($ancestry);
        return $root;
    }

}