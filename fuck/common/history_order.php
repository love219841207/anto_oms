<?php
require_once("../header.php");
require_once("../log.php");
require_once("../../pdo/repair.PdoMySQL.class.php");//REPAIR_PDO
$shdb = new RepairPdoMySQL();

//	一键转入售后
if(isset($_GET['onekey_repair'])){
	$sql = "SELECT 
			who_email,
			order_id,
			goods_id,
			store_name,
			who_id,
			who_phone,
			who_code,
			who_house,
			who_house1,
			who_house2,
			who_name,
			group_concat(goods_code,'*',out_num) as goods,
			receive_phone,
			receive_code,
			receive_house,
			receive_house1,
			receive_house2,
			receive_name,
			total_money,
			ems_money,
			bill,
			point,
			cheap,
			tax,
			buy_money,
			buy_method,
			send_method,
			oms_order_express_num,
			express_day
		 FROM history_send WHERE table_status = '3' GROUP BY receive_phone";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$who_email = $val['who_email'];
		$order_id = $val['order_id'];
		$goods_id = $val['goods_id'];
		$store_name = $val['store_name'];
		$who_id = $val['who_id'];
		$who_phone = $val['who_phone'];
		$who_code = $val['who_code'];
		$who_house = $val['who_house'];
		$who_house1 = $val['who_house1'];
		$who_house2 = $val['who_house2'];
		$who_name = $val['who_name'];
		$goods = $val['goods'];
		$receive_phone = $val['receive_phone'];
		$receive_code = $val['receive_code'];
		$receive_house = $val['receive_house'];
		$receive_house1 = $val['receive_house1'];
		$receive_house2 = $val['receive_house2'];
		$receive_name = $val['receive_name'];
		$total_money = $val['total_money'];
		$ems_money = $val['ems_money'];
		$bill = $val['bill'];
		$point = $val['point'];
		$cheap = $val['cheap'];
		$tax = $val['tax'];
		$buy_money = $val['buy_money'];
		$buy_method = $val['buy_method'];
		$to_method = $val['send_method'];
		$send_num = $val['oms_order_express_num'];
		$send_day = $val['express_day'];
		$sql = "INSERT INTO repair_list (
			email,
			order_id,
			good_id,
			store,
			who_id,
			who_phone,
			who_code,
			who_house,
			who_house1,
			who_house2,
			who_name,
			goods,
			receive_phone,
			receive_code,
			receive_house,
			receive_house1,
			receive_house2,
			receive_name,
			total_money,
			ems_money,
			bill,
			point,
			cheap,
			tax,
			buy_money,
			buy_method,
			to_method,
			send_num,
			send_day
		)VALUES(
			'{$who_email}',
			'{$order_id}',
			'{$goods_id}',
			'{$store_name}',
			'{$who_id}',
			'{$who_phone}',
			'{$who_code}',
			'{$who_house}',
			'{$who_house1}',
			'{$who_house2}',
			'{$who_name}',
			'{$goods}',
			'{$receive_phone}',
			'{$receive_code}',
			'{$receive_house}',
			'{$receive_house1}',
			'{$receive_house2}',
			'{$receive_name}',
			'{$total_money}',
			'{$ems_money}',
			'{$bill}',
			'{$point}',
			'{$cheap}',
			'{$tax}',
			'{$buy_money}',
			'{$buy_method}',
			'{$to_method}',
			'{$send_num}',
			'{$send_day}'
		)";
		$res = $shdb->execute($sql);
	}
	
	// 更新 table_status 3 为 4
	$sql = "UPDATE history_send SET table_status = '4' WHERE table_status = '3'";
	$res = $db->execute($sql);
	echo 'ok';
}

// 退单查询
if(isset($_GET['search_back_order'])){
	$order_id = $_GET['search_back_order'];
	$sql = "SELECT * FROM history_send WHERE order_id = '{$order_id}'";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

// 可退单查询
if(isset($_GET['can_back_order'])){
	$order_id = $_GET['can_back_order'];
	$sql = "SELECT back_status,station FROM history_send WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	$back_status = $res['back_status'];
	$station = $res['station'];
	$finnal_res['station'] = $station;
	$finnal_res['back_status'] = $back_status;
	echo json_encode($finnal_res);
}

// 退单
if(isset($_GET['back_order'])){
	$back_order = $_GET['back_order'];
	$station = $_GET['back_station'];
	$response_list = $station.'_response_list';

	// 标记 history 表退单
 	$sql = "UPDATE history_send SET back_status = '正在退单' WHERE station = '{$station}' AND order_id = '{$back_order}'";
 	$res = $db->execute($sql);

	// 退单到仓库
	$sql = "SELECT goods_code,out_num,pause_ch,pause_jp FROM history_send WHERE back_status = '正在退单'";
	$res = $db->getAll($sql);

	foreach ($res as $val) {
		$goods_code = $val['goods_code'];
		$pause_ch = $val['pause_ch'];
		$pause_jp = $val['pause_jp'];
		$sql = "UPDATE goods_type SET a_repo = a_repo + $pause_ch,b_repo = b_repo + $pause_jp WHERE goods_code = '{$goods_code}'";
		$res = $rdb->execute($sql);
		// 日志
		$do = '[退单] '.$back_order.' <商品代码> '.$goods_code.' <还中国> '.$pause_ch.' <还日本> '.$pause_jp;
		oms_log($_SESSION['oms_u_name'],$do,'change_order','-','-','-');
	}

	// 标记 list 表退单
	$sql = "UPDATE $response_list SET order_line = '-4' WHERE order_id = '{$back_order}'";
	$res = $db->execute($sql);

	// 标记 history 表退单
 	$sql = "UPDATE history_send SET back_status = '已退单' WHERE station = '{$station}' AND order_id = '{$back_order}'";
 	$res = $db->execute($sql);

	// 是否通知售后系统？

	
	echo 'ok';
}