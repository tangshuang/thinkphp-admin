<?php

/**
 * 图片上传模型，实现图片的上传，所有上传的图片将会通过本模型保存到数据库_picture表中
 */

namespace Common\Model;
use Think\Upload;
use Think\Image;

class PictureModel extends \Think\Model
{
    /**
     * 自动完成
     * @var array
     */
    protected $_auto = array(
        array('status', 1, self::MODEL_INSERT),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 检测当前上传的文件是否已经存在，如果存在，就不再执行上传动作，而且通过执行本函数返回文件数组
     * @param  array   $file 文件上传数组
     * @return boolean       文件信息， false - 不存在该文件
     */
    public function isFile($file)
    {
        $map = array('md5' => $file['md5'],'sha1'=>$file['sha1']);
        return $this->field(true)->where($map)->find();
    }

    /**
     * 这个函数是在isFile函数的基础上才会执行，如果检测当前上传的文件不存在，则删除数据库中该文件的记录
     * @param $data array $data是由isFile返回的数组
     */
    public function removeTrash($data)
    {
        $this->where(array('id'=>$data['id']))->delete();
    }

    /**
     * 文件上传
     * @param  array  $files   要上传的文件列表（通常是$_FILES数组）
     * @param  array  $setting 文件上传配置
     * @param  string $driver  上传驱动名称
     * @param  array  $config  上传驱动配置
     * @return array           文件上传成功后的信息
     */
    public function upload($files,$setting){
        $setting['callback'] = array($this,'isFile');
        $setting['removeTrash'] = array($this,'removeTrash');
        $Upload = new Upload($setting,'LOCAL',null);
        $files = $Upload->upload($files);

        if(!$files) {
            $this->error = $Upload->getError();
            return false;
        }

        foreach($files as $key => $file) {
            if(isset($file['id'])) // 如果是直接从数据库中返回的结果的话，就无需再往下执行了
                continue;

            /**
             * 记录文件信息，因为这里要直接记录到数据库里面去，所以，为了复用方便，不能直接加入root路径，而是只记录URI
             */
            $root_path = substr($setting['rootPath'],1);// 因为config中的$setting['rootPath']格式为'./Uploads/Picture/'，要把前面的.去掉
            $picture_path = $root_path.$file['savepath'].$file['savename'];
            $file['path'] = $picture_path;
            $file['url'] = $picture_path;

            /**
             *  添加数据库记录
             */
            $file = $this->create($file,self::MODEL_INSERT);
            if($file) {
                $file['id'] = $this->data($file)->add();
            }

            $files[$key] = $file;
        }

        return $files;
    }

    public function uploadOne($file,$setting) {
        $setting['callback'] = array($this,'isFile');
        $setting['removeTrash'] = array($this,'removeTrash');
        $Upload = new Upload($setting,C('PICTURE_UPLOAD_DRIVER'),C('UPLOAD_DRIVER_'.C('PICTURE_UPLOAD_DRIVER').'_CONFIG'));
        $file = $Upload->uploadOne($file); // 如果已经存在该hash的文件了，就直接返回数据库信息，而不是文件信息

        if(!$file) {
            $this->error = $Upload->getError();
            return false;
        }

        if(isset($file['id'])) // 如果是直接从数据库中返回的结果的话，就无需再往下执行了
            return $file;

        $root_path = substr($setting['rootPath'],1);
        $picture_path = $root_path.$file['savepath'].$file['savename'];
        $file['path'] = $picture_path;
        $file['url'] = $picture_path;

        $file = $this->create($file,self::MODEL_INSERT);
        if(!$file) {
            $this->error = $Upload->getError();
            return false;
        }

        $file['id'] = $this->data($file)->add();
        if($file['id'] === false) {
            return false;
        }

        return $file;
    }

    /**
     * 裁剪图片为不同的尺寸
     * 原始尺寸已原有的名称保存，不动它
     * 大尺寸图片末尾加_l
     * 中尺寸末尾加_m
     * 小尺寸末尾加_s
     * 最终的图片类似“1sw1243sfwe_s.jpg”这样子
     * @param $path 要处理图片的路径
     * @param $corn 图片是否进行裁剪，如果为true则进行裁剪，结果图片展示的内容可能小于原图片的内容，但这样能够保证图片的长宽比，如果不进行裁剪，那么会按照图片的宽度进行缩小（注意，高度不进行控制，因此不要使用高度太大的图片）
     * 图片处理的尺寸在网站后台进行设置，后台设置的时候，设置一个数组，其中的key不一定非得是上面指出的l,m,s，也可以是其他值，这个时候只需要调用你自己设定的值即可，例如你添加了一个o => 2000x1000，那么可以用“osdfwoe234_o.jpg”来调用这个尺寸的图片，但是不能使用w因为w是用来表示水印的（原图加水印）
     * array(key => size)
     * 类似如 array('m' => '600x300')
     * @author 否子戈 <frustigor@qq.com>
     */
    public function thumbs($file,$corn = true){
        if(!isset($file['id'])) {
            $this->error = '图片信息不完整';
            return false;
        }

        $path = STATIC_PATH.$file['path'];
        if(!file_exists($path)) {
            $this->error = '原始图片不存在';
            return false;
        }

        $sizes = C('PICTURE_THUMBNAIL_SIZES');
        if(!$sizes || !is_array($sizes)) {
            $this->error = '尺寸设置有问题';
            return false;
        }

        $ext = substr(strrchr($path,'.'),0);
        $image = new Image();
        $image->open($path);
        $imageWidth = $image->width(); // 返回图片的宽度
        $imageHeight = $image->height(); // 返回图片的高度
        $images = array();
        $thumbs = array();
        foreach($sizes as $end => $size) {
            $this_path = str_replace($ext,"_$end$ext",$path);
            if(strpos($size,'x') === false){
                $width = $size;
                $height = $size;
            }
            else{
                list($width,$height) = explode('x',$size);
            }

            if(!is_numeric($width) || !is_numeric($height))
                continue;

            // 如果图片的尺寸不足要缩小的尺寸，那么这张缩略图就不生成了
            if($imageHeight < $height && $imageWidth < $width && $end != 'thumbnail')
                continue;
            // 如果图片宽度或高度小于规定的尺寸，裁剪的情况下因为不满足，就没有必要生成
            elseif(($imageWidth < $width || $imageHeight < $height) && $corn && $end != 'thumbnail')
                continue;
            // 如果要求裁剪
            if($corn)
                $image->thumb($width,$height,Image::IMAGE_THUMB_CENTER);
            else
                $image->thumb($width,$height);

            $image->save($this_path);
            $images[$end] = $this_path;
            $thumbs[] = $end;
        }

        $this->startTrans();
        $result = $this->where(array('id' => $file['id']))->setField('thumbs',join(',',$thumbs));
        if($result === false) {
            $this->rollback();
        }
        $this->commit();

        return $images;
    }

    /**
     * 给图片添加水印
     * @param $path 要添加水印的图片路径，相对于THINKPHP系统的位置
     * @param $opacity 透明度，1为不透明，0为完全透明
     * @param $cover 是否覆盖原图，如果为true，则直接覆盖原图，因此要谨慎操作
     * 水印图片的路径和在图片中的位置，都通过网站后台来设置 http://document.thinkphp.cn/manual_3_2.html#image
     * @author 否子戈 <furstigor@qq.com>
     */
    public function water($path,$opacity = 80,$file_id = false){
        $water = SYSTEM_PATH.'/'.C('PICTURE_WATER_PATH');
        if(!file_exists($water)) {
            $this->error = '缺少水印文件';
            return false;
        }

        if(!file_exists($path)) {
            $this->error = '原始图片不存在';
            return false;
        }

        $image = new Image();
        $image->open($path)->water($water,Image::IMAGE_WATER_SOUTHEAST,$opacity);

        $result = $image->save($path);
        if(!$result) {
            $this->error = '给图片打水印失败';
            return false;
        }

        if($file_id) {
            $new_md5 = md5_file($path);
            $new_sha1 = sha1_file($path);

            $this->startTrans();
            $result = $this->where(array('id' => $file_id))->setField(array('md5' => $new_md5,'sha1' => $new_sha1));
            if($result === false) {
                $this->rollback();
            }
            $this->commit();
        }

        return true;
    }



    public function crop($path,$x,$y,$width,$height,$file_id = false) {
        if(!file_exists($path)) {
            $this->error = '图片文件不存在';
            return false;
        }

        $image = new Image();
        $image->open($path);

        $result = $image->crop($width,$height,$x,$y)->save($path);
        if(!$result) {
            $this->error = '图片裁剪失败';
            return false;
        }

        if($file_id) {
            $new_md5 = md5_file($path);
            $new_sha1 = sha1_file($path);

            $this->startTrans();
            $result = $this->where(array('id' => $file_id))->setField(array('md5' => $new_md5,'sha1' => $new_sha1));
            if($result === false) {
                $this->rollback();
            }
            $this->commit();
        }

        return true;
    }

    /**
     * 重新调整图片尺寸，比如一张800x600的图片，可以通过这个方法调整为400x300的尺寸
     * 之所以使用max_width，max_height这样的参数，是因为我们设置一个缩小后尺寸的最大值，图片缩小后都会在max_width x max_height这样的一个区域内展示完
     * @param $path
     * @param $max_width
     * @param bool $max_height
     */
    public function resize($path,$max_width = 1200,$max_height = 1200,$file_id = false){
        if(!file_exists($path)) {
            $this->error = '图片文件不存在';
            return false;
        }

        $image = new Image();
        $image->open($path);
        $image->thumb($max_width,$max_height);
        $result = $image->save($path);
        if(!$result) {
            $this->error = '图片缩放失败';
            return false;
        }

        if($file_id) {
            $new_md5 = md5_file($path);
            $new_sha1 = sha1_file($path);

            $this->startTrans();
            $result = $this->where(array('id' => $file_id))->setField(array('md5' => $new_md5,'sha1' => $new_sha1));
            if($result === false) {
                $this->rollback();
            }
            $this->commit();
        }

        return true;
    }

}