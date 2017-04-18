<?php
require_once("./PHPMailer/PHPMailerAutoload.php");
require_once("./header.php");


// $mail->SMTPDebug = true; 
// $mail->SMTPDebug = 4;    

if(isset($_POST['send_mail'])){
	// amazon发信
	if($_POST['send_mail'] == 'amazon'){
		$station = 'conf_'.$_POST['station'];
		$store = $_POST['store'];
		$mail_tpl = $_POST['mail_tpl'];
		$order_items = $_POST['order_items'];
		$order_items = str_replace('\'', '', $order_items);// 去掉order_items引号

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

		// 遍历amazon_order_id
		$order_ids = explode(',', $order_items);
		foreach ($order_ids as $value) {
			//读取信件内容
			$sql = "SELECT * FROM mail_tpl WHERE id = '{$mail_tpl}'";
			$res = $db->getOne($sql);
			$mail_topic = $res['mail_topic'];
			$mail_html = $res['mail_html'];
			$mail_txt = $res['mail_txt'];

			//读取邮箱、收件人等信息
			$sql = "SELECT * FROM amazon_response_list WHERE amazon_order_id = '{$value}'";
			$res = $db->getOne($sql);

			$to_mail = $res['buyer_email'];	#邮箱
		 	$buyer_name = $res['buyer_name'];	#购买人
		 	$oms_order_express_num = $res['oms_order_express_num'];	#单号

			//替换信件变量
			$mail_topic = str_replace('#购买人#', $buyer_name, $mail_topic);
			$mail_topic = str_replace('#快递单号#', $oms_order_express_num, $mail_topic);
			$mail_html = str_replace('#购买人#', $buyer_name, $mail_html);
			$mail_html = str_replace('#快递单号#', $oms_order_express_num, $mail_html);

			//发送
			$mail = new PHPMailer;
			$mail->CharSet = "UTF-8";
			$mail->isSMTP();                                      // Set mailer to use SMTP
			$mail->Host = $mail_smtp;  // Specify main and backup SMTP servers
			$mail->SMTPAuth = true;                               // 开启SMTP验证
			$mail->Username = $mail_id;                 // SMTP 账号
			$mail->Password = $mail_pwd;                           // SMTP 密码
			$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = $mail_port;                                    // 邮件端口
			$mail->setFrom($mail_id, $mail_name);
			$mail->addAddress($to_mail, $buyer_name);     // 收件人
			$mail->addReplyTo($mail_answer_addr, '');	//邮件回复地址
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $mail_topic;	//邮件标题
			$mail->Body    = $mail_html;	//邮件内容
			$mail->AltBody = $mail_txt;	//未设置HTML将会收到的内容

			if(!$mail->send()) {
			    echo '邮件发送失败.';
			    echo 'Mailer Error: ' . $mail->ErrorInfo;
			} else {
			}
		}	# 遍历id
	}
}