<?php
require_once("../header.php");
require_once("../log.php");
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