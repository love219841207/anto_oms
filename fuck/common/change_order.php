<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);

// 计算价格
if(isset($_GET['play_price'])){
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';
	$order_id = $_GET['order_id'];

	//查询该订单是否是COD订单
	$sql = "SELECT payment_method,send_id,order_line FROM $response_list WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	$payment_method = $res['payment_method'];
	$send_id = $res['send_id'];
	$order_line = $res['order_line'];
	
	//子订单价格 = 数量 * 单价
	$sql = "UPDATE $response_info SET item_price = unit_price * goods_num WHERE order_id = '{$order_id}'";
	$res = $db->execute($sql);

	if($payment_method == 'COD'){
		//如果是COD订单，计算金额
		$sql = "SELECT sum(item_price+yf_money) as pay_money FROM $response_info WHERE order_id='{$order_id}'";
		$res = $db->getOne($sql);
		$pay_money = $res['pay_money'];
		$pay_money2 = $res['pay_money'];

		$sql = "SELECT cod_money FROM $response_info WHERE order_id='{$order_id}'";
		$res = $db->getOne($sql);
		$cod_money = $res['cod_money'];

		$pay_money = $pay_money+$cod_money;
		//更新
		$sql = "UPDATE $response_list SET all_total_money = '{$pay_money}',order_total_money = '{$pay_money}',pay_money = '{$pay_money}'  WHERE order_id='{$order_id}'";
		$res = $db->execute($sql);

		// 是否已经到发货区
		if($order_line > 4){
			$sql = "UPDATE send_table SET due_money = '{$pay_money}' WHERE send_id='{$send_id}'";
			$res = $db->execute($sql);
		}
	}else{
		//查询出订单额，计算金额
		$sql = "SELECT sum(item_price+yf_money) as total_money FROM $response_info WHERE order_id='{$order_id}'";
		$res = $db->getOne($sql);
		$total_money = $res['total_money'];

		//更新total_money
		$sql = "UPDATE $response_list SET all_total_money = '{$total_money}',order_total_money = '{$total_money}'  WHERE order_id='{$order_id}'";
		$res = $db->execute($sql);

		// 是否已经到发货区 不是COD不需要更新
		// if($order_line > 4){
		// 	$sql = "UPDATE send_table SET due_money = '{$total_money}' WHERE send_id='{$send_id}'";
		// 	$res = $db->execute($sql);
		// }
	}

	// // 查询是否是合单
	if(strstr($send_id, 'H') == true){
		// 总合单金额计算 = 合单金额计算 - COD订单数 * COD费用 + 一个COD费用 （以后涉及运费代码问题）
		$sql = "SELECT count(order_id) as count_cod FROM $response_list WHERE send_id = '{$send_id}' AND payment_method = 'COD'";
		$res = $db->getOne($sql);
		$count_cod = $res['count_cod'];

		$sql = "SELECT cod_money FROM $response_info WHERE order_id = '{$order_id}'";
		$res = $db->getOne($sql);
		$cod_money = $res['cod_money'];

		$fee = $count_cod * $cod_money;

		// 查出总价
		$sql = "SELECT sum(order_total_money) as sum FROM $response_list WHERE send_id = '{$send_id}'";
		$res = $db->getOne($sql);
		$sum_total_money = $res['sum'];

		$fee = $sum_total_money - $fee + $cod_money;

		$sql = "UPDATE $response_list SET all_total_money = $fee WHERE send_id='{$send_id}'";
		$res = $db->execute($sql);
		//	合单并代引
		if($payment_method == 'COD'){
			// 算出非代引总价
			$sql = "SELECT sum(order_total_money) as normal_sum FROM amazon_response_list WHERE send_id = '{$send_id}' AND payment_method <> 'COD'";
			$res = $db->getOne($sql);

			$normal_sum = $res['normal_sum'];
			$fee_pay = $fee - $normal_sum;

			$sql = "UPDATE $response_list SET pay_money = $fee_pay WHERE send_id='{$send_id}'";
			$res = $db->execute($sql);
			// 是否已经到发货区
			if($order_line > 4){
				$sql = "UPDATE send_table SET due_money = $fee_pay WHERE send_id='{$send_id}'";
				$res = $db->execute($sql);
			}
		}else{
			// 是否已经到发货区
			if($order_line > 4){
				$sql = "UPDATE send_table SET due_money = '0' WHERE send_id='{$send_id}'";
				$res = $db->execute($sql);
			}
		}
		
	}

	echo 'ok';
}

