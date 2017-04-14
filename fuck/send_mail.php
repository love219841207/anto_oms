<?php
require_once("./PHPMailer/PHPMailerAutoload.php");
require_once("./header.php");

$mail = new PHPMailer;
$mail->CharSet = "UTF-8";
// $mail->SMTPDebug = true; 
// $mail->SMTPDebug = 4;    
if(isset($_POST['send_mail'])){
	if($_POST['send_mail'] == 'test_mail'){
		$station = 'conf_'.$_POST['station'];
		$store = $_POST['store'];
		$to_mail = $_POST['to_mail'];
		$model_name = $_POST['model_name'];

		//读取店铺配置
			$sql = "SELECT * FROM $station WHERE store_name = '{$store}'";
			$res = $db->getOne($sql);
			$mail_name = $res['mail_name'];
			$mail_id = $res['mail_id'];
			$mail_pwd = $res['mail_pwd'];
			$mail_smtp = $res['mail_smtp'];
			$mail_port = $res['mail_port'];
			$mail_answer_addr = $res['mail_answer_addr'];
			$mail_over_send = $res['mail_over_send'];

		if($model_name == 'tpl'){
			$id = $_POST['id'];
			//读取信件内容
			$sql = "SELECT * FROM mail_tpl WHERE id = '{$id}'";
			$res = $db->getOne($sql);
			$mail_topic = $res['mail_topic'];
			$mail_html = $res['mail_html'];
			$mail_txt = $res['mail_txt'];
		}else if($model_name == 'send_express'){
			//读取信件内容
			$sql = "SELECT * FROM mail_tpl WHERE store_name = '{$store}' and model_name = '{$model_name}'";
			$res = $db->getOne($sql);
			$mail_topic = $res['mail_topic'];
			$mail_html = $res['mail_html'];
			$mail_txt = $res['mail_txt'];
			//替换信件变量
			$mail_topic = str_replace('#购买人#', '测试员', $mail_topic);
			$mail_topic = str_replace('#快递单号#', '1234567890', $mail_topic);
			$mail_html = str_replace('#购买人#', '测试员', $mail_html);
			$mail_html = str_replace('#快递单号#', '1234567890', $mail_html);
		}

		//发送
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = $mail_smtp;  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // 开启SMTP验证
		$mail->Username = $mail_id;                 // SMTP 账号
		$mail->Password = $mail_pwd;                           // SMTP 密码
		$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = $mail_port;                                    // 邮件端口

		$mail->setFrom($mail_id, $mail_name);
		$mail->addAddress($to_mail, 'test');     // 收件人
		// $mail->addAddress('ellen@example.com');               // 另一个收件人
		$mail->addReplyTo($mail_answer_addr, '');	//邮件回复地址
		// $mail->addCC('cc@example.com');	//抄送
		// $mail->addBCC('ycmbcd@foxmail.com');	//秘密抄送

		// $mail->addAttachment('/var/tmp/file.tar.gz');         // 添加附件
		// $mail->addAttachment('/images/user_logo.jpg', 'user_logo.jpg');    // Optional name
		$mail->isHTML(true);                                  // Set email format to HTML

		$mail->Subject = $mail_topic;	//邮件标题
		$mail->Body    = $mail_html;	//邮件内容
		$mail->AltBody = $mail_txt;	//未设置HTML将会收到的内容

		if(!$mail->send()) {
		    echo '邮件发送失败.';
		    echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
		    echo 'sended';
		}
	}else{

	}
}