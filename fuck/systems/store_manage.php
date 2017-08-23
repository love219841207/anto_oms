<?php
require_once("../header.php");
// 开关发货通知信
if(isset($_GET['toggle_send'])){
	$conf = 'conf_'.$_GET['station'];
	$store_name = $_GET['toggle_send'];
	$sql = "UPDATE $conf SET mail_over_send = (CASE mail_over_send WHEN  '0' THEN '1' WHEN '1' THEN '0' END) WHERE store_name = '{$store_name}'";
    $res = $db->execute($sql);
    echo 'ok';
}

// 获取发货通知信
if(isset($_GET['over_send'])){
	$conf = 'conf_'.$_GET['station'];
	$store_name = $_GET['over_send'];
	$sql = "SELECT mail_over_send FROM $conf WHERE store_name = '{$store_name}'";
    $res = $db->getOne($sql);
    echo $res['mail_over_send'];
}

//查询店铺
if(isset($_GET['get_store'])){
	$sql = "SELECT * FROM oms_store ORDER BY station";
    $res = $db->getAll($sql);
    echo json_encode($res);
}

// 查询平台
if(isset($_GET['get_station'])){
	$store = $_GET['get_station'];
	$sql = "SELECT station FROM oms_store WHERE store_name = '{$store}'";
    $res = $db->getOne($sql);
    echo $res['station'];
}

//添加店铺
if(isset($_GET['new_store'])){
	$new_store = addslashes($_GET['new_store']);
	$station = addslashes($_GET['station']);
	$conf = 'conf_'.$station;
	$sql = "SELECT * FROM oms_store WHERE store_name = '{$new_store}'";
    $res = $db->getOne($sql);
	if(empty($res)){
		//新建店铺
		$sql = "INSERT INTO oms_store (station,store_name) VALUES ('{$station}','{$new_store}')";
		$res = $db->execute($sql);
		//新建店铺配置
		$sql = "INSERT INTO $conf (store_name) VALUES ('{$new_store}')";
		$res = $db->execute($sql);
		echo 'ok';
	}else{
		echo 'has';
	}
}

//查询店铺配置
if(isset($_GET['get_conf'])){
	$station = $_GET['get_conf'];
	$station = 'conf_'.$station;
	$store_name = $_GET['store_name'];
	$sql = "SELECT * FROM {$station} WHERE store_name = '{$store_name}'";
    $res = $db->getOne($sql);
    echo json_encode($res);  
}

//更新店铺配置
if(isset($_GET['update_conf'])){
	$station = $_GET['update_conf'];
	$store_name = $_GET['store_name'];
	
	$mail_over_send = $_GET['mail_over_send'];
	$use_yfcode = $_GET['use_yfcode'];
	//判断店铺
	if($station == 'Amazon'){
		$awsaccesskeyid = $_GET['awsaccesskeyid'];
		$sellerid = $_GET['sellerid'];
		$signatureversion = $_GET['signatureversion'];
		$secret = $_GET['secret'];
		$marketplaceid_id_1 = $_GET['marketplaceid_id_1'];
		$mail_name = $_GET['mail_name'];
		$mail_id = $_GET['mail_id'];
		$mail_pwd = $_GET['mail_pwd'];
		$mail_smtp = $_GET['mail_smtp'];
		$mail_port = $_GET['mail_port'];
		$mail_answer_addr = $_GET['mail_answer_addr'];
		$sql = "UPDATE conf_Amazon SET awsaccesskeyid = '{$awsaccesskeyid}',sellerid = '{$sellerid}',signatureversion = '{$signatureversion}',secret = '{$secret}',marketplaceid_id_1 = '{$marketplaceid_id_1}',mail_name = '{$mail_name}',mail_id = '{$mail_id}',mail_pwd = '{$mail_pwd}',mail_smtp = '{$mail_smtp}',mail_port = '{$mail_port}',mail_answer_addr = '{$mail_answer_addr}',mail_over_send = '{$mail_over_send}',use_yfcode = '{$use_yfcode}' WHERE store_name = '{$store_name}'";
	}
	if($station == 'Yahoo'){
		$sql = "UPDATE conf_Yahoo SET mail_over_send = '{$mail_over_send}',use_yfcode = '{$use_yfcode}' WHERE store_name = '{$store_name}'";
	}
	if($station == 'Rakuten'){
		$mail_name = $_GET['mail_name'];
		$mail_id = $_GET['mail_id'];
		$mail_pwd = $_GET['mail_pwd'];
		$mail_smtp = $_GET['mail_smtp'];
		$mail_port = $_GET['mail_port'];
		$mail_answer_addr = $_GET['mail_answer_addr'];
		$sql = "UPDATE conf_Rakuten SET mail_name = '{$mail_name}',mail_id = '{$mail_id}',mail_pwd = '{$mail_pwd}',mail_smtp = '{$mail_smtp}',mail_port = '{$mail_port}',mail_answer_addr = '{$mail_answer_addr}' WHERE store_name = '{$store_name}'";
	}
    $res = $db->execute($sql);
    echo 'ok';
}

