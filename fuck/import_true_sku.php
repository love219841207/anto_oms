<?php
require_once("header.php");
require_once("PHPExcel/PHPExcel.php");//引入PHPExcel
//加大响应
set_time_limit(0); 
ini_set("memory_limit", "1024M"); 

if(isset($_GET['import_file'])){
    $file_name = $_GET['import_file'];

	//清空amz_mail表
    $sql = "TRUNCATE true_sku;";
    $res = $db->execute($sql);

	//开始读取xlsx文件并导入
	$filename=$dir."/../uploads/true_sku.xlsx";		
	$objPHPExcel=PHPExcel_IOFactory::Load($filename);
	$sheet = $objPHPExcel->getSheet(0); //只读取第一个表
	//开始读取表格
	$highestRow = $sheet->getHighestRow();           //取得总行数 
	$highestColumn = 'C'; //取得总列数

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
        
    	//插入
 		$sql = "INSERT INTO true_sku (sku,goods_code,store) VALUES('{$strs[0]}','{$strs[1]}','{$strs[2]}')";
 		$res = $db->execute($sql);

	}

	echo 'ok';
}

//检测邮编
function look_true_sku(){
	$db = new PdoMySQL();
	//查询导入总数
	$sql = "SELECT count(1) as insert_num FROM true_sku";
	$res = $db->getOne($sql);
	$insert_num = $res['insert_num'];

	//检测查询
	$sql = "SELECT * FROM true_sku ORDER BY id DESC LIMIT 0,100";
	$res = $db->getAll($sql);
	$look_data1 = $res;

	$sql = "SELECT * FROM true_sku ORDER BY id LIMIT 0,1";
	$res = $db->getAll($sql);
	$look_data2 = $res;

	//final_res
	$final_res['status'] = 'ok';
	$final_res['insert_num'] = $insert_num;
	$final_res['look_data1'] = $look_data1;
	$final_res['look_data2'] = $look_data2;
	echo json_encode($final_res);
}

if(isset($_GET['look_true_sku'])){
	look_true_sku();
}