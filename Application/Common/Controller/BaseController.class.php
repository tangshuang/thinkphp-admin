<?php

namespace Common\Controller;
use Think\Controller;

class BaseController extends Controller
{
    public function _initialize()
    {
        $this->CONFIG();
    }

    protected function CONFIG() {
        // 读取数据库中的配置
        $config = S('DB_CONFIG_DATA'); // 从缓存中读取
        if(!$config){ // 如果不存在缓存，则再去读取数据库
            $config = D('Config')->lists();
            S('DB_CONFIG_DATA',$config); // 缓存起来
        }
        C($config); //添加配置
    }

    /**
     * 上传多张图片，在其他的Controller中，使用$result = $this->uploadPictures();即可监听图片上传，并且得到需要的上传结果
     * @param bool $is_thumb
     * @param bool $is_water
     * @return bool
     */
    protected function uploadPictures($is_thumb = true,$is_water = false)
    {

        $Picture = D('Picture');
        $files = $Picture->upload($_FILES,C('PICTURE_UPLOAD'));

        if(!$files) {
            $this->error = $Picture->getError();
            return false;
        }

        foreach($files as &$file) {
            $uri = $file['url'];
            $path = STATIC_PATH.$file['path'];
            $url = STATIC_URL.$uri;

            // 下面开始对上传好的图片进行文件处理，例如图片裁剪、压缩和水印
            $file['thumbs'] = array();
            if($is_thumb) {
                $thumbs = $Picture->thumbs($file,true);
                if($thumbs) foreach($thumbs as $size => $thumb) {
                    $ext = substr(strrchr($url,'.'),0);
                    $thumb = substr($url,0,strrpos($url,$ext))."_$size$ext";
                    $file['thumbs'][$size] = $thumb;
                }
            }

            // 水印
            if($is_water) {
                $Picture->water($path,80,$file['id']); // 给原图打水印
                if(!empty($file['thumbs'])) foreach($file['thumbs'] as $size => $thumb) {
                    $ext = substr(strrchr($url,'.'),0);
                    $thumb = substr($path,0,strrpos($path,$ext))."_$size$ext";
                    $Picture->water($thumb,80,$file['id']);
                }
            }

            // 在这里生成图片的url，方便前台上传后马上直接显示图片
            $file['path'] = $path; // 绝对路径
            $file['url'] = $url; // 绝对URL
            $file['uri'] = $uri;
        }

        return $files;
    }

    /**
     * 上传单张图片
     * @param string $field
     * @param bool $is_thumb
     * @param bool $is_water
     * @return bool
     */
    protected function uploadPicture($field = 'file',$is_thumb = true,$is_water = false)
    {
        $Picture = D('Picture');
        $file = $Picture->uploadOne($_FILES[$field],C('PICTURE_UPLOAD'));

        if(!$file) {
            $this->error = $Picture->getError();
            return false;
        }

        $uri = $file['url'];
        $path = STATIC_PATH.$file['path'];
        $url = STATIC_URL.$uri;

        $file['thumbs'] = array();
        if($is_thumb) {
            $thumbs = $Picture->thumbs($file,true);
            if($thumbs) foreach($thumbs as $size => $thumb) {
                $ext = substr(strrchr($url,'.'),0);
                $thumb = substr($url,0,strrpos($url,$ext))."_$size$ext";
                $file['thumbs'][$size] = $thumb;
            }
        }

        if($is_water) {
            $Picture->water($path,80,$file['id']); // 给原图打水印
            if(!empty($file['thumbs'])) foreach($file['thumbs'] as $size => $thumb) {
                $ext = substr(strrchr($url,'.'),0);
                $thumb = substr($path,0,strrpos($path,$ext))."_$size$ext";
                $Picture->water($thumb,80,$file['id']);
            }
        }

        // 在这里生成图片的url，方便前台上传后马上直接显示图片
        $file['path'] = $path; // 绝对路径
        $file['url'] = $url; // 绝对URL
        $file['uri'] = $uri;

        return $file;
    }

    /**
     * 通用分页列表数据集获取方法
     *
     *  可以通过url参数传递where条件,例如:  index.html?name=asdfasdfasdfddds
     *  可以通过url空值排序字段和方式,例如: index.html?_field=id&_order=asc
     *  可以通过url参数r指定每页数据条数,例如: index.html?r=5
     *
     * @param object $model   模型名或模型实例
     * @param array        $where   where查询条件(优先级: $where>$_REQUEST>模型设定)
     * @param array|string $order   排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
     *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
     *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
     *
     * @param boolean      $field   单表模型用不到该参数,要用在多表join时为field()方法指定参数
     *
     * @return array|false
     * 返回数据集
     */
    protected function lists($model,$where=array(),$order='',$field=true){
        $options    =   array();
        $REQUEST    =   (array)I('request.');
        if(is_string($model)){
            $model  =   D($model);
        }

        $OPT        =   new \ReflectionProperty($model,'options');
        $OPT->setAccessible(true);

        $pk         =   $model->getPk();
        if($order===null){
            //order置空
        }else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
            $options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
        }elseif( $order==='' && empty($options['order']) && !empty($pk) ){
            $options['order'] = $pk.' desc';
        }elseif($order){
            $options['order'] = $order;
        }
        unset($REQUEST['_order'],$REQUEST['_field']);

        /*
        if(empty($where)){
            $where  =   array('status'=>array('egt',0));
        }
        */
        if( !empty($where)){
            $options['where']   =   $where;
        }
        $options      =   array_merge( (array)$OPT->getValue($model), $options );
        $total        =   $model->where($options['where'])->count();

        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 20;
        }
        $page = new \Think\Page($total, $listRows, $REQUEST);
        if($total>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p =$page->show();
        $this->assign('_page', $p? $p: '');
        $this->assign('_total',$total);
        $options['limit'] = $page->firstRow.','.$page->listRows;

        $model->setProperty('options',$options);

        return $model->field($field)->select();
    }

    public function _empty($action) {
        R('Empty/_empty');
    }
}