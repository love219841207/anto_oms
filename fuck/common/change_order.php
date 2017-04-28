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

//修改list字段
if(isset($_GET['change_list_field'])){
	$store = $_GET['store'];
    $station = strtolower($_GET['station']);
	$order_id = $_GET['order_id'];
	$field_name = $_GET['field_name'];
	$new_key = addslashes($_GET['new_key']);

	$response_list = $station.'_response_list';
	$order_id_field = $station.'_order_id';

	//查询原字段值
	$sql = "SELECT $field_name as o_key FROM $response_list WHERE $order_id_field = '{$order_id}'";
	$res = $db->getOne($sql);
	$o_key = $res['o_key'];

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
	$do = '订单【'.$order_id.'】修改 <'.$ch_field.'>【'.$o_key.'】为【'.$new_key.'】';
	oms_log($u_name,$do,'amazon_order',$station,$store);

	echo 'ok';
}

//修改info字段
if(isset($_GET['change_info_field'])){
	$id = $_GET['change_info_field'];
	$field_name = $_GET['field_name'];
	$order_id = $_GET['order_id'];
	$new_key = addslashes($_GET['new_key']);
	$station = strtolower($_GET['station']);
	$store = $_GET['store'];
	$response_info = $station.'_response_info';

	if($field_name == 'goods_title'){
		$ch_field = '品名';
	}
	if($field_name == 'goods_code'){
		$ch_field = '商品代码';
	}
	if($field_name == 'goods_num'){
		$ch_field = '数量';
	}
	if($field_name == 'item_price'){
		$ch_field = '子订单价格';
	}

	//查询原字段值
	$sql = "SELECT $field_name as o_key FROM $response_info WHERE id = '{$id}'";
	$res = $db->getOne($sql);
	$o_key = $res['o_key'];

	// 如果是商品代码，检测
	if($field_name == 'goods_code'){
		$sql = "SELECT * FROM goods_type WHERE goods_code ='{$new_key}'";
		$res = $rdb->getOne($sql);
		if(empty($res)){
			echo '无此商品代码。';
		}else{
			$sql = "UPDATE $response_info SET $field_name = '{$new_key}' WHERE id = '{$id}'";
			$res = $db->execute($sql);
			echo 'ok';
		}
	}else{
		$sql = "UPDATE $response_info SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
		echo 'ok';
	}

	// 日志
	$do = '订单【'.$order_id.'】修改 <'.$ch_field.'>【'.$o_key.'】为【'.$new_key.'】';
	oms_log($u_name,$do,'amazon_order',$station,$store);
	
}

//修改订单备注
if(isset($_GET['change_note'])){
	$order_id = $_GET['change_note'];
	$new_key = addslashes($_GET['note']);
	$station = strtolower($_GET['station']);
	$store = $_GET['store'];
	$order_id_field = $station.'_order_id';
	$response_list = $station.'_response_list';

	$sql = "UPDATE $response_list SET order_note = '{$new_key}' WHERE $order_id_field = '{$order_id}'";
	$res = $db->execute($sql);
	// 日志
	$do = '订单【'.$order_id.'】备注为【'.$new_key.'】';
	oms_log($u_name,$do,'amazon_order',$station,$store);
	echo 'ok';
}

//读取订单备注
if(isset($_GET['read_note'])){
	$order_id = $_GET['read_note'];
	$station = strtolower($_GET['station']);
	$order_id_field = $station.'_order_id';
	$response_list = $station.'_response_list';

	$sql = "SELECT order_note FROM $response_list WHERE $order_id_field = '{$order_id}'";
	$res = $db->getOne($sql);
	echo $res['order_note'];
}

// 标记订单查询
if(isset($_GET['mark_orders'])){
	$mark_orders = $_GET['mark_orders'];
	$method = $_GET['method'];
	$station = strtolower($_GET['station']);
	$mark_orders = '('.$mark_orders.')';

	$response_list = $station.'_response_list';
	$order_id_field = $station.'_order_id';
	// 标记
	$sql = "UPDATE $response_list SET is_mark = '{$method}' WHERE $order_id_field in $mark_orders";
	$res = $db->execute($sql);

	echo 'ok';
}

// 删除订单，实则修改order_id=-1
if(isset($_POST['del_items'])){
	$del_items = $_POST['del_items'];
	$del_items = '('.$del_items.')';
	$del_log_items = addslashes($_POST['del_items']);
	$station = strtolower($_POST['station']);
	$store = $_POST['store'];

	$response_list = $station.'_response_list';
	$order_id_field = $station.'_order_id';

	// 删除response_list，取消标记
	$sql = "UPDATE $response_list SET order_line = '-1',is_mark='0' WHERE $order_id_field IN $del_items";
	$res = $db->execute($sql);

	//日志
	$do = ' [删除订单]：【'.$del_log_items.'】';
	oms_log($u_name,$do,'amazon_order',$station,$store);

	echo 'ok';
}

// 还原订单，实则修改order_id=1 返回到订单验证前，同步后状态
if(isset($_POST['return_items'])){
	$return_items = $_POST['return_items'];
	$return_items = '('.$return_items.')';
	$res_log_items = addslashes($_POST['return_items']);
	$station = strtolower($_POST['station']);
	$store = $_POST['store'];

	$response_list = $station.'_response_list';
	$order_id_field = $station.'_order_id';

	// 还原response_list
	$sql = "UPDATE $response_list SET order_line = '1' WHERE $order_id_field IN $return_items";
	$res = $db->execute($sql);

	//日志
	$do = ' [还原订单]：【'.$res_log_items.'】';
	oms_log($u_name,$do,'amazon_order',$station,$store);

	echo 'ok';
}