//删除店铺
if(isset($_GET['del_store'])){
	$del_store = $_GET['del_store'];
	$sql = "DELETE FROM oms_store WHERE store_name = '{$del_store}'";
	$res = $db->execute($sql);
	//删除配置
	$sql = "DELETE FROM conf_Amazon WHERE store_name = '{$del_store}'";
	$res = $db->execute($sql);
	//删除邮件模板
	$sql = "DELETE FROM mail_tpl WHERE store_name = '{$del_store}'";
	$res = $db->execute($sql);
	//这里以后要删除其他平台的配置。！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
	echo 'ok';
}

//保存发货通知信模板
if(isset($_POST['save_express_mail'])){
	$store = addslashes($_POST['save_express_mail']);
	$mail_topic = addslashes($_POST['mail_topic']);
	$express_mail_html = addslashes($_POST['express_mail_html']);
	$express_mail_txt = addslashes($_POST['express_mail_txt']);
	//查询是否有此模板
	$sql = "SELECT * FROM mail_tpl WHERE store_name = '{$store}' AND model_name = 'send_express'";
	$res = $db->getOne($sql);
	if(empty($res)){
		$sql = "INSERT INTO mail_tpl (store_name,model_name,mail_topic,mail_html,mail_txt) VALUES ('{$store}','send_express','{$mail_topic}','{$express_mail_html}','{$express_mail_txt}')";
		$res = $db->execute($sql);
	}else{
		$sql = "UPDATE mail_tpl set mail_topic = '{$mail_topic}',mail_html = '{$express_mail_html}',mail_txt = '{$express_mail_txt}' WHERE store_name = '{$store}' AND model_name = 'send_express'";
		$res = $db->execute($sql);
	}
	echo 'ok';
}

//读取发货通知信模板
if(isset($_GET['get_express_mail'])){
	$store = $_GET['get_express_mail'];
	$sql = "SELECT * FROM mail_tpl WHERE store_name = '{$store}' AND model_name = 'send_express'";
	$res = $db->getOne($sql);
	echo json_encode($res);
}

//新增店铺邮件模板
if(isset($_GET['add_mail_tpl'])){
	$new_tpl = addslashes($_GET['add_mail_tpl']);
	$store = $_GET['mail_tpl_store'];
	$sql = "INSERT INTO mail_tpl (store_name,model_name,mail_topic,mail_html,mail_txt) VALUES ('{$store}','{$new_tpl}','','','')";
	$res = $db->execute($sql);
	echo 'ok';
}

