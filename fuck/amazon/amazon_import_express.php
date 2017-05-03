<?php
require_once("../header.php");
require_once("../PHPExcel/PHPExcel.php");//引入PHPExcel
require_once("../log.php");
//加大响应
set_time_limit(0); 
ini_set("memory_limit", "1024M"); 

//导入快递单
if(isset($_GET['import_file'])){
	$import_file = $_GET['import_file'];
	$filename = $import_file.'.xlsx';
	//开始读取xlsx文件并导入
	$filename=$dir."/../uploads/".$filename;		
	$objPHPExcel=PHPExcel_IOFactory::Load($filename);
	$sheet = $objPHPExcel->getSheet(0); //只读取第一个表
	//开始读取表格
	$highestRow = $sheet->getHighestRow();           //取得总行数 
	$highestColumn = 'I'; //取得总列数

	//日志
	$do = '[START] 导入快递单：'.$import_file;
	oms_log($u_name,$do,'import_file');

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

		if($strs[0]==""){
        	//如果没有填入数目，则跳过
        }else{
        	//查询是否已经包含
            $sql = "SELECT count(1) as c_count FROM amazon_express WHERE order_id = '{$strs[0]}'";
            $res = $db->getOne($sql);

            if($res['c_count']==1){
            	$has_num = $has_num + 1;
            	continue;
            }else{
            	//插入
		 		$sql = "INSERT INTO amazon_express (order_id,express_name,express_method,express_num,is_money) VALUES('{$strs[0]}','{$strs[5]}','{$strs[7]}','{$strs[6]}','{$strs[8]}')";
		 		$res = $db->execute($sql);
		 		$insert_num = $insert_num + 1;	
            }
        } 
	}
	//日志
	$do = '[END] 导入快递单：'.$import_file;
	oms_log($u_name,$do,'import_file');

	//final_res
	$final_res['status'] = 'ok';
	$final_res['has_num'] = $has_num;
	$final_res['insert_num'] = $insert_num;
	echo json_encode($final_res);
}