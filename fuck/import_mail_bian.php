<?php
require_once("header.php");
require_once("PHPExcel/PHPExcel.php");//引入PHPExcel
//加大响应
set_time_limit(0); 
ini_set("memory_limit", "1024M"); 

if(isset($_GET['import_file'])){
    $file_name = $_GET['import_file'];

	//导入亚马逊mail表
	if($file_name == 'mail_table'){
	    $data_type = $_GET['data_type'];
	    if($data_type == 'all'){
	    	//清空amz_mail表
		    $sql = "TRUNCATE amz_mail;";
		    $res = $db->execute($sql);
	    }
	    if($data_type == 'add' or $data_type == 'all'){
	    	//开始读取xlsx文件并导入
			$filename=$dir."/../uploads/mail_table.xlsx";		
			$objPHPExcel=PHPExcel_IOFactory::Load($filename);
			$sheet = $objPHPExcel->getSheet(0); //只读取第一个表
			//开始读取表格
			$highestRow = $sheet->getHighestRow();           //取得总行数 
			$highestColumn = 'C'; //取得总列数

			//初始化总数
			$insert_num = "0";

			//初始化不存在
			$no_sku = "";
			for($j=2;$j<=$highestRow;$j++){    //从第2行开始读取数据
		    	$str="";
		        for($k='A';$k<=$highestColumn;$k++)    //从A列读取数据
				{ 
					$str .=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
				} 
		        $str=mb_convert_encoding($str,'utf8','auto');//根据自己编码修改
		        $strs = explode("|*|",$str);
		        // 查询库存系统是否存在
	            $sql = "SELECT count(1) as c_count FROM goods_type WHERE goods_code = '{$strs[0]}'";
	            $res = $rdb->getOne($sql);

	            if($res['c_count'] == 1){
	            	// 查询是否存在
	            	$sql = "SELECT count(1) as cc_count FROM amz_mail WHERE goods_code = '{$strs[0]}'";
	            	$res = $db->getOne($sql);
	            	if($res['cc_count'] == 1){
	            		// 如果存在，更新
	            		$sql = "UPDATE amz_mail set own_key = '{$strs[1]}',mail_type = '{$strs[2]}' WHERE goods_code = '{$strs[0]}'";
	            		$res = $db->execute($sql);
	            	}else{
	            		//插入
			 			$sql = "INSERT INTO amz_mail (goods_code,own_key,mail_type) VALUES('{$strs[0]}','{$strs[1]}','{$strs[2]}')";
			 			$res = $db->execute($sql);
	            	}
	            }else{
	            	$no_sku = $no_sku.' '.$strs[0];
	            	continue;
	            }

			}
	    }

	    if($data_type == 'del'){

	    	//开始读取xlsx文件并导入
			$filename=$dir."/../uploads/mail_table.xlsx";		
			$objPHPExcel=PHPExcel_IOFactory::Load($filename);
			$sheet = $objPHPExcel->getSheet(0); //只读取第一个表
			//开始读取表格
			$highestRow = $sheet->getHighestRow();           //取得总行数 
			$highestColumn = 'C'; //取得总列数

			//初始化总数
			$insert_num = "0";
			$no_sku = "";
			for($j=2;$j<=$highestRow;$j++){    //从第2行开始读取数据
		    	$str="";
		        for($k='A';$k<=$highestColumn;$k++)    //从A列读取数据
				{ 
					$str .=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'|*|';//读取单元格
				} 
		        $str=mb_convert_encoding($str,'utf8','auto');//根据自己编码修改
		        $strs = explode("|*|",$str);

            	// 查询是否存在
            	$sql = "SELECT count(1) as c_count FROM amz_mail WHERE goods_code = '{$strs[0]}'";
            	$res = $db->getOne($sql);
            	if($res['c_count'] == 1){
            		// 如果存在，删除
            		$sql = "DELETE FROM amz_mail WHERE goods_code = '{$strs[0]}'";
            		$res = $db->execute($sql);
            	}else{
            		
            	}

			}
	    }
		
	}

	if($no_sku == ''){
		echo 'ok';
	}else{
		echo $no_sku;
	}
	
}

//检测邮编
function look_amz_mail(){
	$db = new PdoMySQL();
	//查询导入总数
	$sql = "SELECT count(1) as insert_num FROM amz_mail";
	$res = $db->getOne($sql);
	$insert_num = $res['insert_num'];

	//检测查询
	$sql = "SELECT * FROM amz_mail ORDER BY id DESC LIMIT 0,100";
	$res = $db->getAll($sql);
	$look_data1 = $res;

	$sql = "SELECT * FROM amz_mail ORDER BY id LIMIT 0,1";
	$res = $db->getAll($sql);
	$look_data2 = $res;

	//final_res
	$final_res['status'] = 'ok';
	$final_res['insert_num'] = $insert_num;
	$final_res['look_data1'] = $look_data1;
	$final_res['look_data2'] = $look_data2;
	echo json_encode($final_res);
}

if(isset($_GET['look_amz_mail'])){
	look_amz_mail();
}

// 下载mail表
if(isset($_POST['down_amz_mail'])){
	require_once($dir."/./PHPExcel/PHPExcel.php");//引入PHPExcel

    //PHPExcel
    $objPHPExcel = new PHPExcel();
    $objSheet = $objPHPExcel->getActiveSheet();
    $objSheet->setCellValue("A1","出品者SKU")
            ->setCellValue("B1","体积")
            ->setCellValue("C1","类型");    //表头值
    $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(12);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:D')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objSheet->getStyle('A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objSheet->getStyle('A1:D1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    $objSheet->getColumnDimension('A')->setWidth(34);//单元格宽
    $objSheet->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐

    //SQL
    $sql = "SELECT * FROM amz_mail";
    $res = $db->getAll($sql);

    $j=2;
    foreach ($res as $key => $value) {
        $objSheet->setCellValue("A".$j,$value['goods_code'])
                ->setCellValue("B".$j,$value['own_key'])
                ->setCellValue("C".$j,$value['mail_type']);
        $j++;
    }

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../down/mail_table.xlsx");   //保存在服务器
    echo "ok";
}