<?php
require_once("../header.php");
require_once("../PHPExcel/PHPExcel.php");//引入PHPExcel
require_once("../log.php");

$dir = dirname(__FILE__);

set_time_limit(0);

// 更新快递单号
if(isset($_GET['up_express_order'])){
	// 更新单号和快递日期到 send_table，并且table_status = 2,express_status = 1
	$sql = "UPDATE send_table send,import_express import SET send.oms_order_express_num = import.express_num,send.express_day = import.express_date,send.table_status = '2',import.express_status = '1' WHERE import.pack_id = send.pack_id AND import.express_status = '0'";
	$res = $db->execute($sql);

	// 更新单号和快递日期到list表 三个平台次更新 order_line = 6
	$sql = "UPDATE amazon_response_list list,send_table send SET list.express_company = send.express_company,list.send_method = send.send_method,list.oms_order_express_num = send.oms_order_express_num,list.express_day = send.express_day,list.order_line = '6' WHERE list.order_id = send.order_id AND send.table_status = '2'";
	$res = $db->execute($sql);

	// 移动 table_status = 2 到已出单
	$sql = "INSERT INTO history_send (
				station,
				order_id,
				send_id,
				repo_status,
				oms_id,
				info_id,
				pack_id,
				sku,
				goods_code,
				out_num,
				pause_ch,
				pause_jp,
				receive_phone,
				receive_code,
				receive_house,
				receive_house1,
				receive_house2,
				receive_name,
				send_day,
				send_time,
				is_cod,
				due_money,
				express_company,
				send_method,
				order_method,
				need_not_send,
				who_email,
				store_name,
				holder,
				item_line,
				import_day,
				oms_order_express_num,
				express_day,
				back_status,
				table_status,
				other_1 
				)
			SELECT
				station,
				order_id,
				send_id,
				repo_status,
				oms_id,
				info_id,
				pack_id,
				sku,
				goods_code,
				out_num,
				pause_ch,
				pause_jp,
				who_tel,
				who_post,
				who_house,
				who_house1,
				who_house2,
				who_name,
				send_day,
				send_time,
				is_cod,
				due_money,
				express_company,
				send_method,
				order_method,
				need_not_send,
				who_email,
				store_name,
				holder,
				item_line,
				import_day,
				oms_order_express_num,
				express_day,
				back_status,
				table_status,
				other_1
			FROM send_table WHERE table_status = 2";
	$res = $db->execute($sql);

	// 删除 table_status = 2 的订单
	$sql = "DELETE FROM send_table WHERE table_status = 2";
	$res = $db->execute($sql);

	// 删除 express_status = 1 的订单
	$sql = "DELETE FROM import_express WHERE express_status = 1";
	$res = $db->execute($sql);

	// table_status = 2 的订单进行原信息匹配
		// 亚马逊匹配
		$sql = "UPDATE history_send history,amazon_response_list list SET history.buy_method = list.payment_method,history.who_name = list.buyer_name,history.total_money = list.all_total_money,history.buy_money = list.all_total_money WHERE history.order_id = list.order_id AND history.table_status = '2'";
		$res = $db->execute($sql);

		// 亚马逊匹配 bill
		$sql = "UPDATE history_send history,amazon_response_info info SET history.bill = info.cod_money WHERE history.info_id = info.id AND history.table_status = '2'";
		$res = $db->execute($sql);
		
		// 更新亚马逊 buy_method
		$sql = "UPDATE history_send SET buy_method = 'DirectPayment' WHERE station = 'amazon' AND buy_method = 'COD' AND table_status = '2'";
		$res = $db->execute($sql);
		$sql = "UPDATE history_send SET buy_method = 'Amazon決済（前払い）' WHERE station = 'amazon' AND is_cod <> 'COD' AND table_status = '2'";
		$res = $db->execute($sql);
		// 雅虎匹配
		// 乐天匹配
	$sql = "UPDATE history_send SET table_status = '3' WHERE table_status = '2'";
	$res = $db->execute($sql);

	echo 'ok';
}

// 清空快递单号
if(isset($_GET['truncate_yes'])){
	$sql = "DELETE FROM import_express WHERE express_status = '0'";
	$res = $db->execute($sql);
	echo 'ok';
}

if(isset($_GET['look_express'])){
	look_express();
}

