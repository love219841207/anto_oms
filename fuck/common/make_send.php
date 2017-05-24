<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);


if(isset($_GET['send_table'])){
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
    $objPHPExcel->getActiveSheet()->getStyle('A:CQ')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:CQ1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objSheet->getStyle('A1:CQ1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objSheet->getStyle('A1:CQ1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    $objSheet->getColumnDimension('A')->setWidth(34);//单元格宽
    $objSheet->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐

	//下载表格
	if($select_company == 'zuochuan'){
		// 如果是佐川宅配便
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
			$sql = "SELECT who_tel,who_post,who_house,who_name,pack_id,send_day,send_time,due_money,back_status,group_concat(goods_code,'*',out_num separator '#') as aaa,express_company,send_method,need_not_send,other_1 from send_table where item_line = 1 and repo_status = '{$select_repo}' and express_company='佐川急便' and send_method in('着払い','宅配便') and back_status = '0' and table_status='0' and import_day between '{$start}' and '{$end}' group by send_id order by id;";	//根据电话号码进行多重验证
		}else{	
			$sql = "SELECT who_tel,who_post,who_house,who_name,pack_id,send_day,send_time,due_money,back_status,group_concat(goods_code,'*',out_num separator '#') as aaa,express_company,send_method,need_not_send,other_1 from send_table where item_line = 1 and repo_status = '{$select_repo}' and express_company='佐川急便' and send_method in('着払い','宅配便') and back_status = '0' and import_day between '{$start}' and '{$end}' group by send_id order by id;";
		}
		$res = $db->getAll($sql);
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
				@$new_1=$new_arr[1];
				@$new_2=$new_arr[2];
				@$new_3=$new_arr[3];
				@$new_4=$new_arr[4];
			}
			$back = $value['back_status'];
			if($back=="0"){
				$back="";
			}else{
				
			}
			//代引如果为0则为空
			$due_money = $value['due_money'];
			if($due_money == "0"){
				$due_money = "";
			}else{
				
			}
			//如果着払い，则元着区分=1
			if($value['express_company']=='佐川急便' and $value['send_method']=='着払い'){
				$yuan='1';
			}else{
				$yuan='';
			}
			$objSheet->setCellValue("B".$j,$value['who_tel'])->setCellValue("C".$j,$value['who_post'])->setCellValue("D".$j,$value['who_house'])->setCellValue("G".$j,$value['who_name'])->setCellValue("I".$j,$value['pack_id'])->setCellValue("S".$j,$value['other_1'])->setCellValue("AB".$j,$value['send_day'])->setCellValueExplicit("AC".$j,$value['send_time'],PHPExcel_Cell_DataType::TYPE_STRING)->setCellValue("AE".$j,$due_money)->setCellValue("T".$j,$new_0)->setCellValue("U".$j,$new_1)->setCellValue("V".$j,$new_2)->setCellValue("W".$j,$new_3)->setCellValue("X".$j,$new_4)->setCellValue("AK".$j,$value['need_not_send'])->setCellValue("AM".$j,$yuan);
			$j++;
		}
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($dir."/../../down/".$select_repo."_佐川.xlsx");	//保存在服务器
		if($down_type=='new'){
			// 标注已经下载过1
			$sql = "UPDATE send_table set table_status = '1' where express_company = '佐川急便' and send_method in('着払い','宅配便') and back_status = '0' and import_day between '{$start}' and '{$end}';";
			$res = $db->execute($sql);
		}
		echo $select_repo.'_佐川.xlsx';
	}else if($select_company == 'heimao'){
		// 如果是黑猫
		$objSheet->
	setCellValue("A1","お客様管理番号")->
	setCellValue("B1","送り状種類")->
	setCellValue("C1","クール区分")->
	setCellValue("D1","伝票番号")->
	setCellValue("E1","出荷予定日")->
	setCellValue("F1","お届け予定日")->
	setCellValue("G1","配達時間帯")->
	setCellValue("H1","お届け先コード")->
	setCellValue("I1","お届け先電話番号")->
	setCellValue("J1","お届け先電話番号枝番")->
	setCellValue("K1","お届け先郵便番号")->
	setCellValue("L1","お届け先住所")->
	setCellValue("M1","お届け先アパートマンション名")->
	setCellValue("N1","お届け先会社・部門１")->
	setCellValue("O1","お届け先会社・部門２")->
	setCellValue("P1","お届け先名")->
	setCellValue("Q1","お届け先名(ｶﾅ)")->
	setCellValue("R1","敬称")->
	setCellValue("S1","ご依頼主コード")->
	setCellValue("T1","ご依頼主電話番号")->
	setCellValue("U1","ご依頼主電話番号枝番")->
	setCellValue("V1","ご依頼主郵便番号")->
	setCellValue("W1","ご依頼主住所")->
	setCellValue("X1","ご依頼主アパートマンション")->
	setCellValue("Y1","ご依頼主名")->
	setCellValue("Z1","ご依頼主名(ｶﾅ)")->
	setCellValue("AA1","品名コード１")->
	setCellValue("AB1","品名１")->
	setCellValue("AC1","品名コード２")->
	setCellValue("AD1","品名２")->
	setCellValue("AE1","荷扱い１")->
	setCellValue("AF1","荷扱い２")->
	setCellValue("AG1","記事")->
	setCellValue("AH1","ｺﾚｸﾄ代金引換額（税込)")->
	setCellValue("AI1","内消費税額等")->
	setCellValue("AJ1","止置き")->
	setCellValue("AK1","営業所コード")->
	setCellValue("AL1","発行枚数")->
	setCellValue("AM1","個数口表示フラグ")->
	setCellValue("AN1","請求先顧客コード")->
	setCellValue("AO1","請求先分類コード")->
	setCellValue("AP1","運賃管理番号")->
	setCellValue("AQ1","注文時カード払いデータ登録")->
	setCellValue("AR1","注文時カード払い加盟店番号")->
	setCellValue("AS1","注文時カード払い申込受付番号１")->
	setCellValue("AT1","注文時カード払い申込受付番号２")->
	setCellValue("AU1","注文時カード払い申込受付番号３")->
	setCellValue("AV1","お届け予定ｅメール利用区分")->
	setCellValue("AW1","お届け予定ｅメールe-mailアドレス")->
	setCellValue("AX1","入力機種")->
	setCellValue("AY1","お届け予定ｅメールメッセージ")->
	setCellValue("AZ1","お届け完了ｅメール利用区分")->
	setCellValue("BA1","お届け完了ｅメールe-mailアドレス")->
	setCellValue("BB1","お届け完了ｅメールメッセージ")->
	setCellValue("BC1","収納代行利用区分")->
	setCellValue("BD1","予備")->
	setCellValue("BE1","収納代行請求金額(税込)")->
	setCellValue("BF1","収納代行内消費税額等")->
	setCellValue("BG1","収納代行請求先郵便番号")->
	setCellValue("BH1","収納代行請求先住所")->
	setCellValue("BI1","収納代行請求先アパートマンション")->
	setCellValue("BJ1","収納代行請求先会社・部門名１")->
	setCellValue("BK1","収納代行請求先会社・部門名２")->
	setCellValue("BL1","収納代行請求先名(漢字)")->
	setCellValue("BM1","収納代行請求先名(カナ)")->
	setCellValue("BN1","収納代行問合せ先名(漢字)")->
	setCellValue("BO1","収納代行問合せ先郵便番号")->
	setCellValue("BP1","収納代行問合せ先住所")->
	setCellValue("BQ1","収納代行問合せ先アパートマンション")->
	setCellValue("BR1","収納代行問合せ先電話番号")->
	setCellValue("BS1","収納代行管理番号")->
	setCellValue("BT1","収納代行品名")->
	setCellValue("BU1","収納代行備考")->
	setCellValue("BV1","予備０１")->
	setCellValue("BW1","予備０２")->
	setCellValue("BX1","予備０３")->
	setCellValue("BY1","予備０４")->
	setCellValue("BZ1","予備０５")->
	setCellValue("CA1","予備０６")->
	setCellValue("CB1","予備０７")->
	setCellValue("CC1","予備０８")->
	setCellValue("CD1","予備０９")->
	setCellValue("CE1","予備１０")->
	setCellValue("CF1","予備１１")->
	setCellValue("CG1","予備１２")->
	setCellValue("CH1","予備１３")->
	setCellValue("CI1","投函予定メール利用区分")->
	setCellValue("CJ1","投函予定メールe-mailアドレス")->
	setCellValue("CK1","投函予定メールメッセージ")->
	setCellValue("CL1","投函完了メール(お届け先宛)利用区分")->
	setCellValue("CM1","投函完了メール(お届け先宛)e-mailアドレス")->
	setCellValue("CN1","投函完了メール(お届け先宛)メッセージ")->
	setCellValue("CO1","投函完了メール(ご依頼主宛)利用区分")->
	setCellValue("CP1","投函完了メール(ご依頼主宛)e-mailアドレス")->
	setCellValue("CQ1","投函完了メール(ご依頼主宛)メッセージ");//表头值
	$objSheet->getStyle("A1:J1")->getFont()->setName("微软雅黑")->setSize(10)->setBold(True);//表头字体
	$objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(10);//默认字体
	$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
	$objPHPExcel->getActiveSheet()->getStyle('A:G')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
	$objSheet->getStyle('A1:G1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	$objPHPExcel->getActiveSheet()->getStyle('A1:AM1')->getFont()->setBold(false);
	// $objSheet->getColumnDimension('E')->setWidth(20);//单元格宽
	// $objSheet->getColumnDimension('F')->setWidth(20);//单元格宽
	// $objSheet->getColumnDimension('G')->setWidth(28);//单元格宽
	$objSheet->getDefaultRowDimension()->setRowHeight(16);   //单元格高
	$objSheet->freezePane('A2');//冻结表头
	$objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐
	//sql
	if($down_type=='new'){
		$sql = "SELECT pack_id as A,send_method as B,due_money,import_day as E,send_day as F,send_time as G,who_tel as I,who_post as K,who_house,who_name as P,group_concat(goods_code,'*',out_num separator ' ') as ABD,due_money as AH,need_not_send as AJ,who_email as AW,order_method,other_1,back_status from send_table where express_company='ヤマト運輸' and repo_status = '{$select_repo}' and back_status ='0' and table_status='0' and import_day between '{$start}' and '{$end}' group by send_id order by id;";
	}else{	
		$sql = "SELECT pack_id as A,send_method as B,due_money,import_day as E,send_day as F,send_time as G,who_tel as I,who_post as K,who_house,who_name as P,group_concat(goods_code,'*',out_num separator ' ') as ABD,due_money as AH,need_not_send as AJ,who_email as AW,order_method,other_1,back_status from send_table where express_company='ヤマト運輸' and repo_status = '{$select_repo}' and back_status ='0' and import_day between '{$start}' and '{$end}' group by send_id order by id;";
	}

		$res = $db->getAll($sql);
		$j=2;
		foreach ((array)$res as $key => $value) {
			$back = $value['back_status'];
			if($back=="NULL"){
				$back="";
			}else{
				
			}
			//判断付款金额
			if(empty($value['due_money'])){
				//判断包裹种类
				if($value['B']=='宅急便'){
					$value['B']='0';
				}
				if($value['B']=='コレクト'){
					$value['B']='2';
				}
				if($value['B']=='DM便'){
					$value['B']='3';
				}
				if($value['B']=='タイムサービス'){
					$value['B']='4';
				}
				if($value['B']=='着払い'){
					$value['B']='5';
				}
				if($value['B']=='メール便速達'){
					$value['B']='6';
				}
				if($value['B']=='ネコポス'){
					$value['B']='7';
				}
				if($value['B']=='宅急便コンパクト'){
					$value['B']='8';
				}
			}else{
				$value['B']='2';
			}
			//出荷予定日格式替换
			$value['E']=str_replace("-","/",$value['E']);
			//地址分割
			$house = $value['who_house'];
			$house = str_replace(" ","",$house);	//去掉空格
			$len_house = strlen($house);
			$MM="";
			$NN="";
			$OO="";
			if($len_house<33){
				$LL=mb_substr($house,0,$len_house);
			}else{
				$LL=mb_substr($house,0,16);
				$MM=mb_substr($house,16,16);
				$NN=mb_substr($house,32,25);
				$OO=mb_substr($house,57,25);
			}
			//商品分割
			$ABD = $value['ABD'];
			$len_ABD = strlen($ABD);
			$AD="";
			if($len_ABD<51){
				$AB=substr($ABD,0,$len_ABD);
			}else if($len_ABD>50){
				$AB=substr($ABD,0,50);
				$AD=substr($ABD,50,50);
			}
			//注文経由，收信终端1和4是1，2和3是2
			if($value['order_method']=='1' or $value['order_method']=='4'){
				$value['order_method']='1';
			}else if($value['order_method']=='2' or $value['order_method']=='3'){
				$value['order_method']='2';
			}
			//如果包裹种类是7，那么CI行(hang)为注文経由
			$CI="";
			if($value['B']=='7'){
				$CI=$value['order_method'];
			};
			// 代引如果是0则为空
			if($value['AH'] == '0'){
				$value['AH'] = '';
			};
			//写入表格
			$objSheet->
			setCellValueExplicit("A".$j,$value['A'],PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValue("B".$j,$value['B'])->
			setCellValue("E".$j,$value['E'])->
			setCellValue("F".$j,$value['F'])->
			setCellValue("G".$j,$value['G'])->
			setCellValue("I".$j,$value['I'])->setCellValue("K".$j,$value['K'])->
			setCellValueExplicit("L".$j,$LL,PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValueExplicit("M".$j,$MM,PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValueExplicit("N".$j,$NN,PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValueExplicit("O".$j,$OO,PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValue("P".$j,$value['P'])->
			setCellValue("R".$j,"様")->
			setCellValue("T".$j,"047-498-2370")->
			setCellValue("V".$j,"270-1437")->
			setCellValue("W".$j,"千葉県白井市木833-15")->
			setCellValue("Y".$j,"有川株式会社")->
			setCellValueExplicit("AB".$j,$AB,PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValueExplicit("AD".$j,$AD,PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValueExplicit("AF".$j,$value['ABD'],PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValueExplicit("AG".$j,$value['other_1'],PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValue("AH".$j,$value['AH'])->
			setCellValue("AJ".$j,$value['AJ'])->
			setCellValueExplicit("AN".$j,"047498237001",PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValueExplicit("AP".$j,"01",PHPExcel_Cell_DataType::TYPE_STRING)->
			setCellValue("AV".$j,"1")->
			setCellValue("AW".$j,$value['AW'])->
			setCellValue("AX".$j,$value['order_method'])->
			setCellValue("AY".$j,"当店で商品をお買い上げいただきありがとうございました。ご注文いただいた商品の出荷処理を開始します。商品の到着をお待ちになってください。")->
			setCellValue("AZ".$j,"1")->
			setCellValue("BA".$j,$value['AW'])->
			setCellValue("BB".$j,"親愛なるお客様へ：ご注文いただいた商品を配送完了になりました。何か問題ございましたら、アフターサービス窓口：info@after-service.info　へお問い合わせて頂けますようお願いいたします。今後とも、引き続きご愛顧くださいますようお願い申し上げます。")->
			setCellValue("CI".$j,$CI)->
			setCellValue("CJ".$j,$value['AW'])->
			setCellValue("CK".$j,"当店で商品をお買い上げいただきありがとうございました。ご注文いただいた商品の投函処理を開始します。商品の到着をお待ちになってください。")->
			setCellValue("CL".$j,$CI)->
			setCellValue("CM".$j,$value['AW'])->
			setCellValue("CN".$j,"親愛なるお客様へ：ご注文いただいた商品を投函完了になりました。何か問題ございましたら、アフターサービス窓口：info@after-service.info　へお問い合わせて頂けますようお願いいたします。今後とも、引き続きご愛顧くださいますようお願い申し上げます。");
			$j++;
		}
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($dir."/../../down/".$select_repo."_黑猫.xlsx");	//保存在服务器
		if($down_type=='new'){
			// 标注已经下载过1
			$sql = "UPDATE send_table set table_status='1' where express_company='ヤマト運輸' and back_status ='0' and out_day between '{$start}' and '{$end}';";
			$res = $db->execute($sql);
		}
		echo $select_repo.'_黑猫.xlsx';
	}

}