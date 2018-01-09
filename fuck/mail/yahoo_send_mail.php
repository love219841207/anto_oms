<?php
require_once("../PHPMailer/PHPMailerAutoload.php");
require_once("../header.php");
require_once("../log.php");
//加大响应
ignore_user_abort(true);
set_time_limit(0); 
ini_set("memory_limit", "1024M"); 

// 远程请求（不获取内容）函数
function _curl($url,$store,$order_items,$u_name,$u_num,$mail_tpl) {
    $Data = array('store' => $store,'order_items' => $order_items,'u_name' => $u_name,'u_num' => $u_num, 'mail_tpl' => $mail_tpl);
    // echo $url;
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_TIMEOUT,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $Data);
    $result = curl_exec($ch);
    curl_close($ch);
    print_r($result);
}

// $mail->SMTPDebug = true; 
// $mail->SMTPDebug = 4;   
// 读取错误邮件
if(isset($_GET['read_error_mail'])){
    $sql = "SELECT * FROM mail_error";
    $res = $db->getAll($sql);
    echo json_encode($res);
}

// 雅虎发信
if(isset($_POST['send_mail'])){
    // rakuten发信
    $store = $_POST['store'];
    $mail_tpl = $_POST['mail_tpl'];
    $order_items = $_POST['my_checked_items'];

    // 发信
    // _curl('http://192.168.0.17:6620/fuck/mail/yahoo_back_mail.php',$store,$order_items,$u_name,$u_num,$mail_tpl);
    // _curl('http://www.oms.cc/fuck/mail/yahoo_back_mail.php',$store,$order_items,$u_name,$u_num,$mail_tpl);
    echo 'ok';
}