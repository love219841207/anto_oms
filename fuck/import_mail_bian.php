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

		//清空amz_mail表
	    $sql = "TRUNCATE amz_mail;";
	    $res = $db->execute($sql);

		//开始读取xlsx文件并导入
		$filename=$dir."/../uploads/mail_table.xlsx";		
		$objPHPExcel=PHPExcel_IOFactory::Load($filename);
		$sheet = $objPHPExcel->getSheet(0); //只读取第一个表
		//开始读取表格
		$highestRow = $sheet->getHighestRow();           //取得总行数 
		$highestColumn = 'M'; //取得总列数

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
	        
            $sql = "SELECT count(1) as c_count FROM goods_type WHERE goods_code = '{$strs[0]}'";
            $res = $rdb->getOne($sql);

            if($res['c_count'] == 1){
            	//插入
		 		$sql = "INSERT INTO amz_mail (goods_code,own_key,mail_type) VALUES('{$strs[0]}','{$strs[1]}','{$strs[2]}')";
		 		$res = $db->execute($sql);
            	
            }else{
            	$no_sku = $no_sku.' '.$strs[0];
            	continue;
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