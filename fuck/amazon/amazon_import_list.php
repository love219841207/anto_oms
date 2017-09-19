<?php
require_once("../header.php");
require_once("../PHPExcel/PHPExcel.php");//引入PHPExcel
require_once("../log.php");
//加大响应
set_time_limit(0); 
ini_set("memory_limit", "1024M"); 

//导入亚马逊订单 = 手动
if(isset($_GET['import_add_list'])){
	$file = $dir."/../uploads/amazon_add_list.txt";
    $lines = file_get_contents($file); 
    $lines = mb_convert_encoding($lines, 'utf-8', 'Shift-JIS');	//编码转换
    ini_set('memory_limit', '-1');	//不要限制Mem大小，否则会报错 
    $arr=explode("\n",$lines);

    //获取店铺
    $store = addslashes($_GET['store']);

    //日志
	$do = '[START] 导入订单：'.$store;
	oms_log($u_name,$do,'amazon_import','amazon',$store,'-');
    
    //所有oms_has状态变成0
 	$sql = "UPDATE amazon_response_list SET oms_has_me = '0'";
    $res = $db->execute($sql);

    //清空导入表
    $sql = "TRUNCATE amazon_import_list";
    $res = $db->execute($sql);

    //初始化数
    $count_order = 0;
	$insert_count = 0;
	$has_count = 0;
    array_shift($arr);	//删除标题
    foreach ($arr as $key => $value) {
    	$new_arr = explode("	",$value);	//分割字段

    	if(count($new_arr) == '36'){	//如果为36个字段，则数据正确
    		//debug
    		// print_r($new_arr);echo '<hr>'; 
    		// echo $new_arr[21];
    		//计算总数
    		$count_order = $count_order + 1;

			//获取订单信息
			$order_id = $new_arr[0];	#订单号
			$purchase_date = $new_arr[2];	#购买时间
			$buyer_email = $new_arr[4];		#购买人邮箱
			$buyer_name = $new_arr[5];	#购买人
			#$buyer_phone = $new_arr[6];	#不全，删
			$sku = $new_arr[7];	#sku
			$goods_title = $new_arr[8];	#商品名
			$goods_num = $new_arr[9];	#购买数量
			$currency = $new_arr[10];	#货币种类
			$unit_price = $new_arr[11]/$goods_num;	#金额
			$item_tax = $new_arr[12];	#税
			$shipping_price = $new_arr[13];	#运费
			$shipping_tax = $new_arr[14];	#运费税
			if($new_arr[15]=='Standard'){
				$order_type = 'StandardOrder';	#默认stand 运费种类
			}else{
				$order_type = 'Other';
			}
			$receive_name = $new_arr[16];	#收货人
			$address = $new_arr[20].$new_arr[21].$new_arr[17].$new_arr[18].$new_arr[19];	#配送地址
			$post_code = $new_arr[22];	#邮编
			$country = $new_arr[23];	#国家
			$receive_phone = $new_arr[24];	#配送电话
			if(@$receive_phone == ''){

			}else{
				//格式化电话号码
				$receive_phone = str_replace("-","",$receive_phone);	//替换-
				$receive_phone = str_replace(" ","",$receive_phone);	//替换空格
				//计算电话长度
				$receive_phone_len = strlen($receive_phone);
				if($receive_phone_len == 11 or $receive_phone_len == 10){
					$re3 = substr($receive_phone,-4,4);//从后往前截取4位
					$re2 = substr($receive_phone,-8,4);//从后往前截取4位
					$re1 = str_replace($re2.$re3, '', $receive_phone);//截取前几位
					$receive_phone = $re1.'-'.$re2.'-'.$re3;
				}else{
					$receive_phone = '';
				}
			}
			$item_promotion_discount = $new_arr[25];	#可能对应Gift
			$ship_promotion_discount = $new_arr[27];	#对应Shipping_discount
			$payment_method = $new_arr[30];	#代引
			$pay_money = $new_arr[31];	#代引金额
			$gc = $new_arr[32];	#亚马逊GC
			$cod_money = $new_arr[33];	#cod费

			// 查询是否存在此订单
			$sql = "SELECT * from amazon_response_list WHERE order_id = '{$order_id}'";
			$res = $db->getOne($sql);

		    if(empty($res)){
		    	//记录到导入订单列表中
		    	$sql = "INSERT INTO amazon_import_list (
		    		store,
		    		order_id,
					purchase_date,
					buyer_email,
					buyer_name,
					sku,
					goods_title,
					goods_num,
					currency,
					unit_price,
					item_tax,
					shipping_price,
					shipping_tax,
					order_type,
					receive_name,
					address,
					post_code,
					country,
					receive_phone,
					item_promotion_discount,
					ship_promotion_discount,
					payment_method,
					pay_money,
					cod_money,
					coupon
				) VALUES (
					'{$store}',
					'{$order_id}',
					'{$purchase_date}',
					'{$buyer_email}',
					'{$buyer_name}',
					'{$sku}',
					'{$goods_title}',
					'{$goods_num}',
					'{$currency}',
					'{$unit_price}',
					'{$item_tax}',
					'{$shipping_price}',
					'{$shipping_tax}',
					'{$order_type}',
					'{$receive_name}',
					'{$address}',
					'{$post_code}',
					'{$country}',
					'{$receive_phone}',
					'{$item_promotion_discount}',
					'{$ship_promotion_discount}',
					'{$payment_method}',
					'{$pay_money}',
					'{$cod_money}',
					'{$gc}'
				)";
				$res = $db->execute($sql);

		    	$insert_count = $insert_count + 1;

		    	//日志
				$do = '[ING] 导入订单：'.$order_id.' | 收件人：'.$receive_name.' | 商品：'.$sku.'*'.$goods_num;
				oms_log($u_name,$do,'amazon_import','amazon',$store,'-');

		    }else{
		    	$sql = "UPDATE amazon_response_list SET oms_has_me = 'has' WHERE order_id = '{$order_id}'";
		    	$res = $db->execute($sql);
		    	$has_count = $has_count + 1;
		    	usleep(50000);
		    	continue;
		    }
		}

    }
    //计算单品总金额
    $sql = "UPDATE amazon_import_list SET item_total_money = unit_price * goods_num + item_tax + shipping_price + shipping_tax - item_promotion_discount - ship_promotion_discount";
    $res = $db->execute($sql);
    usleep(50000);
    //清空缓存
    $sql = "TRUNCATE amazon_total_price";
    $res = $db->execute($sql);
    usleep(50000);
    //缓存订单总金额和总代引金额
    $sql = "INSERT INTO amazon_total_price (order_id,order_total_money,pay_money) SELECT order_id,sum(item_total_money),sum(pay_money) FROM amazon_import_list GROUP BY order_id";
    $res = $db->execute($sql);
    usleep(50000);
    //更新订单总金额
    $sql = "UPDATE amazon_import_list pp,amazon_total_price tt SET pp.order_total_money = tt.order_total_money WHERE pp.order_id = tt.order_id";
    $res = $db->execute($sql);
    usleep(50000);

    //获取当前日期
    $today = date('y-m-d',time());
    //插入主response_list订单表
    $sql = "INSERT INTO amazon_response_list (
    	store,
    	syn_day,
    	order_type,
    	purchase_date,
    	order_id,
    	order_line,
    	buyer_name,
    	buyer_email,
    	country,
    	order_total_currency,
    	order_total_money,
    	payment_method,
    	pay_money,
    	phone,
    	post_code,
    	address,
    	receive_name,
    	oms_order_info_status,
    	send_id,
    	coupon
    ) SELECT 
    	store,
		'{$today}',
		order_type,
		purchase_date,
		order_id,
		'1',
		buyer_name,
		buyer_email,
		country,
		currency,
		order_total_money,
		payment_method,
		pay_money,
		receive_phone,
		post_code,
		address,
		receive_name,
		'ok',
		'ready',
		coupon
	FROM amazon_import_list GROUP BY order_id";
	$res = $db->execute($sql);

	//更新send_id
	$sql = "UPDATE amazon_response_list SET send_id = concat('amz',id) WHERE send_id = 'ready'";
	$res = $db->execute($sql);

	// 当前时间戳
	$now_time = time();

	//插入详情
	$sql = "INSERT INTO amazon_response_info (
		store,
		order_id,
		goods_title,
		sku,
		goods_num,
		shipping_price,
		shipping_tax,
		item_price,
		unit_price,
		item_tax,
		promotion_discount,
		shipping_discount,
		cod_money,
		import_time
	) SELECT
		store,
		order_id,
		goods_title,
		sku,
		goods_num,
		shipping_price,
		shipping_tax,
		item_total_money,-- 这里是计算后所得结果
		unit_price,
		item_tax,
		item_promotion_discount,
		ship_promotion_discount,
		cod_money,
		{$now_time}
	FROM amazon_import_list";
	$res = $db->execute($sql);

    //final_res
	$final_res['status'] = 'ok';
	$final_res['count_order'] = $count_order;
	$final_res['insert_count'] = $insert_count;
	$final_res['has_count'] = $has_count;

	//日志
	$do = '[END] 导入订单：'.$store.' 总单数：'.$count_order.' | 导入数：'.$insert_count.' | 已存在：'.$has_count;
	oms_log($u_name,$do,'amazon_import','amazon',$store,'-');

	echo json_encode($final_res);
}