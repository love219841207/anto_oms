<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);


if(isset($_GET['make_send'])){
	// 下载方式
	$down_type = $_GET['type'];

	//获取公司
	$select_company = $_GET['select_company'];

	//获取仓库
	$select_repo = $_GET['select_repo'];

	// 获取日期区间
	$start = $_GET['s_date'];
	$end = $_GET['e_date'];
	$the_date = $start."#".$end;

	require_once($dir."/../PHPExcel/PHPExcel.php");	//引入PHPExcel
    
    //制作时间
    date_default_timezone_set("Asia/Shanghai");
    $now_time = date("Y-m-d H'i's");

    //PHPExcel
    $objPHPExcel = new PHPExcel();
    $objSheet = $objPHPExcel->getActiveSheet();
    $objSheet->setTitle($select_company.$select_repo.$now_time);//表名
    $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(12);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:B')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:B1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objSheet->getStyle('A1:B1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objSheet->getStyle('A1:B1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    $objSheet->getColumnDimension('A')->setWidth(34);//单元格宽
    $objSheet->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐

	//下载表格
	if($select_company == 'heimao'){
		// 如果是黑猫
        $objSheet->setCellValue("A1","住所録コ-ド")->setCellValue("B1","お届け先電話番号")->setCellValue("C1","お届け先郵便番号")->setCellValue("D1","お届け先住所1")->setCellValue("E1","お届け先住所2")->setCellValue("F1","お届け先住所3")->setCellValue("G1","お届け先名称1")->setCellValue("H1","お届け先名称2")->setCellValue("I1","お客様管理ナンバ-")->setCellValue("J1","お客様コ-ド")->setCellValue("K1","部署・担当者")->setCellValue("L1","荷送人電話番号")->setCellValue("M1","ご依頼主電話番号")->setCellValue("N1","ご依頼主郵便番号")->setCellValue("O1","ご依頼主住所1")->setCellValue("P1","ご依頼主住所２")->setCellValue("Q1","ご依頼主名称1")->setCellValue("R1","ご依頼主名称2")->setCellValue("S1","荷姿コ-ド")->setCellValue("T1","品名1")->setCellValue("U1","品名2")->setCellValue("V1","品名3")->setCellValue("W1","品名4")->setCellValue("X1","品名5")->setCellValue("Y1","出荷個数")->setCellValue("Z1","便種（スピ-ドを選択）")->setCellValue("AA1","便種（ク-ル便指定）")->setCellValue("AB1","配達日")->setCellValue("AC1","配達指定時間帯")->setCellValue("AD1","配達指定時間（時分）")->setCellValue("AE1","代引金額")->setCellValue("AF1","消費税")->setCellValue("AG1","保険金額")->setCellValue("AH1","指定シ-ル1")->setCellValue("AI1","指定シ-ル2")->setCellValue("AJ1","指定シ-ル3")->setCellValue("AK1","営業所止めフラグ")->setCellValue("AL1","営業店コ-ド")->setCellValue("AM1","元着区分");//表头值
        $objSheet->getColumnDimension('B')->setWidth(16);//单元格宽
		$objSheet->getColumnDimension('D')->setWidth(26);//单元格宽
		$objSheet->getColumnDimension('G')->setWidth(16);//单元格宽
		$objSheet->getColumnDimension('X')->setWidth(26);//单元格宽
		$objSheet->getColumnDimension('T')->setWidth(26);//单元格宽
		$objSheet->getColumnDimension('U')->setWidth(26);//单元格宽
		$objSheet->getColumnDimension('V')->setWidth(26);//单元格宽
		$objSheet->getColumnDimension('W')->setWidth(26);//单元格宽
		// 如果是最新
		if($down_type == 'new'){
			$sql = "SELECT who_tel,who_post,who_house,who_name,oms_id,send_day,send_time,due_money,back_status,group_concat(goods_code,'*',out_num separator '#') as aaa,express_company,send_method,need_not_send,other_1 from jp_list_out where  express_company='佐川急便' and send_method in('着払い','宅配便') and back_status is NULL and table_status='0' and out_day between '{$start}' and '{$end}' group by order_id,who_tel,back_status order by id;";	//根据电话号码进行多重验证
		}else{	
			$sql = "SELECT who_tel,who_post,who_house,who_name,oms_id,send_day,send_time,due_money,back_status,group_concat(goods_code,'*',out_num separator '#') as aaa,express_company,send_method,need_not_send,other_1 from jp_list_out where  express_company='佐川急便' and send_method in('着払い','宅配便') and back_status is NULL and out_day between '{$start}' and '{$end}' group by order_id,who_tel,back_status order by id;";
		}
		$res = $db->execute($sql);
		$j=2;
		foreach ((array)$res as $key => $value) {
			$aaa = $value['aaa'];
			$new_arr = explode("#",$aaa);
			$new_count = count($new_arr);
			$new_0="";
			$new_1="";
			$new_2="";
			$new_3="";
			$new_4="";
			if($new_count>5){
				$new_0=$new_arr[0];
				$new_1=$new_arr[1];
				$new_2=$new_arr[2];
				$new_3=$new_arr[3];	//四个已经写满
				$new_4="";
				for($i=4; $i<=$new_count; $i++){
					$new_4=$new_arr[$i]." ".$new_4;
				}
			}else{
				$new_0=$new_arr[0];
				$new_1=$new_arr[1];
				$new_2=$new_arr[2];
				$new_3=$new_arr[3];
				$new_4=$new_arr[4];
			}
			$back = $value['back_status'];
			if($back=="NULL"){
				$back="";
			}else{
				
			}
			//如果着払い，则元着区分=1
			if($value['express_company']=='佐川急便' and $value['send_method']=='着払い'){
				$yuan='1';
			}else{
				$yuan='';
			}
			$objSheet->setCellValue("B".$j,$value['who_tel'])->setCellValue("C".$j,$value['who_post'])->setCellValue("D".$j,$value['who_house'])->setCellValue("G".$j,$value['who_name'])->setCellValue("I".$j,$value['oms_id'])->setCellValue("S".$j,$value['other_1'])->setCellValue("AB".$j,$value['send_day'])->setCellValueExplicit("AC".$j,$value['send_time'],PHPExcel_Cell_DataType::TYPE_STRING)->setCellValue("AE".$j,$value['due_money'])->setCellValue("T".$j,$new_0)->setCellValue("U".$j,$new_1)->setCellValue("V".$j,$new_2)->setCellValue("W".$j,$new_3)->setCellValue("X".$j,$new_4)->setCellValue("AK".$j,$value['need_not_send'])->setCellValue("AM".$j,$yuan);
			$j++;
		}
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($dir."/../down/JP_zuochuan_addr.xlsx");	//保存在服务器
		if($down_method=='0'){
			// 标注已经下载过1
			$sql = "UPDATE jp_list_out set table_status='1' where express_company='佐川急便' and send_method in('着払い','宅配便') and back_status is NULL and out_day between '{$start}' and '{$end}';";
			$res = $db->execute($sql);
		}
			echo "ok";
			return false;
	}else if($select_company == 'zuochuan'){
		// 如果是佐川宅配便
		// 如果是最新
	}

	

}