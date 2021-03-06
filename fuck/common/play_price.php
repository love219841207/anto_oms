<?php
require_once("../header.php");

function play_order_price($station,$response_list,$response_info,$order_id){
	$db = new PdoMySQL();

	//查询该订单是否是COD订单
	$sql = "SELECT payment_method,send_id,order_line,shipping_price,order_tax FROM $response_list WHERE order_id = '{$order_id}'";
	$res = $db->getOne($sql);
	$payment_method = $res['payment_method'];
	$send_id = $res['send_id'];
	$order_line = $res['order_line'];
	$shipping_price = $res['shipping_price'];
	$order_tax = $res['order_tax'];
	
	// 更新子订单价格 = 数量 * 单价
	$sql = "UPDATE $response_info SET item_price = unit_price * goods_num WHERE order_id = '{$order_id}'";
	$res = $db->execute($sql);

	if($payment_method == 'COD'){
		// 如果是COD订单，计算总订单金额，不包括代引手续费
		$sql = "SELECT sum(item_price) as total_money FROM $response_info WHERE order_id='{$order_id}'";
		$res = $db->getOne($sql);
		$total_money = $res['total_money'];

		// 拿出代引手续费
		$sql = "SELECT cod_money FROM $response_info WHERE order_id='{$order_id}'";
		$res = $db->getOne($sql);
		$cod_money = $res['cod_money'];

		// 客人订单总金额（不包括运费） = 支付金额 + 代引手续费 + 订单税
		$total_money = $total_money + $cod_money + $order_tax;

		// 更新客人代引金额到 LIST , all_total_money 为合单后金额（包括运费），order_total_money 为订单金额不包含运费，pay_money = 客人实际支付金额 ，需减去积分和优惠券,加上运费
		$sql = "UPDATE $response_list SET all_total_money = '{$total_money}' + shipping_price,order_total_money = '{$total_money}',pay_money = '{$total_money}'-points-coupon+shipping_price  WHERE order_id = '{$order_id}'";
		$res = $db->execute($sql);

	}else{
		//查询出订单额，计算金额
		$sql = "SELECT sum(item_price) as total_money FROM $response_info WHERE order_id='{$order_id}'";
		$res = $db->getOne($sql);
		// 客人订单总金额（不包括运费） = 支付金额 + 订单税
		$total_money = $res['total_money'] + $order_tax;

		//更新total_money
		$sql = "UPDATE $response_list SET all_total_money = '{$total_money}' + shipping_price,order_total_money = '{$total_money}'  WHERE order_id='{$order_id}'";
		$res = $db->execute($sql);
	}

	// // 查询是否是合单
	if(strstr($send_id, 'H') == true){
		// 查询该单有几个
		$sql = "SELECT count(1) as count_h FROM $response_list WHERE send_id = '{$send_id}'";
		$res = $db->getOne($sql);
		$count_H = $res['count_h']-1;

		// 总合单金额计算 = 合单金额计算 - COD订单数 * COD费用 + 一个COD费用
		// 查询有合单中有几单是合单的费用
		$sql = "SELECT count(order_id) as count_cod FROM $response_list WHERE send_id = '{$send_id}' AND payment_method = 'COD'";
		$res = $db->getOne($sql);
		$count_cod = $res['count_cod'];

		$sql = "SELECT cod_money FROM $response_info WHERE order_id = '{$order_id}'";
		$res = $db->getOne($sql);
		$cod_money = $res['cod_money'];	// 提取代引手续费

		$all_cod_fee = $count_cod * $cod_money;

		// 查出总价
		$sql = "SELECT sum(order_total_money) as sum,sum(points+coupon) as has_pay,all_yfmoney FROM $response_list WHERE send_id = '{$send_id}'";
		$res = $db->getOne($sql);
		$sum_total_money = $res['sum'];
		$has_pay = $res['has_pay'];
		$all_yfmoney = $res['all_yfmoney'];

		// 合单金额 = 总订单金额 - 总COD手续费 + 一个COD手续费 + 总运费
		$all_fee = $sum_total_money - $all_cod_fee + $cod_money + $all_yfmoney;

		// 更新合单金额到 LIST
		$sql = "UPDATE $response_list SET all_total_money = $all_fee WHERE send_id = '{$send_id}'";
		$res = $db->execute($sql);

		//	合单并代引
		if($payment_method == 'COD'){
			// 算出非代引总价
			$sql = "SELECT sum(order_total_money) as normal_sum FROM $response_list WHERE send_id = '{$send_id}' AND payment_method <> 'COD'";
			$res = $db->getOne($sql);
			$normal_sum = $res['normal_sum'];

			// 算出代引总价
			$sql = "SELECT sum(points+coupon) as has_pay,all_yfmoney FROM $response_list WHERE send_id = '{$send_id}' AND payment_method = 'COD'";
			$res = $db->getOne($sql);
			$has_pay = $res['has_pay'];
			$all_yfmoney = $res['all_yfmoney'];

			// 客人最终需要付款 = 合单金额 - 正常订单总价 - 已经付过的积分（优惠券）已经包含运费
			$pay_money = $all_fee - $normal_sum - $has_pay;

			$sql = "UPDATE $response_list SET pay_money = '{$pay_money}' WHERE send_id='{$send_id}'";
			$res = $db->execute($sql);

		}else{
			
		}
		
	}
}
