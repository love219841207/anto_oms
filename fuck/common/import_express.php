<?php
require_once("../header.php");
require_once("../PHPExcel/PHPExcel.php");//引入PHPExcel
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
// 清空快递单号
if(isset($_GET['truncate_yes'])){
	$sql = "DELETE FROM import_express WHERE express_status = '0'";
	$db->execute($sql);
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
		// //日志
	// $do = '[START] 导入快递单：'.$import_file;
	// oms_log($u_name,$do,'import_file');

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
			 		$sql = "INSERT INTO import_express (pack_id,express_name,express_url,express_num,express_date) VALUES('{$strs[12]}','佐川急便','http://k2k.sagawa-exp.co.jp/p/sagawa/web/okurijoinput.jsp','{$strs[0]}','{$strs[1]}')";
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
	            $sql = "SELECT count(1) as c_count FROM import_express WHERE pack_id = '{$strs[0]}'";
	            $res = $db->getOne($sql);

	            if($res['c_count']==1){
	            	$has_num = $has_num + 1;
	            	continue;
	            }else{
	            	//插入
			 		$sql = "INSERT INTO import_express (pack_id,express_name,express_url,express_num,express_date) VALUES('{$strs[0]}','ヤマト運輸','http://toi.kuronekoyamato.co.jp/cgi-bin/tneko','{$strs[3]}','{$strs[4]}')";
			 		$res = $db->execute($sql);
			 		$insert_num = $insert_num + 1;	
	            }
	        } 
		}
	}
	//日志
	// $do = '[END] 导入快递单：'.$import_file;
	// oms_log($u_name,$do,'import_file');

	//final_res
	$final_res['status'] = 'ok';
	$final_res['has_num'] = $has_num;
	$final_res['insert_num'] = $insert_num;
	echo json_encode($final_res);
}