//检测导入单号
function look_express(){
	$db = new PdoMySQL();
	//查询导入总数
	$sql = "SELECT count(1) as insert_num FROM import_express WHERE express_status = '0'";
	$res = $db->getOne($sql);
	$insert_num = $res['insert_num'];

	//检测查询
	$sql = "SELECT * FROM import_express WHERE express_status = '0' ORDER BY id DESC";
	$res = $db->getAll($sql);
	$look_data = $res;

	//final_res
	$final_res['status'] = 'ok';
	$final_res['insert_num'] = $insert_num;
	$final_res['look_data'] = $look_data;
	echo json_encode($final_res);
}

if(isset($_GET['import_express'])){
	$file_name = $_GET['import_express'];

	//佐川
	if($file_name == 'sagawa'){

		//开始读取xlsx文件并导入
		$filename=$dir."/../../uploads/sagawa.csv";		
		$objPHPExcel=PHPExcel_IOFactory::Load($filename);
		$sheet = $objPHPExcel->getSheet(0); //只读取第一个表
		//开始读取表格
		$highestRow = $sheet->getHighestRow();           //取得总行数 
		$highestColumn = 'M'; //取得总列数

		//初始化总数
		$insert_num = "0";
		//初始化已存在数
		$has_num = "0";
		for($j=2;$j<=$highestRow;$j++){    //从第2行开始读取数据
	    	$str="";
	        for($k='A';$k<=$highestColumn;$k++)    //从A列读取数据
			{ 
				$str .=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
			} 
	        $str=mb_convert_encoding($str,'utf8','auto');//根据自己编码修改
	        $strs = explode("|*|",$str);

	        $yy = substr ($strs[1],0,4);
	        $mm = substr ($strs[1],4,2);
	        $dd = substr ($strs[1],6,2);
	        $express_day = $yy.'-'.$mm.'-'.$dd;

			if($strs[0]==""){
	        	//如果没有填入数目，则跳过
	        }else{
	        	//查询是否已经包含
	            $sql = "SELECT count(1) as c_count FROM import_express WHERE pack_id = '{$strs[12]}'";
	            $res = $db->getOne($sql);

	            if($res['c_count']==1){
	            	$has_num = $has_num + 1;
	            	continue;
	            }else{
	            	//插入
			 		$sql = "INSERT INTO import_express (pack_id,express_name,express_url,express_num,express_date) VALUES('{$strs[12]}','佐川急便','http://k2k.sagawa-exp.co.jp/p/sagawa/web/okurijoinput.jsp','{$strs[0]}','{$express_day}')";
			 		$res = $db->execute($sql);
			 		$insert_num = $insert_num + 1;	
	            }
	        } 
		}
	}
	if($file_name == 'maildh'){
		//开始读取xlsx文件并导入
		$filename=$dir."/../../uploads/maildh.xls";		
		$objPHPExcel=PHPExcel_IOFactory::Load($filename);
		$sheet = $objPHPExcel->getSheet(0); //只读取第一个表
		//开始读取表格
		$highestRow = $sheet->getHighestRow();           //取得总行数 
		$highestColumn = 'E'; //取得总列数

		//初始化总数
		$insert_num = "0";
		//初始化已存在数
		$has_num = "0";
		for($j=1;$j<=$highestRow;$j++){    //从第1行开始读取数据
	    	$str="";
	        for($k='A';$k<=$highestColumn;$k++)    //从A列读取数据
			{ 
				$str .=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
			} 
	        $str=mb_convert_encoding($str,'utf8','auto');//根据自己编码修改
	        $strs = explode("|*|",$str);

	        $express_date = str_replace('/', '-', $strs[4]);

			if($strs[0]==""){
	        	//如果没有填入数目，则跳过
	        }else{
	        	//查询是否已经包含
	            $sql = "SELECT count(1) as c_count FROM import_express WHERE pack_id = '{$strs[0]}'";
	            $res = $db->getOne($sql);

	            if($res['c_count']==1){
	            	$has_num = $has_num + 1;
	            	continue;
	            }else{
	            	//插入
			 		$sql = "INSERT INTO import_express (pack_id,express_name,express_url,express_num,express_date) VALUES('{$strs[0]}','ヤマト運輸','http://toi.kuronekoyamato.co.jp/cgi-bin/tneko','{$strs[3]}','{$express_date}')";
			 		$res = $db->execute($sql);
			 		$insert_num = $insert_num + 1;	
	            }
	        } 
		}
	}

	//final_res
	$final_res['status'] = 'ok';
	$final_res['has_num'] = $has_num;
	$final_res['insert_num'] = $insert_num;
	echo json_encode($final_res);
}