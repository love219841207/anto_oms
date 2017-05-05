<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);

// 读取待发货
if(isset($_GET['show_send_info'])){
	$id = $_GET['show_send_info'];
	$sql = "SELECT * FROM send_table WHERE id = '{$id}'";
	$res = $db->getAll($sql);

	echo json_encode($res);
}

// 验证send字段
if(isset($_GET['need_check_send'])){
	$field_name = $_GET['field_name'];
	$new_key = $_GET['new_key'];

	//	收件人/电话/地址
	if($field_name == 'who_name' or $field_name == 'who_tel' or $field_name == 'who_email'){
		echo 'ok';
	}

	// 数量 or 代引金额
	if($field_name == 'out_num' or $field_name == 'due_money'){
		if($new_key < 0){
			echo '不能为负数。';
		}else{
			echo 'ok';
		}
	}

	// goods_code
	if($field_name == 'goods_code'){
		$sql = "SELECT 1 FROM goods_type WHERE goods_code='{$new_key}' limit 1";
	    $res = $rdb->getOne($sql);
	    if(empty($res)){
	    	echo '无此商品代码。';
	    }else{
	    	echo 'ok';
	    }
	}

}

//修改send字段
if(isset($_GET['change_send_field'])){
	$store = $_GET['store'];
    $station = strtolower($_GET['station']);
	$id = $_GET['id'];	//send_table 的 ID
	$oms_id = $_GET['oms_id'];
	$info_id = $_GET['info_id'];
	$o_key = $_GET['o_key'];
	$field_name = $_GET['field_name'];
	$new_key = addslashes($_GET['new_key']);

	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	if($field_name == 'who_tel'){
		$ch_field = '电话';
		$list_field = 'phone';
		// 更新LIST
		$sql = "UPDATE $response_list SET $list_field = '{$new_key}' WHERE id = '{$oms_id}'";
		$res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}
	if($field_name == 'who_name'){
		$ch_field = '收件人';
		$list_field = 'receive_name';
		// 更新LIST
		$sql = "UPDATE $response_list SET $list_field = '{$new_key}' WHERE id = '{$oms_id}'";
		$res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}
	if($field_name == 'who_email'){
		$ch_field = '邮箱';
		$list_field = 'buyer_email';
		// 更新LIST	
		$sql = "UPDATE $response_list SET $list_field = '{$new_key}' WHERE id = '{$oms_id}'";
		$res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}
	if($field_name == 'goods_code'){
		$ch_field = '商品代码';
		$list_field = 'goods_code';
		// 更新INFO
		$sql = "UPDATE $response_info SET $list_field = '{$new_key}' WHERE id = '{$info_id}'";
		$res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}
	if($field_name == 'out_num'){
		$ch_field = '数量';
		$list_field = 'goods_num';
		// 更新INFO
		$sql = "UPDATE $response_info SET $list_field = '{$new_key}' WHERE id = '{$info_id}'";
		$res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}
	if($field_name == 'due_money'){
		$ch_field = '代引金额';
		$list_field = 'pay_money';
		// 更新LIST
		$sql = "UPDATE $response_list SET $list_field = '{$new_key}' WHERE id = '{$oms_id}'";
		$res = $db->execute($sql);
		// 更新 send_table
		$sql = "UPDATE send_table SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
	}

	// 日志
	$do = '修改 <'.$ch_field.'>【'.$o_key.'】为【'.$new_key.'】';
	oms_log($u_name,$do,'ready_send',$station,$store,$oms_id);

	echo 'ok';
}

// 邮编、地址验证。通过则更新。
if(isset($_GET['change_post_addr'])){
	$store = $_GET['store'];
    $station = strtolower($_GET['station']);
	$id = $_GET['id'];	//send_table 的 ID
	$oms_id = $_GET['oms_id'];
	$info_id = $_GET['info_id'];
	$response_list = $station.'_response_list';
	$new_post_code = addslashes($_GET['new_post_code']);
	$new_address = addslashes($_GET['new_address']);

	// 查询出该 post_code 的对应市区
	$sql = "SELECT post_name FROM oms_post WHERE post_code = '{$new_post_code}'";
	$res = $db->getOne($sql);
	$post_name = $res['post_name'];
	if($post_name == ''){
		echo '邮编不存在。';
	}else{
		// 邮编和地址匹配
		if(strstr($new_address, $post_name) == true){
			// 查询原字段值
			$sql = "SELECT post_code,address FROM $response_list WHERE id = '{$oms_id}'";
			$res = $db->getOne($sql);
			$o_post_code = $res['post_code'];
			$o_address = $res['address'];

			// 保存 LIST
			$sql = "UPDATE $response_list SET post_code = '{$new_post_code}',address = '{$new_address}' WHERE id = '{$oms_id}'";
		    $res = $db->execute($sql);

		    // 保存 send_table
			$sql = "UPDATE send_table SET who_post = '{$new_post_code}',who_house = '{$new_address}' WHERE id = '{$id}'";
		    $res = $db->execute($sql);

		    // 日志
			$do = 'OMS-ID【'.$oms_id.'】修改 <邮编/地址>【'.$o_post_code.'/'.$o_address.'】为【'.$new_post_code.'/'.$new_address.'】';
			oms_log($u_name,$do,'ready_send',$station,$store,$oms_id);

			echo 'ok';
		}else{
			echo '邮编、地址不匹配。';
		}
	}
}