//查看订单详单
if(isset($_GET['show_one_info'])){
	$store = $_GET['store'];
    $station = strtolower($_GET['station']);
	$order_id = $_GET['show_one_info'];

	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	//查询订单信息
	$sql = "SELECT * FROM $response_list WHERE order_id = '{$order_id}'";
	$res_list = $db->getAll($sql);

	//查询子订单	
	$sql = "SELECT * FROM $response_info WHERE order_id = '{$order_id}'";
	$res_info = $db->getAll($sql);

	// 查询 OMS-ID
	$sql = "SELECT id FROM $response_list WHERE order_id = '{$order_id}'";
   	$res = $db->getOne($sql);
   	$oms_id = $res['id'];

	//查询操作日志
	$sql = "SELECT * FROM oms_log WHERE oms_id = '{$oms_id}' ORDER BY id DESC";
	$res_logs = $db->getAll($sql);

	//final_res
	$final_res['status'] = 'ok';
	$final_res['res_list'] = $res_list;
	$final_res['res_info'] = $res_info;
	$final_res['res_logs'] = $res_logs;
	echo json_encode($final_res);
}

// 查看库存数
if(isset($_GET['check_repo'])){
	$store = $_GET['store'];
	$id = $_GET['id'];
    $station = strtolower($_GET['station']);
    $response_info = $station.'_response_info';
	$goods_code = $_GET['check_repo'];

	$sql = "SELECT a_repo,b_repo FROM goods_type WHERE goods_code = '{$goods_code}'";
	$res = $rdb->getOne($sql);
	$a_repo = $res['a_repo'];
	$b_repo = $res['b_repo'];
	$sql = "UPDATE $response_info SET a_repo_num = '{$a_repo}',b_repo_num = '{$b_repo}' WHERE id='{$id}'";
	$res = $db->execute($sql);
	echo 'ok';
}

//修改list字段
if(isset($_GET['change_list_field'])){
	$store = $_GET['store'];
    $station = strtolower($_GET['station']);
	$order_id = $_GET['order_id'];
	$field_name = $_GET['field_name'];
	$new_key = addslashes($_GET['new_key']);

	$response_list = $station.'_response_list';

	//查询原字段值
	$sql = "SELECT id,$field_name as o_key FROM $response_list WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	$o_key = $res['o_key'];
	$oms_id = $res['id'];

	if($field_name == 'phone'){
		$ch_field = '电话';
		$sql = "UPDATE $response_list SET $field_name = '{$new_key}',tel_ok = 1 WHERE order_id = '{$order_id}'";
	}
	if($field_name == 'receive_name'){
		$ch_field = '收件人';
		$sql = "UPDATE $response_list SET $field_name = '{$new_key}' WHERE order_id = '{$order_id}'";
	}
	if($field_name == 'buyer_email'){
		$ch_field = '邮箱';
		$sql = "UPDATE $response_list SET $field_name = '{$new_key}' WHERE order_id = '{$order_id}'";
	}
	$res = $db->execute($sql);

	// 日志
	$do = '修改 <'.$ch_field.'>【'.$o_key.'】为【'.$new_key.'】';
	oms_log($u_name,$do,'change_order',$station,$store,$oms_id);

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
	$response_list = $station.'_response_list';
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
	if($field_name == 'unit_price'){
		$ch_field = '单价';
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
			echo '无此商品代码。';die;
		}else{
			$sql = "UPDATE $response_info SET $field_name = '{$new_key}',sku_ok='1' WHERE id = '{$id}'";
			$res = $db->execute($sql);

			// 查询总item 数
			$sql = "SELECT count(1) as ycm FROM $response_info WHERE order_id = '{$order_id}'";
			$res = $db->getOne($sql);
			$item_count = $res['ycm'];
			// 查询sku_ok item数
			$sql = "SELECT count(1) as bcd FROM $response_info WHERE order_id = '{$order_id}' AND sku_ok = 1";
			$res1 = $db->getOne($sql);
			$sku_ok_count = $res1['bcd'];
			if($item_count == $sku_ok_count){
				//更新list sku_ok = 1  通过
				$sql = "UPDATE $response_list SET sku_ok = 1 WHERE order_id = '{$order_id}'";
				$res = $db->execute($sql);
			}else{
				//更新list sku_ok = 2  不通过
				$sql = "UPDATE $response_list SET sku_ok = 2 WHERE order_id = '{$order_id}'";
				$res = $db->execute($sql);
			}
			echo 'ok';
		}
	}else{
		$sql = "UPDATE $response_info SET $field_name = '{$new_key}' WHERE id = '{$id}'";
		$res = $db->execute($sql);
		echo 'ok';
	}

	//查询OMS-ID
	$sql = "SELECT id FROM $response_list WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	$oms_id = $res['id'];

	// 日志
	$do = '修改 <'.$ch_field.'>【'.$o_key.'】为【'.$new_key.'】';
	oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
	
}

