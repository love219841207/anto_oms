<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
ini_set("memory_limit", "1024M");

// 下载打包表
if(isset($_GET['down_packing'])){
    // 
    $sql = "UPDATE send_table SET pack_count = pack_id";
    $res = $db->execute($sql);
    // 替换pack_count里的（）为空
    $sql = "UPDATE send_table SET pack_count = REPLACE(pack_count,substring(pack_count, locate('(', pack_count),locate(')', pack_count)),'')";

    $res = $db->execute($sql);

    // 计算打包数
    // $sql = "SELECT pack_id,pack_count,count(pack_id) as count FROM send_table GROUP BY pack_count";
    // $res = $db->getAll($sql);
    // foreach ($res as $value) {
    //     $pack_id = $value['pack_id'];
    //     $count = $value['count'];
    //     $pack_count = $value['pack_count'];
    //     if($count > '1'){
    //         $sql = "UPDATE send_table SET pack_count = concat(pack_id,'-{$count}') WHERE pack_count = '{$pack_count}'";
    //         $res = $db->execute($sql);
    //     }
    // }

    // 预计多少行
    $sql = "SELECT count(1) as cc FROM send_table WHERE repo_status <> '日'";
    // $sql = "SELECT count(1) as cc FROM send_table";
    $res = $db->getOne($sql);
    $final_row = $res['cc']+1;

    require_once($dir."/../PHPExcel/PHPExcel.php");//引入PHPExcel
    
    //制作时间
    date_default_timezone_set("Asia/Shanghai");
    $now_time = date("Y-m-d H'i's");

    //PHPExcel
    $objPHPExcel = new PHPExcel();

    // 表格边框
    $Border_all = array(  
        'borders' => array(  
            'allborders' => array(  
                //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的  
                'style' => PHPExcel_Style_Border::BORDER_THIN,//细边框  
                'color' => array('argb' => 'dedede'),
            ),  
        ),  
    );

    $Border_out = array( 
        'borders' => array ( 
            'outline' => array ( 
                'style' => PHPExcel_Style_Border::BORDER_THICK, //设置border样式 
                'color' => array ('argb' => '666666'), //设置border颜色 
        ), 
    ),);


    $Border_fff = array( 
        'borders' => array ( 
            'outline' => array ( 
                'style' => PHPExcel_Style_Border::BORDER_THIN, //设置border样式 
                'color' => array ('argb' => 'FFFFFF'), //设置border颜色 
        ), 
    ),);

    // sheet1
    $objPHPExcel->setActiveSheetIndex(0); 
    $objPHPExcel->getActiveSheet()->setTitle('总发货表@'.$now_time);//表名
    $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(14);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:D')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    $objPHPExcel->getActiveSheet()->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);//单元格宽
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);//单元格宽
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);//单元格宽
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);//单元格宽
    $k = 1;
    $objPHPExcel->getActiveSheet()->setCellValue("A".$k,"商品代码")
            ->setCellValue("B".$k,"总发货数")
            ->setCellValue("C".$k,"总中国发货数")
            ->setCellValue("D".$k,"总日本发货数");    //表头值

    $k = $k+1;
    $sql = "SELECT goods_code,
            sum(out_num) AS sum_out_num,
            sum(pause_ch) AS sum_pause_ch,
            sum(pause_jp) AS sum_pause_jp FROM send_table
             WHERE goods_code <> substring(goods_code, locate('bag (', goods_code),locate(')', goods_code))
             AND repo_status <> '日' AND has_pack = '0'
             GROUP BY goods_code";

    $res = $db->getAll($sql);
    foreach ($res as $key => $value) {
        $objPHPExcel->getActiveSheet()->getStyle('A'.$k.':D'.$k)->applyFromArray($Border_all);
        $objPHPExcel->getActiveSheet()->setCellValue("A".$k,$value['goods_code'])
                ->setCellValueExplicit("B".$k,$value['sum_out_num'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("C".$k,$value['sum_pause_ch'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("D".$k,$value['sum_pause_jp'],PHPExcel_Cell_DataType::TYPE_STRING);
        $k++;
    }

    // sheet2
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(1); 
    $objPHPExcel->getActiveSheet()->setTitle('宅配');//表名
    $objPHPExcel->getActiveSheet()->setCellValue("A1","导入日期")
            ->setCellValue("B1","包裹")
            ->setCellValue("C1","收件人")
            ->setCellValue("D1","商品代码")
            ->setCellValue("E1","中")
            ->setCellValue("F1","日")
            ->setCellValue("G1","总数")
            ->setCellValue("H1","快递");    //表头值
    $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(14);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:H')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);//单元格宽
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);//单元格宽
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);//单元格宽
    // $objSheet->getColumnDimension('D')->setWidth(30);//单元格宽
    $objPHPExcel->getActiveSheet()->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐

    //SQL
    $sql = "SELECT * FROM send_table WHERE repo_status <> '日' AND send_method IN ('宅配便','宅急便') AND has_pack = '0' order by pack_count";
    // $sql = "SELECT * FROM send_table order by pack_id";
    $res = $db->getAll($sql);
    $j=2;
    $ppk = '';  //判断是否可以合并单元格
    foreach ($res as $key => $value) {
            $jj = $j-1;

            $objPHPExcel->getActiveSheet()->getStyle('A'.$j.':H'.$j)->applyFromArray($Border_all);

        if($ppk == $value['pack_count']){
            $objPHPExcel->getActiveSheet()->getStyle('A'.$jj.':H'.$jj)->getBorders()->getBottom()->getColor()->setARGB('FFFFFF');
        }else{
            $objPHPExcel->getActiveSheet()->getStyle('A'.$jj.':H'.$jj)->getBorders()->getBottom()->getColor()->setARGB('1d9c73');
            $objPHPExcel->getActiveSheet()->getStyle('A'.$jj.':H'.$jj)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

        }

        if($value['pause_ch']=='0'){
            $value['pause_ch'] = '';
        }

        if($value['pause_jp']=='0'){
            $value['pause_jp'] = '';
        }
        $objPHPExcel->getActiveSheet()->setCellValue("A".$j,$value['import_day'])
                ->setCellValueExplicit("B".$j,$value['pack_count'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("C".$j,$value['who_name'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("D".$j,$value['goods_code'])
                ->setCellValue("E".$j,$value['pause_ch'])
                ->setCellValue("F".$j,$value['pause_jp'])
                ->setCellValue("G".$j,$value['out_num'])
                ->setCellValue("H".$j,$value['send_method']);
        $j++;
        $ppk = $value['pack_count'];
    }

    // sheet3
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(2); 
    $objPHPExcel->getActiveSheet()->setTitle('mail');//表名
    $objPHPExcel->getActiveSheet()->setCellValue("A1","导入日期")
            ->setCellValue("B1","包裹")
            ->setCellValue("C1","收件人")
            ->setCellValue("D1","商品代码")
            ->setCellValue("E1","中")
            ->setCellValue("F1","日")
            ->setCellValue("G1","总数")
            ->setCellValue("H1","快递");    //表头值
    $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(14);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:H')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);//单元格宽
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);//单元格宽
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);//单元格宽
    // $objSheet->getColumnDimension('D')->setWidth(30);//单元格宽
    $objPHPExcel->getActiveSheet()->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐
    // $objPHPExcel->getActiveSheet()->getStyle('A1:H'.$final_row)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
    // $objPHPExcel->getActiveSheet()->getStyle('A1:H'.$final_row)->getBorders()->getAllBorders()->getColor()->setARGB('dedede');

    //SQL
    $sql = "SELECT * FROM send_table WHERE repo_status <> '日' AND send_method NOT IN ('宅配便','宅急便') AND has_pack = '0' order by send_method,send_id";
    // $sql = "SELECT * FROM send_table order by pack_id";
    $res = $db->getAll($sql);
    $j=2;
    $LL = 3;
    $L = 2;
    $ppk = '';  //判断是否可以合并单元格
    foreach ($res as $key => $value) {
            $jj = $j-1;

            $objPHPExcel->getActiveSheet()->getStyle('A'.$j.':H'.$j)->applyFromArray($Border_all);

        if($ppk == $value['pack_count']){
            $objPHPExcel->getActiveSheet()->getStyle('A'.$jj.':H'.$jj)->getBorders()->getBottom()->getColor()->setARGB('FFFFFF');
        }else{
            $objPHPExcel->getActiveSheet()->getStyle('A'.$jj.':H'.$jj)->getBorders()->getBottom()->getColor()->setARGB('1d9c73');
            $objPHPExcel->getActiveSheet()->getStyle('A'.$jj.':H'.$jj)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);

        }

        if($value['pause_ch']=='0'){
            $value['pause_ch'] = '';
        }

        if($value['pause_jp']=='0'){
            $value['pause_jp'] = '';
        }
        $objPHPExcel->getActiveSheet()->setCellValue("A".$j,$value['import_day'])
                ->setCellValueExplicit("B".$j,$value['pack_count'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValueExplicit("C".$j,$value['who_name'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("D".$j,$value['goods_code'])
                ->setCellValue("E".$j,$value['pause_ch'])
                ->setCellValue("F".$j,$value['pause_jp'])
                ->setCellValue("G".$j,$value['out_num'])
                ->setCellValue("H".$j,$value['send_method']);
        $j++;
        $ppk = $value['pack_count'];
    }
    $objPHPExcel->setActiveSheetIndex(0);   //默认回第一个 sheet 

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/packing_table.xlsx");   //保存在服务器
    
    echo "ok";
}