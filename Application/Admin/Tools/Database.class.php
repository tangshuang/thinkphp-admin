<?php

namespace Admin\Tools;
use Think\Db;

//数据导出模型
class Database {
    /**
     * 文件指针
     * @var resource
     */
    private $fp;

    /**
     * 备份文件信息 part - 卷号，name - 文件名
     * @var array
     */
    private $file;

    /**
     * 当前打开文件大小
     * @var integer
     */
    private $size = 0;

    /**
     * 备份配置
     * @var integer
     */
    private $config;

    /**
     * 数据库备份构造方法
     * @param array  $file   备份或还原的文件信息
     * @param array  $config 备份配置信息
     * @param string $type   执行类型，export - 备份数据， import - 还原数据
     */
    public function __construct($file, $config, $type = 'export'){
        $this->file   = $file;
        $this->config = $config;
    }

    /**
     * 打开一个卷，用于写入数据
     * @param  integer $size 写入数据的大小
     */
    /*
    private function open($size){
        if($this->fp){
            $this->size += $size;
            if($this->size > $this->config['part']){
                $this->config['compress'] ? @gzclose($this->fp) : @fclose($this->fp);
                $this->fp = null;
                $this->file['part']++;
                session('backup_file', $this->file);
                $this->create();
            }
        } else {
            $backuppath = $this->config['path'];
            $filename   = "{$backuppath}{$this->file['name']}-{$this->file['part']}.sql";
            if($this->config['compress']){
                $filename = "{$filename}.gz";
                $this->fp = @gzopen($filename, "a{$this->config['level']}");
            } else {
                $this->fp = @fopen($filename, 'a');
            }
            $this->size = filesize($filename) + $size;
        }
    }
    */

    /**
     * 重新开始一个文件，在必要的时候，通过调用本方法，可以实现半路拦截的作用，该方法之后的写入，将在新的文件中开始
     */
    /*
    private function reopen() {
        if($this->file['part'] <= 1) return; // 第一卷不做任何处理
        $this->config['compress'] ? @gzclose($this->fp) : @fclose($this->fp);
        $this->fp = null;
        $this->file['part']++;
        session('backup_file', $this->file);

        $this->open(0);

        $sql  = "-- -----------------------------\n";
        $sql .= "-- Think MySQL Data Transfer \n";
        $sql .= "-- \n";
        $sql .= "-- Host     : " . C('DB_HOST') . "\n";
        $sql .= "-- Port     : " . C('DB_PORT') . "\n";
        $sql .= "-- Database : " . C('DB_NAME') . "\n";
        $sql .= "-- \n";
        $sql .= "-- Part : #{$this->file['part']}\n";
        $sql .= "-- Date : " . date("Y-m-d H:i:s") . "\n";
        $sql .= "-- -----------------------------\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        return $this->config['compress'] ? @gzwrite($this->fp, $sql) : @fwrite($this->fp, $sql);
    }
    */

    /**
     * 写入SQL语句
     * @param  string $sql 要写入的SQL语句
     * @return boolean     true - 写入成功，false - 写入失败！
     */
    /*
    private function write($sql){
        $size = strlen($sql);

        //由于压缩原因，无法计算出压缩后的长度，这里假设压缩率为50%，
        //一般情况压缩率都会高于50%；
        $size = $this->config['compress'] ? $size / 2 : $size;

        $this->open($size);
        return $this->config['compress'] ? @gzwrite($this->fp, $sql) : @fwrite($this->fp, $sql);
    }
    */


    private function open() {
        $backuppath = $this->config['path'];
        $filename   = "{$backuppath}{$this->file['name']}-{$this->file['part']}.sql";
        if($this->config['compress']){
            $filename = "{$filename}.gz";
            $this->fp = @gzopen($filename, "a{$this->config['level']}");
        }
        else {
            $this->fp = @fopen($filename, 'a');
        }
        $this->size = filesize($filename) + $size;
        return $this->fp;
    }

    private function write($sql){
        return $this->config['compress'] ? @gzwrite($this->fp, $sql) : @fwrite($this->fp, $sql);
    }

    /**
     * 向原有的文件中继续添加sql
     * @param $sql
     * @param bool $new 是否另起一个文件
     * @return int
     */
    private function append($sql,$new = false) {
        $size = strlen($sql);
        $size = $this->config['compress'] ? $size / 2 : $size;
        $this->size += $size;

        // 判断是否打开文档
        if(!$this->fp) $this->open();

        // 当写入的内容大于规定的每卷大小时,重新打开一个新文档
        if(($this->config['part'] && $this->size > $this->config['part']) || $new) {
            $this->config['compress'] ? @gzclose($this->fp) : @fclose($this->fp);
            $this->file['part']++;
            session('backup_file', $this->file);
            $this->create();
        }

        return $this->write($sql);
    }



    /**
     * 写入初始数据
     * @return boolean true - 写入成功，false - 写入失败
     */
    public function create(){
        $sql  = "-- -----------------------------\n";
        $sql .= "-- Think MySQL Data Transfer \n";
        $sql .= "-- \n";
        $sql .= "-- Host     : " . C('DB_HOST') . "\n";
        $sql .= "-- Port     : " . C('DB_PORT') . "\n";
        $sql .= "-- Database : " . C('DB_NAME') . "\n";
        $sql .= "-- \n";
        $sql .= "-- Part : #{$this->file['part']}\n";
        $sql .= "-- Date : " . date("Y-m-d H:i:s") . "\n";
        $sql .= "-- -----------------------------\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        $this->open();
        return $this->write($sql);
    }

    /**
     * 备份表结构
     * @param  string  $table 表名
     * @param  integer $start 起始行数
     * @return boolean        false - 备份失败
     */
    public function backup($table, $start){
        //创建DB对象
        $db = Db::getInstance();
        //数据总数
        $result = $db->query("SELECT COUNT(*) AS count FROM `{$table}`");
        $count  = $result['0']['count'];

        //备份表结构
        if(0 == $start){
            $result = $db->query("SHOW CREATE TABLE `{$table}`");
            $sql  = "\n";
            $sql .= "-- -----------------------------\n";
            $sql .= "-- Table structure for `{$table}`\n";
            $sql .= "-- -----------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= trim($result[0]['create table']) . ";\n\n";
            if(false === $this->append($sql,$this->file['part'] > 1)){
                return false;
            }
            $this->append('',true); // 另起一个文件
        }
            
        //备份表数据
        if($count){

            //写入数据注释
            $sql  = "-- -----------------------------\n";
            $sql .= "-- Records of `{$table}`\n";
            $sql .= "-- -----------------------------\n";

            //备份数据记录
            $result = $db->query("SELECT * FROM `{$table}` LIMIT {$start}, 1000");
            foreach ($result as $row) {
                $row = array_map('addslashes', $row);
                $sql .= "INSERT INTO `{$table}` VALUES ('" . str_replace(array("\r","\n"),array('\r','\n'),implode("', '", $row)) . "');\n";
            }
            if(false === $this->append($sql)){
                return false;
            }

            //还有更多数据
            if($count > $start + 1000){
                return array($start + 1000, $count);
            }

        }

        //备份下一表
        return 0;
    }

    /**
     * 析构方法，用于关闭文件资源
     */
    public function __destruct(){
        $this->config['compress'] ? @gzclose($this->fp) : @fclose($this->fp);
    }
}