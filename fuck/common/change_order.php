<?php
require_once("../header.php");
require_once("../log.php");
require_once("./play_price.php");
require_once("./play_yfcode.php");
$dir = dirname(__FILE__);

set_time_limit(0);
// 指定配送日期时间
if(isset($_GET['want_date'])){
	$station = strtolower($_GET['station']);
	$store = $_GET['store'];
	$response_list = $station.'_response_list';
	$want_date = $_GET['want_date'];
	$want_time = $_GET['want_time'];
	$order_id = $_GET['order_id'];

	$sql = "UPDATE $response_list SET want_date = '{$want_date}',want_time = '{$want_time}' WHERE order_id = '{$order_id}'";
	$res = $db->execute($sql);

	// 更新send_table
	$sql = "UPDATE send_table SET want_date = '{$want_date}',want_time = '{$want_time}' WHERE order_id = '{$order_id}' AND station = '{$station}'";
	$res = $db->execute($sql);

	$sql = "SELECT id FROM $response_list WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	$oms_id = $res['id'];

	// 日志
	$do = '指定配送时间： <'.$order_id.'>【'.$want_date.'】'.$want_time;
	oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
	echo 'ok';
}

// 计算价格
if(isset($_GET['play_price'])){
	$station = strtolower($_GET['station']);
	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';
	$order_id = $_GET['order_id'];

	play_order_price($station,$response_list,$response_info,$order_id);
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
	$sql = "SELECT * FROM oms_log WHERE oms_id = '{$oms_id}' AND station='{$station}' AND store = '{$store}' ORDER BY id DESC";
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
	if($field_name == 'shipping_price'){
		$ch_field = '运费';
		$sql = "UPDATE $response_list SET $field_name = '{$new_key}' WHERE order_id = '{$order_id}'";
	}
	
	$res = $db->execute($sql);

	// 日志
	$do = '修改 <'.$ch_field.'>【'.$o_key.'】为【'.$new_key.'】';
	oms_log($u_name,$do,'change_order',$station,$store,$oms_id);

	echo 'ok';
}

// 修改同步运费代码
if(isset($_GET['syn_yfcode'])){
	$send_id = $_GET['syn_yfcode'];
	$station = strtolower($_GET['station']);
	$store = $_GET['store'];
	$response_list = $station.'_response_list';
	$response_info = $station.'_response_info';

	// 查询原始运费
	$sql = "SELECT id,shipping_price,all_yfmoney FROM $response_list WHERE send_id = '{$send_id}'";
	$res = $db->getOne($sql);

	$o_key = $res['shipping_price'];
	$new_key = $res['all_yfmoney'];
	$oms_id = $res['id'];

	// 更新运费
	$sql = "UPDATE $response_list SET shipping_price = all_yfmoney WHERE send_id = '{$send_id}'";
	$res = $db->execute($sql);

	// 计算运费代码
	play_yf_code($station,$response_list,$response_info,$send_id);
	$sql = "SELECT order_id FROM $response_list WHERE send_id = '{$send_id}'";
	$res = $db->getOne($sql);
	$order_id = $res['order_id'];
	
	// 计算订单金额
	play_order_price($station,$response_list,$response_info,$order_id);

	// 日志
	$do = '同步运费代码【'.$o_key.'】为【'.$new_key.'】';
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
	if($field_name == 'yfcode'){
		$ch_field = '运费代码';
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
	// 如果是运费代码或者是数量，计算运费代码
	if($field_name == 'yfcode' or $field_name == 'goods_num'){
		// 查询send_id
		$sql = "SELECT send_id FROM $response_list WHERE order_id = '{$order_id}'";
		$res = $db->getOne($sql);
		$send_id = $res['send_id'];
		play_yf_code($station,$response_list,$response_info,$send_id);
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
		if($order_line > 2){
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
	$sql = "UPDATE $response_list SET order_line = '1',pause_time='' WHERE order_id IN $stop_order";
	$res = $db->execute($sql);
	// 修改退押情况下info
	$sql = "UPDATE $response_info SET is_pause = '' WHERE order_id IN $stop_order";
	$res = $db->execute($sql);

	//日志
	$do = ' [取回订单]：【'.$del_log_items.'】';
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
		'{$add_item}',
		'{$add_item}',
		'{$add_goods_num}',
		'{$add_unit_price}',
		'{$add_cod_money}',
		{$now_time}
		) ";
	$res = $db->execute($sql);

	// 查询send_id，计算运费代码
	$sql = "SELECT id,send_id FROM $response_list WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	$oms_id = $res['id'];
	$send_id = $res['send_id'];
	play_yf_code($station,$response_list,$response_info,$send_id);

	// 如果COD_money大于0，则为代引
	if($add_cod_money > 0){
		//日志
		$do = ' [新增一单]：订单号【'.$order_id.'】商品代码【'.$add_item.'】数量【'.$add_goods_num.'】单价【'.$add_unit_price.'】运费代码【'.$add_yfcode.'】代引金额【'.$add_cod_money.'】';

	}else{
		//日志
		$do = ' [新增一单]：订单号【'.$order_id.'】商品代码【'.$add_item.'】数量【'.$add_goods_num.'】单价【'.$add_unit_price.'】运费代码【'.$add_yfcode.'】';
	}

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

	// 查询send_id，计算运费代码
	$sql = "SELECT id,send_id FROM $response_list WHERE order_id = '{$order_id}'";
	$res2 = $db->getOne($sql);
	$oms_id = $res2['id'];
	$send_id = $res2['send_id'];
	play_yf_code($station,$response_list,$response_info,$send_id);

	$do = '[删除一单]：订单号【'.$res['order_id'].'】商品代码【'.$res['goods_code'].'】数量【'.$res['goods_num'].'】单价【'.$res['unit_price'].'】运费代码【'.$res['yfcode'].'】代引金额【'.$res['cod_money'].'】';

	oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
	echo 'ok';

}