//修改订单备注
if(isset($_GET['change_note'])){
	$order_id = $_GET['change_note'];
	$new_key = addslashes($_GET['note']);
	$station = strtolower($_GET['station']);
	$store = $_GET['store'];
	$response_list = $station.'_response_list';

	$sql = "UPDATE $response_list SET order_note = '{$new_key}' WHERE order_id = '{$order_id}'";
	$res = $db->execute($sql);

	//查询OMS-ID
	$sql = "SELECT id FROM $response_list WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	$oms_id = $res['id'];

	// 日志
	$do = '备注为【'.$new_key.'】';
	oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
	echo 'ok';
}

//批量修改订单备注
if(isset($_GET['change_multi_note'])){
	$new_key = addslashes($_GET['note']);
	$station = strtolower($_GET['station']);
	$store = $_GET['store'];
	$response_list = $station.'_response_list';

	$note_orders = $_GET['note_orders'];
	$note_orders = '('.$note_orders.')';

	$sql = "UPDATE $response_list SET order_note = '{$new_key}' WHERE order_id IN $note_orders";
	$res = $db->execute($sql);

	//查询OMS-ID
	$sql = "SELECT id FROM $response_list WHERE order_id in $note_orders";
	$res = $db->getAll($sql);
	foreach ($res as $value) {
		$oms_id = $value['id'];
		// 日志
		$do = '备注为【'.$new_key.'】';
		oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
	}

	echo 'ok';
}

// 标记订单查询
if(isset($_GET['mark_orders'])){
	$mark_orders = $_GET['mark_orders'];
	$method = $_GET['method'];
	$station = strtolower($_GET['station']);
	$mark_orders = '('.$mark_orders.')';

	$response_list = $station.'_response_list';

	// 标记
	$sql = "UPDATE $response_list SET is_mark = '{$method}' WHERE order_id in $mark_orders";
	$res = $db->execute($sql);

	echo 'ok';
}

// 删除订单，实则修改order_id=-1
if(isset($_POST['del_items'])){
	$del_items = $_POST['del_items'];
	$method = $_POST['method'];
	$del_items = '('.$del_items.')';
	$del_log_items = addslashes($_POST['del_items']);
	$station = strtolower($_POST['station']);
	$store = $_POST['store'];

	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	$can_stop = 1;	//默认可以删除

	// 查询是否可以删除
	$sql = "SELECT order_line FROM $response_list WHERE order_id IN $del_items";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$order_line = $val['order_line'].'|';
		if($order_line > 2){	//如果包含冻结以上，不能删除
			$can_stop = 0;
		}
	}
	if($can_stop == 0){
		echo 'cut';
	}else{
		// 判断是否彻底删除
		if($method == 'delete'){
			// 删除response_list
			$sql = "DELETE FROM $response_list WHERE order_id IN $del_items";
			$res = $db->execute($sql);
			// 删除response_info
			$sql = "DELETE FROM $response_info WHERE order_id IN $del_items";
			$res = $db->execute($sql);

			//日志
			$do = ' [彻底删除订单]：【'.$del_log_items.'】';
			oms_log($u_name,$do,'change_order',$station,$store,'-');

			echo 'ok';
		}else if($method == 'trash'){
			// 删除response_list，取消标记
			$sql = "UPDATE $response_list SET order_line = '-1',is_mark='0' WHERE order_id IN $del_items";
			$res = $db->execute($sql);

			//日志
			$do = ' [删除订单]：【'.$del_log_items.'】';
			oms_log($u_name,$do,'change_order',$station,$store,'-');

			echo 'ok';
		}
	}
}

// 还原订单，实则修改order_id=1 返回到订单验证前，同步后状态
if(isset($_POST['return_items'])){
	$return_items = $_POST['return_items'];
	$return_items = '('.$return_items.')';
	$res_log_items = addslashes($_POST['return_items']);
	$station = strtolower($_POST['station']);
	$store = $_POST['store'];

	$response_list = $station.'_response_list';

	// 还原response_list
	$sql = "UPDATE $response_list SET order_line = '1' WHERE order_id IN $return_items";
	$res = $db->execute($sql);

	//日志
	$do = ' [还原订单]：【'.$res_log_items.'】';
	oms_log($u_name,$do,'change_order',$station,$store,'-');

	echo 'ok';
}

