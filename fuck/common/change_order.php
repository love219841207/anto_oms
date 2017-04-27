<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);

//查看订单详单
if(isset($_GET['show_one_info'])){
	$store = $_GET['store'];
    $station = strtolower($_GET['station']);
	$order_id = $_GET['show_one_info'];

	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';
	$order_id_field = $station.'_order_id';

	//查询订单信息
	$sql = "SELECT * FROM $response_list WHERE $order_id_field = '{$order_id}'";
	$res_list = $db->getAll($sql);

	//查询子订单	
	$sql = "SELECT * FROM $response_info WHERE $order_id_field = '{$order_id}'";
	$res_info = $db->getAll($sql);

	//final_res
	$final_res['status'] = 'ok';
	$final_res['res_list'] = $res_list;
	$final_res['res_info'] = $res_info;
	echo json_encode($final_res);
}

//修改list字段，多字段修改
if(isset($_GET['change_list_field'])){
	$store = $_GET['store'];
    $station = strtolower($_GET['station']);
	$order_id = $_GET['order_id'];
	$field_name = $_GET['field_name'];
	$new_key = addslashes($_GET['new_key']);

	$response_list = $station.'_response_list';
	$order_id_field = $station.'_order_id';

	if($field_name == 'phone'){
		$ch_field = '电话';
		$sql = "UPDATE $response_list SET $field_name = '{$new_key}',tel_ok = 1 WHERE $order_id_field = '{$order_id}'";
	}
	if($field_name == 'receive_name'){
		$ch_field = '收件人';
		$sql = "UPDATE $response_list SET $field_name = '{$new_key}' WHERE $order_id_field = '{$order_id}'";
	}
	if($field_name == 'buyer_email'){
		$ch_field = '邮箱';
		$sql = "UPDATE $response_list SET $field_name = '{$new_key}' WHERE $order_id_field = '{$order_id}'";
	}

	
	$res = $db->execute($sql);

	

	// 日志
	$do = '订单【'.$order_id.'】修改【'.$ch_field.'】为【'.$new_key.'】';
	oms_log($u_name,$do,'amazon_syn');

	echo 'ok';
}

//修改info字段，多字段修改
if(isset($_GET['change_info_field'])){
	$order_item_id = $_GET['change_info_field'];
	$field_name = $_GET['field_name'];
	$new_key = addslashes($_GET['new_key']);
	$sql = "UPDATE amazon_response_info SET $field_name = '{$new_key}' WHERE order_item_id = '{$order_item_id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

//修改订单备注
if(isset($_GET['change_note'])){
	$amazon_order_id = $_GET['change_note'];
	$new_key = addslashes($_GET['note']);
	$sql = "UPDATE amazon_response_list SET order_note = '{$new_key}' WHERE amazon_order_id = '{$amazon_order_id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

//读取订单备注
if(isset($_GET['read_note'])){
	$amazon_order_id = $_GET['read_note'];
	$sql = "SELECT order_note FROM amazon_response_list WHERE amazon_order_id = '{$amazon_order_id}'";
	$res = $db->getOne($sql);
	echo $res['order_note'];
}