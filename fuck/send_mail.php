<?php
require_once("./PHPMailer/PHPMailerAutoload.php");
require_once("./header.php");

$mail = new PHPMailer;
$mail->CharSet = "UTF-8";
// $mail->SMTPDebug = true; 
// $mail->SMTPDebug = 4;    
if(isset($_POST['send_mail'])){
	// 测试店铺
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

			//替换信件变量
			$mail_topic = str_replace('#buyer_name#', '购买人', $mail_topic);
			$mail_topic = str_replace('#receive_name#', '收件人', $mail_topic);
			$mail_topic = str_replace('#order_id#', '1234567890', $mail_topic);
			$mail_topic = str_replace('#express_company#', '快递公司', $mail_topic);
			$mail_topic = str_replace('#send_method#', '配送方式', $mail_topic);
			$mail_topic = str_replace('#express_num#', '快递单号', $mail_topic);
			$mail_topic = str_replace('#order_info#', '', $mail_topic);
			$mail_topic = str_replace('#pin_book#', '', $mail_topic);

			$mail_html = str_replace('#buyer_name#', '购买人', $mail_html);
			$mail_html = str_replace('#receive_name#', '收件人', $mail_html);
			$mail_html = str_replace('#order_id#', '1234567890', $mail_html);
			$mail_html = str_replace('#express_company#', '快递公司', $mail_html);
			$mail_html = str_replace('#send_method#', '配送方式', $mail_html);
			$mail_html = str_replace('#express_num#', '快递单号', $mail_html);
			$mail_html = str_replace('#order_info#', '商品明细', $mail_html);
			$mail_html = str_replace('#pin_book#', '纳品书', $mail_html);

		}else if($model_name == 'send_express'){
			//读取信件内容
			$sql = "SELECT * FROM mail_tpl WHERE store_name = '{$store}' and model_name = '{$model_name}'";
			$res = $db->getOne($sql);
			$mail_topic = $res['mail_topic'];
			$mail_html = $res['mail_html'];
			$mail_txt = $res['mail_txt'];

			$order_info = '
<table border="1" bordercolor="no" cellspacing="1" cellpadding="6" style="border-collapse: collapse;font-size:12px;border-color: #ddd;width:100%; font-family: Meiryo;">
	<tr style="background: #009688;color: #FFF;">
		<td style="text-align: center;">商品名/商品オプション</td>
		<td width="25%">商品コード/サブコード</td>
		<td style="text-align:right;" width="20%">単価 * 数量 = 小計</td>
	</tr>
	<tr >
		<td style="color: #616161;">試験商品A HIDライト HIDキット H4リレーレス 10mm業界最薄 本物55W HIDフルキット HIDヘッドライト HIDフォグランプ対応 GTX製HIDライト H11 H8 HB3 HB4 H1 H3 H7</td>
		<td>test-a</td>
		<td style="text-align: right;font-family: monospace;">880 * 10 = 8800円</td>
	</tr>
	<tr>
		<td style="color: #616161;">試験商品B HIDライト HIDキット H4リレーレス 10mm業界最薄 本物55W HIDフルキット HIDヘッドライト HIDフォグランプ対応 GTX製HIDライト H11 H8 HB3 HB4 H1 H3 H7</td>
		<td>test-b</td>
		<td style="text-align: right;font-family: monospace;">1980 * 3 = 5940円</td>
	</tr>
	<tr>
		<td rowspan="7" style="text-align: left; font-size:14px;color: #018276;">
■ 備考
お買い上げ明細書についてご不明な点がございましたら、上記連絡先までお問い合わせください。
		</td>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">商品金額合計:</span>
			<span style="width:80px;display: inline-block;">14740円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">消费税:</span>
			<span style="width:80px;display: inline-block;">0円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">送料:</span>
			<span style="width:80px;display: inline-block;">0円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">値引き:</span>
			<span style="width:80px;display: inline-block;">0円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">ポイント利用分:</span>
			<span style="width:80px;display: inline-block;">0円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">手数料:</span>
			<span style="width:80px;display: inline-block;">324円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">合計金額（税込）:</span>
			<span style="width:80px;display: inline-block;color:#ff5722;font-weight: bold;font-size: 14px;">15064円</span>
		</td>
	</tr>
</table>';

			//替换信件变量
			$mail_topic = str_replace('#buyer_name#', '购买人', $mail_topic);
			$mail_topic = str_replace('#receive_name#', '收件人', $mail_topic);
			$mail_topic = str_replace('#order_id#', '1234567890', $mail_topic);
			$mail_topic = str_replace('#express_company#', '快递公司', $mail_topic);
			$mail_topic = str_replace('#send_method#', '配送方式', $mail_topic);
			$mail_topic = str_replace('#express_num#', '快递单号', $mail_topic);
			$mail_topic = str_replace('#order_info#', '', $mail_topic);

			$mail_html = str_replace('#buyer_name#', '购买人', $mail_html);
			$mail_html = str_replace('#receive_name#', '收件人', $mail_html);
			$mail_html = str_replace('#order_id#', '1234567890', $mail_html);
			$mail_html = str_replace('#express_company#', '快递公司', $mail_html);
			$mail_html = str_replace('#send_method#', '配送方式', $mail_html);
			$mail_html = str_replace('#express_num#', '快递单号', $mail_html);
			$mail_html = str_replace('#order_info#', $order_info, $mail_html);

			// 加入样式
			$mail_html = '
<style>
blockquote {
    display: block;
    border-left: 8px solid #d0e5f2;
    padding: 5px 10px;
    margin: 10px 0;
    line-height: 1.4;
    font-size: 100%;
    background-color: #f1f1f1;
}
table {
  border: none;
  border-collapse: collapse;
}
table td,
table th {
  border: 1px solid #999;
  padding: 3px 5px;
  min-width: 50px;
  height: 20px;
}
</style>'.$mail_html;

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
		$mail->addBCC('ycmbcd@foxmail.com');	//秘密抄送

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
	}
}