// 保留订单，实则修改order_id=9
if(isset($_POST['stop_order'])){
	$stop_order = $_POST['stop_order'];
	$stop_order = '('.$stop_order.')';
	$del_log_items = addslashes($_POST['stop_order']);
	$station = strtolower($_POST['station']);
	$store = $_POST['store'];

	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	$can_stop = 1;	//默认可以stop

	// 查询是否可以保留
	$sql = "SELECT order_line FROM $response_list WHERE order_id IN $stop_order";
	$res = $db->getAll($sql);
	foreach ($res as $val) {
		$order_line = $val['order_line'].'|';
		if($order_line > 3){
			$can_stop = 0;
		}
	}
	if($can_stop == 0){
		echo 'cut';
	}else{
		// 删除response_list，取消标记
		$sql = "UPDATE $response_list SET order_line = '9' WHERE order_id IN $stop_order";
		$res = $db->execute($sql);

		//日志
		$do = ' [保留订单]：【'.$del_log_items.'】';
		oms_log($u_name,$do,'change_order',$station,$store,'-');
		echo 'ok';
	}
}

// 不保留订单，实则修改order_id=9
if(isset($_POST['stop_back_order'])){
	$stop_order = $_POST['stop_back_order'];
	$stop_order = '('.$stop_order.')';
	$del_log_items = addslashes($_POST['stop_back_order']);
	$station = strtolower($_POST['station']);
	$store = $_POST['store'];

	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	$can_stop = 1;	//默认可以stop

	// 删除response_list，取消标记
	$sql = "UPDATE $response_list SET order_line = '1' WHERE order_id IN $stop_order";
	$res = $db->execute($sql);

	//日志
	$do = ' [不保留订单]：【'.$del_log_items.'】';
	oms_log($u_name,$do,'change_order',$station,$store,'-');
	echo 'ok';
}

// 添加item
if(isset($_POST['add_item'])){
	$add_item = trim(addslashes($_POST['add_item']));
	$add_goods_num = $_POST['add_goods_num'];
	$add_unit_price = $_POST['add_unit_price'];
	$add_yfcode = $_POST['add_yfcode'];
	$add_cod_money = $_POST['add_cod_money'];

	$station = strtolower($_POST['station']);
	$store = $_POST['store'];
	$order_id = $_POST['order_id'];

	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	//运费金额计算 ###############
	$add_yf_money = '0';	//暂时为0

	// 当前时间戳
	$now_time = time();

	// 添加
	$sql = "INSERT INTO $response_info (
		store,
		order_id,
		holder,
		goods_title,
		sku_ok,
		yfcode_ok,
		yfcode,
		yf_money,
		sku,
		goods_code,
		goods_num,
		unit_price,
		cod_money,
		import_time) VALUES(
		'{$store}',
		'{$order_id}',
		'{$u_name}',
		'',
		'1',
		'1',
		'{$add_yfcode}',
		'{$add_yf_money}',
		'{$add_item}',
		'{$add_item}',
		'{$add_goods_num}',
		'{$add_unit_price}',
		'{$add_cod_money}',
		{$now_time}
		) ";
	$res = $db->execute($sql);

	// 如果COD_money大于0，则为代引
	if($add_cod_money > 0){
		//日志
		$do = ' [新增一单]：订单号【'.$order_id.'】商品代码【'.$add_item.'】数量【'.$add_goods_num.'】单价【'.$add_unit_price.'】运费代码【'.$add_yfcode.'】运费金额【'.$add_yf_money.'】代引金额【'.$add_cod_money.'】';

	}else{
		//日志
		$do = ' [新增一单]：订单号【'.$order_id.'】商品代码【'.$add_item.'】数量【'.$add_goods_num.'】单价【'.$add_unit_price.'】运费代码【'.$add_yfcode.'】运费金额【'.$add_yf_money.'】';
	}

	//查询OMS-ID
	$sql = "SELECT id FROM $response_list WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	$oms_id = $res['id'];

	oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
	echo 'ok';
}

// 删除item
if(isset($_POST['del_item'])){
	$id = $_POST['del_item'];
	$station = strtolower($_POST['station']);
	$store = $_POST['store'];
	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	// 查询删除的item
	$sql = "SELECT * FROM $response_info WHERE id = '{$id}'";
	$res = $db->getOne($sql);
	$order_id = $res['order_id'];

	// 删除
	$sql1 = "DELETE FROM $response_info WHERE id = '{$id}'";
	$res1 = $db->execute($sql1);

	//查询OMS-ID
	$sql = "SELECT id FROM $response_list WHERE order_id = '{$order_id}'";
	$res2 = $db->getOne($sql);
	$oms_id = $res2['id'];

	$do = '[删除一单]：订单号【'.$res['order_id'].'】商品代码【'.$res['goods_code'].'】数量【'.$res['goods_num'].'】单价【'.$res['unit_price'].'】运费代码【'.$res['yfcode'].'】运费金额【'.$res['yf_money'].'】代引金额【'.$res['cod_money'].'】';

	oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
	echo 'ok';

}