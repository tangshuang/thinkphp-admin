<?php

namespace Admin\Tools;

//数据导出模型
class Email {
    public function send($to, $title, $content) {
        Vendor('PHPMailer.PHPMailerAutoload');
        $mail = new PHPMailer(); //实例化
        $mail->IsSMTP(); // 启用SMTP
        $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
        $mail->SMTPAuth = true; //启用smtp认证
        $mail->Username = C('MAIL_USERNAME'); //你的邮箱名
        $mail->Password = C('MAIL_PASSWORD') ; //邮箱密码
        $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
        $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
        $mail->AddAddress($to,"尊敬的客户");
        $mail->WordWrap = 50; //设置每行字符长度
        $mail->IsHTML(true); // 是否HTML格式邮件
        $mail->CharSet= 'utf-8'; //设置邮件编码
        $mail->Subject =$title; //邮件主题
        $mail->Body = $content; //邮件内容
        $mail->AltBody = "你的邮箱不支持HTML显示。"; //邮件正文不支持HTML的备用显示
        return($mail->Send());
    }
复制代码

}