<?php

/**
 * 后台基础控制器，之后所有的控制器都在必要时扩展本控制器，无法直接访问本控制器
 * 本控制器可以实现菜单拉取、权限验证，同时提供一些操作方法，可以方便的进行一些操作
 */

namespace Admin\Controller;
use Common\Controller\BaseController;

abstract class __Controller extends BaseController {

    protected $administrator;

    // 下面这三项可在各自的控制器中使用_before_index这样的方法进行设定
    protected $model = false;
    protected $form = null;
    protected $template = false;
    protected $lists = null;
    protected $redirect;

    public function _initialize() {
        parent::_initialize();

        /**
         * 第一步，记录administrator信息
         */
        $this->administrator = session('administrator');
        if(!$this->administrator && cookie('administrator_id')) {
            $administrator_id = cookie('administrator_id');
            $AdministratorModel = D('Administrator');
            $administrator = $AdministratorModel->where(array('id' => $administrator_id))->find();
            session('is_login',1);
            cookie('administrator_id',$administrator_id);
            session('administrator',$administrator);
            $this->administrator = $administrator;
        }

        if(!is_login())
            $this->redirect('Login/index');

        $this->assign('administrator',$this->administrator);

        /**
         * 第二步，获取当前url的path,query信息
         */
        $query = I('get.');
        $path = U(CONTROLLER_NAME.'/'.ACTION_NAME);
        $root = $_SERVER['DOCUMENT_ROOT'];
        $base = SITE_PATH;
        $subdir = str_replace($root,'',$base);
        if($subdir !== '' && strpos($url,$subdir) === 0) {
            $path = substr($path,strlen($subdir)); // 如果thinkphp安装在子目录中，可以识别子目录，并只取url中非子目录部分
        }
        $path = md5_16($path);

        // 找出当前url对应的menu_id
        $menu_id = 0;
        $menu_ids = array();
        $AdminMenuModel = D('AdminMenu');
        $menus = S('ADMIN_MENUS_LIST');
        if(!$menus) {
            $menus = $AdminMenuModel->select();
            S('ADMIN_MENUS_LIST',$menus);
        }
        if($menus) {
            foreach($menus as $menu) {
                if($menu['path'] == $path) {
                    if($menu['query'] == '' && empty($query)) { // 如果存在都为空的情况
                        $menu_ids[] = $menu['id'];
                    }
                    elseif($menu['query'] == '' && !empty($query)) {
                        $menu_ids[] = $menu['id'];
                    }
                    elseif($menu['query'] != '' && empty($query)) {}
                    else {
                        $flag = false;
                        parse_str($menu['query'],$querys);
                        foreach($querys as $key => $value) {
                            if(isset($query[$key]) && $query[$key] == $value) {
                                $flag = true;
                            }
                            else {
                                $flag = false;
                                break;
                            }
                        }
                        if($flag) {
                            $menu_ids[] = $menu['id'];
                        }
                    }
                }
            }
            // 由于有相同md5的菜单可能不止一个，一般顶级菜单都是和子菜单一样的，因此要找出真正的当前菜单
            if (count($menu_ids) > 1) { // 当前md5找到了多个菜单，因此要找到他们之间的层级关系，通过层级关系找到真正的菜单
                $parent_ids = array();
                foreach ($menu_ids as $id) {
                    $menu = $menus[$id];
                    $parent_ids[] = $menu['pid'];
                }
                foreach ($menu_ids as $id) {
                    if (!in_array($id, $parent_ids)) {
                        $menu_id = $id;
                    }
                }
            }
            elseif (count($menu_ids) == 1 && $menus[$menu_ids[0]]['pid'] == 0) { // 顶级菜单
                $menu_id = $menu_ids[0];
            }
            elseif (count($menus) == 1) { // 非顶级菜单，但只有一个
                $menu_id = $menu_ids[0];
            }
            else { // 没有找到菜单，该url不在菜单范围内
            }
        }
        unset($menu_ids);

        /**
         * 第三步，获取当前登陆用户所在组的权限，及权限判定
         */

        // 获取当前用户所在组的权限规则列表
        $auths = M('AdministratorGroup')->where(array('id' => $this->administrator['group_id']))->getField('rules');
        $auths = $auths ? explode(',',$auths) : array();
        if(!is_administrator() && !in_array($menu_id,$auths)) $this->error('权限不足。');

        /**
         * 第四步：设置显示菜单
         */

        $_MENU_ = S('ADMIN_MENU_'.$this->administrator['group_id'].'_'.$menu_id);
        if(!$_MENU_) {
            // 当前菜单
            $menu_tree = array_tree($menus,'pid');
            $menu_tree = array_orderby($menu_tree,'sort','desc','children');
            $nav_roots = array();
            if($menu_tree) foreach($menu_tree as $item) {
                if(isset($item['children'])) unset($item['children']);
                $nav_roots[] = $item;
            }
            if ($menu_id) {
                $menu_ancestry_ids = array_ancestry($menu_id,$menus,'pid'); // 当前菜单的层级关系
                $menu_root_id = current($menu_ancestry_ids);
                $nav_children = $menu_tree[$menu_root_id]['children']; // 当前菜单的边侧菜单
            } else {
                $menu_ancestry_ids = null;
                $menu_root_id = 0;
                $nav_children = null;
            }

            // 顶级菜单是否显示：检查用户对菜单是否拥有权限，如果对菜单没有权限的话，要将菜单隐藏
            if (!empty($nav_roots)) foreach ($nav_roots as $key => $nav) {
                if ($nav['hide'] == 1) {
                    unset($nav_roots[$key]);
                    continue;
                }
                if (!in_array($nav['id'], $auths)) {
                    if (is_administrator() && (strpos($nav['url'],'Config/index') === 0 || strpos($nav['url'],'Administrator/index') === 0))
                        continue;
                    unset($nav_roots[$key]);
                }
            }

            // 边栏菜单是否显示
            if (!empty($nav_children)) foreach ($nav_children as &$nav) {
                if ($nav['hide'] == 1) {
                    unset($nav);
                    continue;
                }

                // 三级菜单
                if (isset($nav['children'])) foreach ($nav['children'] as $key => $sub_nav) {
                    if ($sub_nav['hide'] == 1) {
                        if ($sub_nav['id'] == $menu_id) $menu_id = $nav['id']; // 如果第三级菜单隐藏，则把菜单高亮传递给上一级菜单
                        unset($nav['children'][$key]);
                        if (count($nav['children']) == 0) unset($nav['children']);
                        continue;
                    }
                    if (!in_array($sub_nav['id'], $auths)) {
                        if (is_administrator())
                            continue;
                        unset($nav['children'][$key]);
                        if (count($nav['children']) == 0) unset($nav['children']);
                    }
                }

                if (!in_array($nav['id'], $auths)) {
                    if (is_administrator())
                        continue;
                    unset($nav);
                }
            }

            $_MENU_ = array(
                'nav_roots' => $nav_roots,
                'nav_children' => $nav_children,
                'current_id' => $menu_id,
                'current_ancestry' => $menu_ancestry_ids,
                'current_root' => $menu_root_id
            );
            S('ADMIN_MENU_'.$this->administrator['group_id'].'_'.$menu_id, $_MENU_);
        }

        $this->assign('_MENU_', $_MENU_);
        unset($_MENU_);

        /**
         * 第五步：设置当前控制器的默认模型this->model
         */

        // 获取当前数据库的所有表
        $tables = S('DB_TABLES');
        if(!$tables) {
            $convert = function($str) {
                while(($pos = strpos($str,'_'))!==false)
                    $str = substr($str,0,$pos).ucfirst(substr($str,$pos+1));
                return ucfirst($str);
            };
            $tables = M()->query('show tables');
            foreach($tables as $key => $table) {
                $table = $table['tables_in_'.DB_NAME];
                $table = str_replace(DB_PREFIX,'',$table);
                $table = $convert($table);
                $tables[$key] = $table;
            }
            S('DB_TABLES',$tables);
        }

        // 初始化当前主模型
        $this->model = $this->model ? $this->model : CONTROLLER_NAME;
        if(in_array($this->model,$tables)) {
            $this->model = D($this->model);
        }

    }

