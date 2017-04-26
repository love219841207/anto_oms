<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
if(isset($_GET['format_order'])){
	$store = $_GET['format_order'];
	$today = date('y-m-d',time()); //获取日期
	//order_line = 3（已合单）转入格式化表

	$sql = "INSERT INTO amazon_format (
		send_id,	#合单发货ID
		oms_id,	#OMS-ID
		sku, 	#sku，客人看
		goods_code,	#商品代码，仓库看
		out_num,	#商品数量
		who_tel,	#配送电话
		who_post,	#邮编
		who_house,	#地址
		who_name,	#收货人
		is_cod,		#是否代引
		due_money,	#代引金额，写出全部的item金额，根据cod，更新是否是代引
		express_company,
		send_method,
		who_email,	#邮编
		store_name,	#店铺名
		holder,		#担当者
		import_day) SELECT	#导入日期 
		list.send_id,
		list.id,
		info.sku as sku1,
		info.sku as sku2,
		info.goods_num,
		list.phone,
		list.post_code,
		list.address,
		list.receive_name,
		list.payment_method,
		info.cod_money+info.item_price,	#带引金额
		'佐川急便',
		'宅配便',
		list.buyer_email,
		'{$store}',
		'{$u_name}',
		'{$today}' from amazon_response_list list,amazon_response_info info where list.amazon_order_id = info.amazon_order_id AND list.order_line = '3' AND list.store = '{$store}'";
	$res = $db->execute($sql);

	// // 格式化表代引金额
	// $sql = "UPDATE amazon_format SET due_money = '' WHERE is_cod <> 'COD'";
	// $res = $db->execute($sql);

	// //	更新黑猫地址	（神奈川県，埼玉県，茨城県，群馬県，山梨県）
	// $sql = "UPDATE amazon_format SET express_company = 'ヤマト運輸',send_method = '宅急便' WHERE who_house LIKE '%神奈川県%' OR who_house LIKE '%埼玉県%' OR who_house LIKE '%茨城県%' OR who_house LIKE '%群馬県%' OR who_house LIKE '%山梨県%'";
	// $res = $db->execute($sql);

	// // 配送方式
	// $sql = "UPDATE amazon_format SET pack_id = concat('p',oms_id) WHERE send_method = '宅配便' OR send_method = '宅急便'";
	// $res = $db->execute($sql);

	// 更改状态
	$sql = "UPDATE amazon_response_list SET order_line = '4' WHERE order_line = '3' AND store = '{$store}'";
	$res = $db->execute($sql);

	echo 'ok';
}

// 读取格式化列表
if(isset($_GET['read_format_table'])){
	$store = $_GET['read_format_table'];

	$sql = "SELECT * FROM amazon_format WHERE store_name = '{$store}' ORDER BY id DESC";
	$res = $db->getAll($sql);
	echo json_encode($res);
}

// 修改格式化列表字段
// if(isset($_GET['change_format_field'])){
// 	$id = $_GET['change_format_field'];
// 	$field_name = $_GET['field_name'];
// 	$new_key = addslashes($_GET['new_key']);
// 	$old_key = $_GET['old_key'];
// 	$oms_id = $_GET['oms_id'];

// 	//是否是 goods_code
// 	if($field_name == 'goods_code'){
// 		//检测是否存在此 goods_code
// 		$sql = "SELECT 1 FROM goods_type WHERE goods_code='{$new_key}' limit 1";
//         $res = $rdb->getOne($sql);
//         if(empty($res)){
//     		//如果没有此商品代码,error
//     		echo 'no_has';
//         }else{
//         	//更新字段
//         	$sql = "UPDATE amazon_format SET $field_name = '{$new_key}' WHERE id = '{$id}'";
//         	$res = $db->execute($sql);

//         	//更新报错
//         	$sql = "UPDATE amazon_format SET error_info = '0' WHERE id = '{$id}'";
//         	$res = $db->execute($sql);

//         	//日志
// 			$do = '[Format] 修改OMS_ID：'.$oms_id.' 的商品代码：'.$old_key.' 为 '.$new_key;
// 			oms_log($u_name,$do,'amazon_format');
//         	echo 'ok';
//         }
// 	}
// }

// 验证格式化表通过
if(isset($_GET['check_format_ok'])){
	$store = $_GET['check_format_ok'];
	$sql = "SELECT count(1) as error_count FROM amazon_format WHERE store_name = '{$store}' AND error_info <> '0'";
	$res = $db->getOne($sql);
	$error_count = $res['error_count'];
	if($error_count == 0){
		echo 'ok';
	}else{
		echo 'no';
	}
}

// 转入代发货
if(isset($_GET['to_ready_send'])){
	
}