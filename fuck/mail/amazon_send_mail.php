<?php
require_once("../PHPMailer/PHPMailerAutoload.php");
require_once("../header.php");


// $mail->SMTPDebug = true; 
// $mail->SMTPDebug = 4;   
// 读取错误邮件
if(isset($_GET['read_error_mail'])){
	$sql = "SELECT * FROM mail_error";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

// 亚马逊发信
if(isset($_POST['send_mail'])){
	// amazon发信
	if($_POST['send_mail'] == 'amazon'){
		$conf = 'conf_'.$_POST['station'];
		$store = $_POST['store'];
		$mail_tpl = $_POST['mail_tpl'];
		$my_checked_items = $_POST['my_checked_items'];
		$order_items = $_POST['my_checked_items'];
		$order_items = str_replace('\'', '', $order_items);// 去掉order_items引号

		//读取店铺配置
		$sql = "SELECT * FROM $conf WHERE store_name = '{$store}'";
		$res = $db->getOne($sql);
		$mail_name = $res['mail_name'];
		$mail_id = $res['mail_id'];
		$mail_pwd = $res['mail_pwd'];
		$mail_smtp = $res['mail_smtp'];
		$mail_port = $res['mail_port'];
		$mail_answer_addr = $res['mail_answer_addr'];
		$mail_over_send = $res['mail_over_send'];

		// 遍历order_id
		$order_ids = explode(',', $order_items);

		// 清空邮件错误表
	    $sql = "TRUNCATE mail_error";
	    $res = $db->execute($sql);
		$error_num = 0;
		$ok_num = 0;
		foreach ($order_ids as $value) {
			//读取信件内容
			if($mail_tpl == 'send_express'){
				$sql = "SELECT * FROM mail_tpl WHERE store_name = '{$store}' AND model_name = 'send_express'";
				$res = $db->getOne($sql);
			}else{
				$sql = "SELECT * FROM mail_tpl WHERE id = '{$mail_tpl}'";
				$res = $db->getOne($sql);
			}
			
			$mail_topic = $res['mail_topic'];
			$mail_html = $res['mail_html'];
			$mail_txt = $res['mail_txt'];

			//读取邮箱、收件人等信息
			$sql = "SELECT * FROM amazon_response_list WHERE order_id = '{$value}'";
			$res = $db->getOne($sql);

			$to_mail = $res['buyer_email'];	#邮箱
		 	$buyer_name = $res['buyer_name'];	#购买人
		 	$receive_name = $res['receive_name'];	#收货人
		 	$order_id = $res['order_id'];	#订单号
		 	$express_company = $res['express_company'];	#快递公司
		 	$send_method = $res['send_method'];	#配送方式
		 	$express_num = $res['oms_order_express_num'];	#快递单号


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

$pin_book = '
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
			$mail_topic = str_replace('#buyer_name#', $buyer_name, $mail_topic);
			$mail_topic = str_replace('#receive_name#', $receive_name, $mail_topic);
			$mail_topic = str_replace('#order_id#', $order_id, $mail_topic);
			$mail_topic = str_replace('#express_company#', $express_company, $mail_topic);
			$mail_topic = str_replace('#send_method#', $send_method, $mail_topic);
			$mail_topic = str_replace('#express_num#', $express_num, $mail_topic);
			$mail_topic = str_replace('#order_info#', '', $mail_topic);
			$mail_topic = str_replace('#pin_book#', '', $mail_topic);

			$mail_html = str_replace('#buyer_name#', $buyer_name, $mail_html);
			$mail_html = str_replace('#receive_name#', $receive_name, $mail_html);
			$mail_html = str_replace('#order_id#', $order_id, $mail_html);
			$mail_html = str_replace('#express_company#', $express_company, $mail_html);
			$mail_html = str_replace('#send_method#', $send_method, $mail_html);
			$mail_html = str_replace('#express_num#', $express_num, $mail_html);
			$mail_html = str_replace('#order_info#', $order_info, $mail_html);
			$mail_html = str_replace('#pin_book#', $pin_book, $mail_html);

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
			$mail->addBCC('329331097@qq.com');	//秘密抄送
			$mail->addReplyTo($mail_answer_addr, '');	//邮件回复地址
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $mail_topic;	//邮件标题
			$mail->Body    = $mail_html;	//邮件内容
			$mail->AltBody = $mail_txt;	//未设置HTML将会收到的内容

			if(!$mail->send()) {
			    // echo '邮件发送失败.';
			    $error_info = $mail->ErrorInfo;

			    // 发信失败，记录在案
			    $sql = "INSERT INTO mail_error (error_order_id,error_info) VALUES ('{$value}','{$error_info}')";
			    $res = $db->execute($sql);
			    $error_num = $error_num + 1;
			} else {
				$ok_num = $ok_num + 1;
			}
		}	# 遍历id

		// 标记状态
	    $sql = "UPDATE amazon_express SET over_mail = 1 WHERE amazon_order_id IN ($my_checked_items)";
	    $res = $db->execute($sql);
	    $sql = "UPDATE history_send SET over_mail = 1 WHERE order_id IN ($my_checked_items)";
	    $res = $db->execute($sql);

		$final_res['status'] = 'ok';
		$final_res['error_num'] = $error_num;
		$final_res['ok_num'] = $ok_num;
		echo json_encode($final_res);
	}
}