    protected function index() {
        $list = $this->lists($this->model,$this->lists['where'],$this->lists['order'],$this->lists['field']);
        $this->assign('_list_',$list);
        $this->display('index');
    }

    protected function add() {
        if(IS_POST) {
            if(!$this->model->create())
                $this->error($this->model->getError());
            $result = $this->model->add();
            if(!$result)
                $this->error($this->model->getError());
            $this->success('添加成功。',$this->redirect ? $this->redirect : '');
        }
        else {
            $form = $this->form;
            $this->assign('_fields_',$form);
            $this->display($this->template ? $this->template : 'Base/form');
        }
    }

    protected function edit($id) {
        if(IS_POST) {
            if(!$this->model->create())
                $this->error($this->model->getError());
            $result = $this->model->where(array('id' => $id))->save();
            if($result === false)
                $this->error($this->model->getError());
            $this->success('修改成功。',$this->redirect ? $this->redirect : '');
        }
        else {
            $item = $this->model->where(array('id' => $id))->find();
            if(!$item)
                $this->error('要编辑的内容不存在。');
            $form = $this->form;
            foreach($item as $key => $value) {
                if(!isset($form[$key]))
                    continue;
                $form[$key]['value'] = $value;
            }
            $form['id'] = array(
                'type' => 'hidden',
                'name' => 'id',
                'value' => $id
            );

            $this->assign('_fields_',$form);
            $this->display($this->template ? $this->template : 'Base/form');
        }
    }

    protected function delete($id) {
        $this->model->where(array('id' => $id))->delete();
        $this->success('删除成功。');
    }

    protected function status($id,$value) {
        $result = $this->model->where(array('id' => $id))->setField('status',$value);
        if($result === false)
            $this->error($this->model->getError());
        $this->success('状态调整成功。');
    }

    protected function sort($id,$value) {
        $result = $this->model->where(array('id' => $id))->setField('sort',$value);
        if($result === false)
            $this->error($this->model->getError());
        $this->success('排序调整成功。');
    }
}