//重命名店铺邮件模板
if(isset($_GET['rename_mail_tpl'])){
	$new_name = addslashes($_GET['new_name']);
	$id = $_GET['rename_mail_tpl'];
	$sql = "UPDATE mail_tpl SET model_name = '{$new_name}' WHERE id = '{$id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

//删除邮件模板
if(isset($_GET['del_mail_tpl'])){
	$id = $_GET['del_mail_tpl'];
	$sql = "DELETE FROM mail_tpl WHERE id = '{$id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

//读取、编辑邮件模板
if(isset($_GET['edit_mail_tpl'])){
	$id = $_GET['edit_mail_tpl'];
	$sql = "SELECT * FROM mail_tpl WHERE id = '{$id}'";
	$res = $db->getOne($sql);
	echo json_encode($res);
}

//保存邮件模板
if(isset($_POST['save_mail_tpl'])){
	$id = addslashes($_POST['save_mail_tpl']);
	$mail_topic = addslashes($_POST['mail_topic']);
	$mail_html = addslashes($_POST['mail_html']);
	$mail_txt = addslashes($_POST['mail_txt']);

	$sql = "UPDATE mail_tpl set mail_topic = '{$mail_topic}',mail_html = '{$mail_html}',mail_txt = '{$mail_txt}' WHERE id = '{$id}'";
	$res = $db->execute($sql);

	echo 'ok';
}

//读取店铺邮件模板
if(isset($_GET['read_mail_tpl'])){
	$store = $_GET['read_mail_tpl'];
	$sql = "SELECT id,model_name FROM mail_tpl WHERE store_name = '{$store}' AND model_name <> 'send_express'";	#排除 express_mail
	$res = $db->getAll($sql);
	echo json_encode($res);
}

//保存自定义邮件模板
if(isset($_POST['save_mail_custom'])){
	$mail_topic = addslashes($_POST['mail_topic']);
	$mail_html = addslashes($_POST['mail_html']);
	$mail_txt = addslashes($_POST['mail_txt']);

	// 判断是否存在用户 mail_tpl
	$sql = "INSERT INTO mail_tpl (store_name,model_name, mail_topic, mail_html, mail_txt) SELECT '{$u_num}', 'custom','{$mail_topic}','{$mail_html}','{$mail_txt}' FROM DUAL WHERE NOT EXISTS(SELECT store_name FROM mail_tpl WHERE store_name = '{$u_num}')";
	$res = $db->execute($sql);

	$sql = "UPDATE mail_tpl set mail_topic = '{$mail_topic}',mail_html = '{$mail_html}',mail_txt = '{$mail_txt}' WHERE store_name = '{$u_num}'";
	$res = $db->execute($sql);

	echo 'ok';
}

//邮件预览
if(isset($_POST['demo_mail'])){
	$value = $_POST['demo_mail'];
	$method = $_POST['method'];
	$station = strtolower($_POST['station']);
	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	if($method == 'tpl'){
		$mail_tpl = $_POST['to_mail_tpl'];

		//读取信件内容
		$sql = "SELECT * FROM mail_tpl WHERE id = '{$mail_tpl}'";
		$res = $db->getOne($sql);
				
		$mail_topic = $res['mail_topic'];
		$mail_html = $res['mail_html'];
		$mail_txt = $res['mail_txt'];
	}

	if($method == 'custom'){
        $mail_topic = $_POST['mail_tpl_topic'];
        $mail_html = $_POST['mail_tpl_html'];
        $mail_txt = $_POST['mail_tpl_txt'];
	}

	// 查询send_id
	$sql = "SELECT send_id FROM $response_list WHERE order_id = {$value}";
	$res = $db->getOne($sql);
	$send_id = $res['send_id'];

	//读取邮箱、收件人等信息
	$sql = "SELECT * FROM $response_list WHERE send_id = '{$send_id}'";
	$res = $db->getOne($sql);

	$purchase_date = $res['purchase_date'];	#付款日期

	$oms_id = $res['id'];	#OMS-ID
	$store = $res['store'];	#store
	$to_mail = $res['buyer_email'];	#邮箱
 	$buyer_name = $res['buyer_name'];	#购买人
 	$receive_name = $res['receive_name'];	#收货人
 	$order_id = $res['order_id'];	#订单号
 	$express_company = $res['express_company'];	#快递公司
 	$send_method = $res['send_method'];	#配送方式
 	$express_num = $res['oms_order_express_num'];	#快递单号
 	$express_day = $res['express_day'];	#快递日期
 	$all_total_money = $res['all_total_money'];	
 	$order_total_money = $res['order_total_money'];	
 	$payment_method = $res['payment_method'];	
 	if($payment_method == 'COD'){
 		$payment_method = "DirectPayment";
 	}else{
 		$payment_method = "Amazon決済（前払い）";
 	}

 	// 初始化title
 	$u_info = '';
 	$cod_money = '';

 	// 查询send_id
 	$sql = "SELECT send_id FROM $response_list WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	$send_id = $res['send_id'];

	$sql = "SELECT sum(order_tax) as order_tax,sum(points) as points,sum(coupon) as coupon,sum(shipping_price) as shipping_price FROM $response_list WHERE send_id = '{$send_id}'";
	$res = $db->getOne($sql);

 	$order_tax = $res['order_tax'];	
 	$points = $res['points'];	
 	$coupon = $res['coupon'];	
 	$shipping_price = $res['shipping_price'];	
 	if($coupon == ''){
 		$coupon = 0;
 	}

 	// 查询该send_id下的订单
 	$sql = "SELECT order_id FROM $response_list WHERE send_id = '{$send_id}'";
 	$res = $db->getAll($sql);
 	$now_order_id = '';
 	foreach ($res as $value) {
 		$now_order_id = $now_order_id.'\''.$value['order_id'].'\',';
 	}
 	$now_order_ids = rtrim($now_order_id, ",");
	// 读取购买信息
 	$sql = "SELECT * FROM $response_info WHERE order_id IN ({$now_order_ids})";
	$res = $db->getAll($sql);

	$goods_money = 0;
	foreach ($res as $val) {
		$goods_title = $val['goods_title'];
		$sku = $val['sku'];
		$goods_num = $val['goods_num'];
		$unit_price = $val['unit_price'];
		$item_price = $val['item_price'];
		$cod_money = $val['cod_money'];

		$u_info = $u_info.'<tr >
			<td style="color: #616161;">'.$goods_title.'</td>
			<td>'.$sku.'</td>
			<td style="text-align: right;font-family: monospace;">'.$unit_price.' * '.$goods_num.' = '.$item_price.'円</td>
		</tr>';
		$goods_money = $goods_money + $item_price;
	}
			
$order_info = '
<table width="100%" border="1" bordercolor="no" cellspacing="1" cellpadding="6" style="border-collapse: collapse;font-size:12px;border-color: #ddd;width:100%; font-family: Meiryo;">
	<tr style="background: #009688;color: #FFF;">
		<td style="text-align: center;">商品名/商品オプション</td>
		<td width="25%">商品コード/サブコード</td>
		<td style="text-align:right;" width="20%">単価 * 数量 = 小計</td>
	</tr>
	'.$u_info.'
	<tr>
		<td rowspan="7" style="text-align: left; font-size:14px;color: #018276;">
		</td>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">商品金額合計:</span>
			<span style="width:80px;display: inline-block;">'.$goods_money.'円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">消费税:</span>
			<span style="width:80px;display: inline-block;">'.$order_tax.'円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">送料:</span>
			<span style="width:80px;display: inline-block;">'.$shipping_price.'円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">値引き:</span>
			<span style="width:80px;display: inline-block;">'.$coupon.'円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">ポイント利用分:</span>
			<span style="width:80px;display: inline-block;">'.$points.'</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">手数料:</span>
			<span style="width:80px;display: inline-block;">'.$cod_money.'</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">合計金額（税込）:</span>
			<span style="width:80px;display: inline-block;color:#ff5722;font-weight: bold;font-size: 14px;">'.$all_total_money.'円</span>
		</td>
	</tr>
</table>';

$pin_book = '
<table width="100%" border="1" bordercolor="no" cellspacing="1" cellpadding="6" style="border-collapse: collapse;font-size:12px;border-color: #ddd;width:100%; font-family: Meiryo;">
	<tr style="border-color: #FFF;">
		<td>'.$store.'</td>
		<td colspan="2" style="text-align: right;">発行日：'.$express_day.'</td>
	</tr>
	<th colspan="3" style="border-color: #FFF;border-bottom:4px solid #009688;color:#009688;text-align: center;font-size:18px;">
		納 品 書
	</th>
</table>
<table width="100%" border="1" bordercolor="no" cellspacing="1" cellpadding="6" style="border-collapse: collapse;font-size:12px;border-color: #FFF;width:100%;line-height: 10px; font-family: Meiryo;">
	<tr>
		<td>'.$buyer_name.' 様</td>
		<td style="text-align:right;">'.$store.'</td>
	</tr>
	<tr>
		<td></td>
		<td style="text-align:right;">〒270-1437</td>
	</tr>
	<tr>
		<td></td>
		<td style="text-align:right;">千葉県 白井市</td>
	</tr>
	<tr>
		<td></td>
		<td style="text-align:right;">木833-15</td>
	</tr>
	<tr>
		<td colspan="3" style="line-height: 18px;">この度は、「gtx-amazon」にてお買い上げいただきまして、誠にありがとうございました。
お買い上げ明細書を送付いたしますので、ご確認いただけますようお願い申し上げます。</td>
	</tr>
	<tr style="line-height:30px;border-bottom: 2px solid #009688;color:#009688;font-size: 14px;text-align: center;">
		<td colspan="3">お買い上げ明細</td>
	</tr>	

	<tr>
		<td></td>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">ご注文日：</span>
			<span style="width:150px;text-align:left;display: inline-block;">'.$purchase_date.'</span>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">ご注文番号：</span>
			<span style="width:150px;text-align:left;display: inline-block;">'.$now_order_ids.'</span>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">お支払方法：</span>
			<span style="width:150px;text-align:left;display: inline-block;">'.$payment_method.'</span>
		</td>
	</tr>
	<tr>
		<td>'.$buyer_name.' 様</td>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">お届け方法：</span>
			<span style="width:150px;text-align:left;display: inline-block;">'.$send_method.'</span>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">お届け希望日：</span>
			<span style="width:150px;text-align:left;display: inline-block;">希望日なし</span>
		</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">お届け希望時間：</span>
			<span style="width:150px;text-align:left;display: inline-block;">希望時間なし</span>
		</td>
	</tr>
</table>
<table width="100%" border="1" bordercolor="no" cellspacing="1" cellpadding="6" style="border-collapse: collapse;font-size:12px;border-color: #ddd;width:100%; font-family: Meiryo;">
	<tr style="background: #009688;color: #FFF;">
		<td style="text-align: center;">商品名/商品オプション</td>
		<td width="25%">商品コード/サブコード</td>
		<td style="text-align:right;" width="20%">単価 * 数量 = 小計</td>
	</tr>
	'.$u_info.'
	<tr>
		<td rowspan="7" style="text-align: left; font-size:14px;color: #018276;">
		■ 備考
お買い上げ明細書についてご不明な点がございましたら、上記連絡先までお問い合わせください。
		</td>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">商品金額合計:</span>
			<span style="width:80px;display: inline-block;">'.$goods_money.'円</span>
		</td>
	</tr>
<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">消费税:</span>
			<span style="width:80px;display: inline-block;">'.$order_tax.'円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">送料:</span>
			<span style="width:80px;display: inline-block;">'.$shipping_price.'円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">値引き:</span>
			<span style="width:80px;display: inline-block;">'.$coupon.'円</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">ポイント利用分:</span>
			<span style="width:80px;display: inline-block;">'.$points.'</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">手数料:</span>
			<span style="width:80px;display: inline-block;">'.$cod_money.'</span>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: right;">
			<span style="color:#009688;">合計金額（税込）:</span>
			<span style="width:80px;display: inline-block;color:#ff5722;font-weight: bold;font-size: 14px;">'.$all_total_money.'円</span>
		</td>
	</tr>
</table>
';

	//替换信件变量
	$mail_topic = str_replace('#buyer_name#', $buyer_name, $mail_topic);
	$mail_topic = str_replace('#receive_name#', $receive_name, $mail_topic);
	$mail_topic = str_replace('#order_id#', $now_order_ids, $mail_topic);
	$mail_topic = str_replace('#express_company#', $express_company, $mail_topic);
	$mail_topic = str_replace('#send_method#', $send_method, $mail_topic);
	$mail_topic = str_replace('#express_num#', $express_num, $mail_topic);
	$mail_topic = str_replace('#order_info#', '', $mail_topic);
	$mail_topic = str_replace('#pin_book#', '', $mail_topic);

	$mail_html = str_replace('#buyer_name#', $buyer_name, $mail_html);
	$mail_html = str_replace('#receive_name#', $receive_name, $mail_html);
	$mail_html = str_replace('#order_id#', $now_order_ids, $mail_html);
	$mail_html = str_replace('#express_company#', $express_company, $mail_html);
	$mail_html = str_replace('#send_method#', $send_method, $mail_html);
	$mail_html = str_replace('#express_num#', $express_num, $mail_html);
	$mail_html = str_replace('#order_info#', $order_info, $mail_html);
	$mail_html = str_replace('#pin_book#', $pin_book, $mail_html);

	$final_res['mail_topic'] = $mail_topic;
	$final_res['mail_html'] = $mail_html;

	echo json_encode($final_res);
}