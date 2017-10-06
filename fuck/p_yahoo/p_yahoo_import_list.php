<?php
require_once("../header.php");
require_once("../PHPExcel/PHPExcel.php");//引入PHPExcel
require_once("../log.php");
//加大响应
set_time_limit(0); 
ini_set("memory_limit", "1024M"); 

//导入雅虎拍卖订单 = 手动
if(isset($_GET['import_add_list'])){

	//开始读取xlsx文件并导入
	$filename = $dir."/../uploads/p_yahoo_add_list.xlsx";
	$objPHPExcel=PHPExcel_IOFactory::Load($filename);
	$sheet = $objPHPExcel->getSheet(0); //只读取第一个表
	//开始读取表格
	$highestRow = $sheet->getHighestRow();           //取得总行数 
	$highestColumn = $sheet->getHighestColumn(); // 取得总列数
	++$highestColumn;

	//获取店铺
	$store = addslashes($_GET['store']);

	//日志
	$do = '[START] 导入订单：'.$store;
	oms_log($u_name,$do,'p_yahoo_import','p_yahoo',$store,'-');

	//清空导入表
	$sql = "TRUNCATE p_yahoo_import_list";
	$res = $db->execute($sql);
	// $sql = "TRUNCATE p_yahoo_response_list";
	// $res = $db->execute($sql);
	// $sql = "TRUNCATE p_yahoo_response_info";
	// $res = $db->execute($sql);

	//所有oms_has状态变成0
	$sql = "UPDATE p_yahoo_response_list SET oms_has_me = '0'";
	$res = $db->execute($sql);

	//初始化数
	$count_order = 0;
	$insert_count = 0;
	$has_count = 0;

	for($j=2;$j<=$highestRow;$j++){    //从第2行开始读取数据
		$str="";
		for($k='A';$k!= $highestColumn;$k++)    //从A列读取数据
		{ 
			$str .=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
		} 
		$str=mb_convert_encoding($str,'utf8','auto');//根据自己编码修改
		$strs = explode("|*|",$str);

		$order_id = $strs[0];
		$purchase_date = $strs[3];
		$buyer_phone = $strs[75];
		$buyer_post_code = $strs[70];
		$buyer_address = addslashes($strs[71].$strs[73]);
		$buyer_name = $strs[66];
		$order_payment_method = $strs[15];
		$buyer_others = $strs[16];
		$goods_title = $strs[21].'@'.$strs[20];
		$sku = $strs[20];
		$goods_num = $strs[18];
		$unit_price = $strs[22];
		$item_total_money = $strs[24];
		$shipping_price = $strs[25];
		$cod_money = $strs[26];
		$want_date = $strs[29];
		$want_time = $strs[30];
		$who_id = $strs[32];
		$buyer_email = $strs[33];
		$address = addslashes($strs[6].$strs[8]);
		$receive_name = $strs[12];
		$receive_phone = $strs[4];
		$post_code = $strs[5];
		$buyer_send_method = $strs[85];
		$pay_money = $strs[19];
		$order_total_money = $strs[19];
		$payment_method = $strs[15];
		$payment_method = str_replace('商品代引','COD',$payment_method);
		
		$item_tax = '';
		$points = '';
		$coupon = '';

		// 提取运费代码
		$yfcode = substr($sku,0,1);


		//计算总数
		$count_order = $count_order + 1;

		// 查询是否存在此订单
		$sql = "SELECT * from p_yahoo_response_list WHERE order_id = '{$order_id}'";
		$res = $db->getOne($sql);

		if(empty($res)){
				
				// 拆分赠品开始 - - - - - - - - - - - - - - - - 
				// $sku_copy = str_replace($yfcode.'-', '', $sku);
				$sku_copy = preg_replace('/'.$yfcode.'-'.'/', '', $sku, 1); 
				$res_sku = explode('_', $sku_copy); 

				// 主商品代码    
				$count_goods_code = count($res_sku);
				$goods_code_main = trim($res_sku[$count_goods_code-1]);

				// if($strs[1]==='新規受付'){   //只导入新规订单
				$sql = "INSERT INTO p_yahoo_import_list(
					store,          #店铺名
					order_id,           #订单号
					order_payment_method,   #信用卡结算状态
					buyer_others,   #客人备注（配送）
					purchase_date,  #订单日期
					item_total_money,   #合计
					item_tax,   #消费税
					shipping_price, #运费
					cod_money,  #代引手续费
					pay_money,  #代引金额
					order_total_money,  #合计金额
					points, #积分
					goods_title,    #商品名
					sku,    #sku
					goods_num,  #购买数
					unit_price, #单价
					buyer_post_code,    #购买者邮编  
					buyer_address,  #购买者地址
					who_id,
					buyer_name, #购买人
					buyer_phone,    #购买人手机
					buyer_email,    #购买人邮箱
					post_code,  #收件邮编
					address,    #收件地址
					receive_name,   #收件人姓名
					receive_phone,  #收件人电话
					payment_method, #支付方式
					buyer_send_method,  #客人要求配送方式
					coupon,
					yfcode,
					want_date,
					want_time
				)VALUES(
					'{$store}', #店铺名
					'{$order_id}',  #订单号
					'{$order_payment_method}',  #信用卡结算状态    
					'{$buyer_others}',  #客人备注（配送）
					'{$purchase_date}', #订单日期
					'{$item_total_money}',  #合计
					'{$item_tax}',  #消费税
					'{$shipping_price}',    #运费
					'{$cod_money}', #代引手续费
					'{$pay_money}', #代引金额
					'{$order_total_money}', #合计金额
					'{$points}',    #积分
					'{$goods_title}',   #商品名
					'{$goods_code_main}',   #sku
					'{$goods_num}', #购买数
					'{$unit_price}',    #单价
					'{$buyer_post_code}',   #购买人邮编
					'{$buyer_address}', #购买者地址
					'{$who_id}',    #客人ID
					'{$buyer_name}',    #购买人
					'{$buyer_phone}',   #购买人手机
					'{$buyer_email}',   #购买人邮箱
					'{$post_code}', #收件邮编
					'{$address}',   #收件地址
					'{$receive_name}',  #收件姓名
					'{$receive_phone}', #收件人电话
					'{$payment_method}',    #支付方式
					'{$buyer_send_method}', #客人要求配送方式
					'{$coupon}',    #优惠券
					'{$yfcode}',
					'{$want_date}',
					'{$want_time}'
				)";
				$res = $db->execute($sql);

				// 赠品
				
				if($count_goods_code == 1){

				}else{
					for($i=0; $i<$count_goods_code-1; $i++){
						$now_goods_code = trim($res_sku[$i]);
						// if($strs[1]==='新規受付'){   //只导入新规订单
						$sql = "INSERT INTO p_yahoo_import_list(
							store,          #店铺名
							order_id,           #订单号
							order_payment_method,   #信用卡结算状态
							buyer_others,   #客人备注（配送）
							purchase_date,  #订单日期
							item_total_money,   #合计
							item_tax,   #消费税
							shipping_price, #运费
							cod_money,  #代引手续费
							pay_money,  #代引金额
							order_total_money,  #合计金额
							points, #积分
							goods_title,    #商品名
							sku,    #sku
							goods_num,  #购买数
							unit_price, #单价
							buyer_post_code,    #购买者邮编  
							buyer_address,  #购买者地址
							who_id,
							buyer_name, #购买人
							buyer_phone,    #购买人手机
							buyer_email,    #购买人邮箱
							post_code,  #收件邮编
							address,    #收件地址
							receive_name,   #收件人姓名
							receive_phone,  #收件人电话
							payment_method, #支付方式
							buyer_send_method,  #客人要求配送方式
							coupon,
							yfcode,
							want_date,
							want_time
						)VALUES(
							'{$store}', #店铺名
							'{$order_id}',  #订单号
							'{$order_payment_method}',  #信用卡结算状态    
							'{$buyer_others}',  #客人备注（配送）
							'{$purchase_date}', #订单日期
							'0',    #合计
							'0',    #消费税
							'0',    #运费
							'0',    #代引手续费
							'0',    #代引金额
							'0',    #合计金额
							'0',    #积分
							'{$goods_title}',   #商品名
							'{$now_goods_code}',    #sku
							'{$goods_num}', #购买数
							'0',    #单价
							'{$buyer_post_code}',   #购买人邮编
							'{$buyer_address}', #购买者地址
							'{$who_id}',
							'{$buyer_name}',    #购买人
							'{$buyer_phone}',   #购买人手机
							'{$buyer_email}',   #购买人邮箱
							'{$post_code}', #收件邮编
							'{$address}',   #收件地址
							'{$receive_name}',  #收件姓名
							'{$receive_phone}', #收件人电话
							'{$payment_method}',    #支付方式
							'{$buyer_send_method}', #客人要求配送方式
							'{$coupon}',    #优惠券
							'{$yfcode}',
							'{$want_date}',
							'{$want_time}'
						)";
						$res = $db->execute($sql);
					// }
					}
				}
				
				// 拆分赠品结束 - - - - - - - - - - - - - - - - 

				$insert_count = $insert_count + 1;

				//日志
				$do = '[ING] 导入订单：'.$order_id.' | 收件人：'.$receive_name.' | 商品：'.$sku.'*'.$goods_num;
				oms_log($u_name,$do,'p_yahoo_import','p_yahoo',$store,'-');
				// }

			}else{
				$sql = "UPDATE p_yahoo_response_list SET oms_has_me = 'has' WHERE order_id = '{$order_id}'";
				$res = $db->execute($sql);
				$has_count = $has_count + 1;
				usleep(50000);
				continue;
			}
	}

	//获取当前日期
	$today = date('y-m-d',time());
	//插入主response_list订单表
	$sql = "INSERT INTO p_yahoo_response_list (
		store,
		syn_day,    #同步日期（导入日期）
		order_id,   #订单号
		order_line, #order_line
		order_payment_method,   #信用卡结算状态
		buyer_others,   #购买者配送备注
		buyer_send_method,  #购买者要求配送方式
		purchase_date,  #订单付款日期
		who_id,
		buyer_name, #购买人
		buyer_email,    #邮箱
		buyer_phone,    #购买人手机
		buyer_address,  #购买人地址
		buyer_post_code,    #购买人邮编
		order_total_money,  #订单总金额
		coupon, #优惠券
		points, #积分
		shipping_price, #运费
		order_tax,  #消费税
		payment_method, #付款方式
		pay_money,  #代引金额
		phone,  #配送手机
		post_code,  #配送邮编
		address,    #配送地址
		receive_name,   #收件人
		oms_order_info_status,
		send_id,
		want_date,
		want_time
	) SELECT 
		store,
		'{$today}',
		order_id,
		CASE WHEN payment_method = 'COD' THEN '1' ELSE '-2' END,
		order_payment_method,
		buyer_others,
		buyer_send_method,
		purchase_date,
		who_id,
		buyer_name,
		buyer_email,    #邮箱
		buyer_phone,    #购买人手机
		buyer_address,  #购买人地址
		buyer_post_code,    #购买人邮编
		order_total_money,  #订单总金额
		coupon, #优惠券
		points, #积分
		shipping_price, #运费
		item_tax,   #消费税
		payment_method, #付款方式
		pay_money,  #代引金额
		receive_phone,  #配送手机
		post_code,  #配送邮编
		address,    #配送地址
		receive_name,   #收件人
		'ok',
		'ready',
		want_date,
		want_time
	FROM p_yahoo_import_list GROUP BY order_id";
	$res = $db->execute($sql);

	//更新send_id
	$sql = "UPDATE p_yahoo_response_list SET send_id = concat('pya',id) WHERE send_id = 'ready'";
	$res = $db->execute($sql);

	// 当前时间戳
	$now_time = time();

	//插入详情
	$sql = "INSERT INTO p_yahoo_response_info (
		store,
		order_id,
		goods_title,
		sku,
		goods_code,
		goods_num,
		item_price,
		unit_price,
		item_tax,
		cod_money,
		import_time,
		yfcode
	) SELECT
		store,
		order_id,
		goods_title,
		sku,
		goods_code,
		goods_num,
		goods_num*unit_price,
		unit_price,
		item_tax,
		cod_money,
		{$now_time},
		yfcode
	FROM p_yahoo_import_list";
	$res = $db->execute($sql);

	// 更新info.csv
	$filename2 = $dir."/../uploads/p_yahoo_add_info.csv";

	function fgetcsv_reg(& $handle, $length = null, $d = ',', $e = '"') {
	$d = preg_quote($d);
	$e = preg_quote($e);
	$_line = "";
	$eof=false;
	while ($eof != true) {
	$_line .= (empty ($length) ? fgets($handle) : fgets($handle, $length));
	$itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
	if ($itemcnt % 2 == 0)
	$eof = true;
	}
	$_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
	$_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
	preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
	$_csv_data = $_csv_matches[1];
	for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++) {
	$_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1', $_csv_data[$_csv_i]);
	$_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
	}
	return empty ($_line) ? false : $_csv_data;
	}

	$file = fopen($filename2,'r'); 
	while ($data = fgetcsv_reg($file)) {
		$goods_list[] = $data;
	}
	array_shift($goods_list);
	foreach ($goods_list as $arr){
		// var_dump($arr);
		// echo count($arr);echo ' # ';
		if(is_array($arr) && !empty($arr)){
			$str = '';
			for($i=0; $i<35; $i++){
				// echo $arr[$i]."<br>";
				$str .= $arr[$i]."|*|";
			}
			$str=mb_convert_encoding($str,"UTF-8","shift-jis");
			$strs = explode("|*|",$str);
			$order_id = $strs[0].'-'.$strs[1];
			$buyer_others = $strs[31];

			// 查询是否存在此订单
			$sql = "UPDATE p_yahoo_response_list SET buyer_others = '{$buyer_others}' WHERE order_id = '{$order_id}'";
			$res = $db->execute($sql);
		}
	}

	//final_res
	$final_res['status'] = 'ok';
	$final_res['count_order'] = $count_order;
	$final_res['insert_count'] = $insert_count;
	$final_res['has_count'] = $has_count;

	//日志
	$do = '[END] 导入订单：'.$store.' 总单数：'.$count_order.' | 导入数：'.$insert_count.' | 已存在：'.$has_count;
	oms_log($u_name,$do,'p_yahoo_import','p_yahoo',$store,'-');

	echo json_encode($final_res);
}