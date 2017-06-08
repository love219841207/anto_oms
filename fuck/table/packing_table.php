<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
ini_set("memory_limit", "1024M");

// 下载打包表
if(isset($_GET['down_packing'])){
    // 计算打包数
    $sql = "SELECT pack_id,count(pack_id) as count FROM send_table GROUP BY pack_id";
    $res = $db->getAll($sql);
    foreach ($res as $value) {
        $pack_id = $value['pack_id'];
        $pack_count = $value['count'];
        if($pack_count > 1){
            $sql = "UPDATE send_table SET pack_count = concat(pack_id,'-{$pack_count}') WHERE pack_id = '{$pack_id}'";
            $res = $db->execute($sql);
        }else{
            $sql = "UPDATE send_table SET pack_count = concat(pack_id) WHERE pack_id = '{$pack_id}'";
            $res = $db->execute($sql);
        }

    }

    // 预计多少行
    $sql = "SELECT count(1) as cc FROM send_table WHERE repo_status <> '日'";
    $res = $db->getOne($sql);
    $final_row = $res['cc']+1;

    require_once($dir."/../PHPExcel/PHPExcel.php");//引入PHPExcel
    
    //制作时间
    date_default_timezone_set("Asia/Shanghai");
    $now_time = date("Y-m-d H'i's");

    //PHPExcel
    $objPHPExcel = new PHPExcel();
    $objSheet = $objPHPExcel->getActiveSheet();
    $objSheet->setTitle('打包表@'.$now_time);//表名
    $objSheet->setCellValue("A1","导入日期")
            ->setCellValue("B1","收件人")
            ->setCellValue("C1","商品代码")
            ->setCellValue("D1","包裹")
            ->setCellValue("E1","总数")
            ->setCellValue("F1","中")
            ->setCellValue("G1","日");    //表头值
    $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(14);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:G')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objSheet->getStyle('A1:G1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objSheet->getStyle('A1:G1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    $objSheet->getColumnDimension('A')->setWidth(10);//单元格宽
    $objSheet->getColumnDimension('C')->setWidth(24);//单元格宽
    $objSheet->getColumnDimension('B')->setWidth(10);//单元格宽
    $objSheet->getColumnDimension('D')->setWidth(30);//单元格宽
    $objSheet->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐
    $objPHPExcel->getActiveSheet()->getStyle('A1:G'.$final_row)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

    //SQL
    $sql = "SELECT * FROM send_table WHERE repo_status <> '日' order by pack_id";
    // UPDATE send_table ss set ss.pack_type=( pp where ss.pack_id = pp.pack_id);
    $res = $db->getAll($sql);
    $j=2;
    $ppk = '';  //判断是否可以合并单元格
    foreach ($res as $key => $value) {
        if($ppk == $value['pack_count']){
            $jj = $j-1;
            $objPHPExcel->getActiveSheet()->getStyle('A'.$jj.':G'.$j)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
        }

        if($value['pause_ch']=='0'){
            $value['pause_ch'] = '';
        }

        if($value['pause_jp']=='0'){
            $value['pause_jp'] = '';
        }
        $objSheet->setCellValue("A".$j,$value['import_day'])
                ->setCellValueExplicit("B".$j,$value['who_name'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("C".$j,$value['goods_code'])
                ->setCellValueExplicit("D".$j,$value['pack_count'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("E".$j,$value['out_num'])
                ->setCellValue("F".$j,$value['pause_ch'])
                ->setCellValue("G".$j,$value['pause_jp']);
        $j++;
        $ppk = $value['pack_count'];
    }

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/packing_table.xlsx");   //保存在服务器
    echo "ok";
}