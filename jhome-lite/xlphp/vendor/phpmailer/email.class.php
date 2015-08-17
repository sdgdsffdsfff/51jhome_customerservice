<?php

/*
 * email.class 邮件发送类
 */
define('EMAIL_PATH', dirname(__FILE__));
require EMAIL_PATH . '/class.phpmailer.php';
require EMAIL_PATH . '/class.smtp.php';

function setEmail($ArrAddress = array(), $title = '', $content = '', $attach = array(), $replace = false) {
    $sned_err = array();
    $sys = C('email');
    $mail = new PHPMailer(); //建立邮件发送类
    $mail->IsSMTP(); // 使用SMTP方式发送
    $mail->IsHTML(true); // set email format to HTML //是否使用HTML格式
    $mail->CharSet = 'utf-8'; // 指定字符集
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $mail->SMTPDebug = false;
    $mail->SMTPKeepAlive = true;
    $mail->Host = $sys['host']; // 您的企业邮局域名
    $mail->SMTPAuth = $sys['ssl']; // 启用SMTP验证功能
    $mail->Username = $sys['mail']; // 邮局用户名(请填写完整的email地址)
    $mail->Password = $sys['password']; // 邮局密码
    $mail->Port = $sys['port'];
    $mail->From = $sys['mail']; //邮件发送者email地址 
    $mail->FromName = $sys['sender'];
//    $mail->AddReplyTo("", "");
    if (!empty($attach)) {
        foreach ($attach as $key => $val) {
            $mail->AddAttachment($val, $key); //添加附件
        }
    }
    if (!is_array($ArrAddress)) {
        $ArrAddress = array($ArrAddress => $ArrAddress);
    }
    foreach ($ArrAddress as $key => $val) {
        $mail->AddAddress($val, $key); //("收件人email","收件人姓名")
        if ($replace) {
            $title = replace_mailname($title, $key); //替换收件人（标题）
            $content = replace_mailname($content, $key); //（内容）
        }
        $mail->Subject = $title; //邮件标题
        $mail->Body = $content; //邮件内容
        if (!$mail->Send()) {
            $sned_err[$key] = $val;
            //echo "邮件发送失败. <p>错误原因: ",$mail->ErrorInfo,'</p>';
        }
        $mail->ClearAddresses(); //清除收件人
    }
    $mail->SmtpClose(); //关闭smtp链接
    if ($sned_err) {
        return array('status' => 'error', 'err' => $sned_err);
    }
    return array('status' => 'ok', 'err' => '');
}

function replace_mailname($str = '', $new = '', $old = '{$name$}') {
    return str_replace($old, $new, $str);
}

?>
