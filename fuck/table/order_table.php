<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
ini_set("memory_limit", "1024M");

// 下载订单项目
if(isset($_POST['order_table'])){

    $store = $_POST['store'];
    $my_checked_items = $_POST['order_table'];
    $station = strtolower($_POST['station']);

    $response_list = $station.'_response_list';
    $response_info = $station.'_response_info';

    require_once($dir."/../PHPExcel/PHPExcel.php");//引入PHPExcel
    
    //制作时间
    date_default_timezone_set("Asia/Shanghai");
    $now_time = date("Y-m-d H'i's");

    //PHPExcel
    $objPHPExcel = new PHPExcel();
    $objSheet = $objPHPExcel->getActiveSheet();
    $objSheet->setTitle('订单@'.$now_time);//表名
    $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(12);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:AH1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:AH')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:AH1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objSheet->getStyle('A1:AH1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objSheet->getStyle('A1:AH1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    // $objSheet->getColumnDimension('A')->setWidth(34);//单元格宽
    $objSheet->freezePane('A2');//冻结表头
    // $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐

    // if($station == 'amazon'){
        $objSheet->setCellValue("A1","OMS-ID")
            ->setCellValue("B1","平台")
            ->setCellValue("C1","店铺")
            ->setCellValue("D1","注文番号")
            ->setCellValue("E1","注文时间")
            ->setCellValue("F1","备注")
            ->setCellValue("G1","购买者")
            ->setCellValue("H1","email")
            ->setCellValue("I1","总金额")
            ->setCellValue("J1","订单金额")
            ->setCellValue("K1","支付方式")
            ->setCellValue("L1","待支付")
            ->setCellValue("M1","电话")
            ->setCellValue("N1","邮编")
            ->setCellValue("O1","配送地址")
            ->setCellValue("P1","收件人")
            ->setCellValue("Q1","品名")
            ->setCellValue("R1","运费")
            ->setCellValue("S1","商品SKU")
            ->setCellValue("T1","商品代码")
            ->setCellValue("U1","数量")
            ->setCellValue("V1","押中国")
            ->setCellValue("W1","押日本")
            ->setCellValue("X1","发货仓库")
            ->setCellValue("Y1","单价")
            ->setCellValue("Z1","该项价格")
            ->setCellValue("AA1","代引金额")
            ->setCellValue("AB1","同步日期")
            ->setCellValue("AC1","配送方式")
            ->setCellValue("AD1","客人备注")
            ->setCellValue("AE1","指定日期")
            ->setCellValue("AF1","指定时间")
            ->setCellValue("AG1","订单状态")
            ->setCellValue("AH1","落扎者ID")
            ;    //表头值
        //SQL

         if($station == 'amazon'){
                    $sql = "SELECT 
                list.id,    #OMS-ID
                list.station,   #平台
                list.store, #店铺
                list.order_id,  #注文番号
                list.purchase_date,     #注文时间
                list.order_note,    #备注
                list.buyer_name,    #购买者
                list.buyer_email,   #email
                list.all_total_money,   #总金额
                list.order_total_money, #订单金额
                list.payment_method,    #支付方式
                list.pay_money, #待支付
                list.phone, #电话
                list.post_code, #邮编
                list.address,   #配送地址
                list.receive_name,  #收件人
                info.goods_title,   #品名
                list.shipping_price,  #运费
                info.sku,   #商品SKU
                info.goods_code,    #商品代码
                info.goods_num, #数量
                info.pause_ch,  #押中国
                info.pause_jp,  #押日本
                list.repo_status,   #发货仓库
                info.unit_price,    #单价
                info.item_price,    #该项价格
                info.cod_money, #代引金额
                list.syn_day,   #同步日期
                list.send_method, #配送方式
                0, #客人备注
                list.want_date, #指定日期
                list.want_time, #指定时间
                list.order_line, #指定时间
                list.who_id
         FROM $response_list list,$response_info info WHERE list.order_id = info.order_id AND list.order_id in ($my_checked_items) ORDER BY send_id";
         }else{
                    $sql = "SELECT 
                list.id,    #OMS-ID
                list.station,   #平台
                list.store, #店铺
                list.order_id,  #注文番号
                list.purchase_date,     #注文时间
                list.order_note,    #备注
                list.buyer_name,    #购买者
                list.buyer_email,   #email
                list.all_total_money,   #总金额
                list.order_total_money, #订单金额
                list.payment_method,    #支付方式
                list.pay_money, #待支付
                list.phone, #电话
                list.post_code, #邮编
                list.address,   #配送地址
                list.receive_name,  #收件人
                info.goods_title,   #品名
                list.shipping_price,  #运费
                info.sku,   #商品SKU
                info.goods_code,    #商品代码
                info.goods_num, #数量
                info.pause_ch,  #押中国
                info.pause_jp,  #押日本
                list.repo_status,   #发货仓库
                info.unit_price,    #单价
                info.item_price,    #该项价格
                info.cod_money, #代引金额
                list.syn_day,   #同步日期
                list.send_method, #配送方式
                list.buyer_others, #客人备注
                list.want_date, #指定日期
                list.want_time, #指定时间
                list.order_line, #指定时间
                list.who_id
         FROM $response_list list,$response_info info WHERE list.order_id = info.order_id AND list.order_id in ($my_checked_items) ORDER BY send_id";
         }
         $res = $db->getAll($sql);
        $j=2;
        foreach ($res as $key => $value) {
            if($value['order_line'] == 0){
                $value['order_line'] = '无详单';
            }
            if($value['order_line'] == 1){
                $value['order_line'] = '待处理';
            }
            if($value['order_line'] == 2){
                $value['order_line'] = '已合单';
            }
            if($value['order_line'] == 3){
                $value['order_line'] = '冻结';
            }
            if($value['order_line'] == 5){
                $value['order_line'] = '待发货';
            }
            if($value['order_line'] == 6){
                $value['order_line'] = 'close';
            }
            if($value['order_line'] == '-1'){
                $value['order_line'] = '回收站';
            }
            if($value['order_line'] == '-2'){
                $value['order_line'] = '待支付';   // 待支付
            }
            if($value['order_line'] == '-3'){   // 冻结退单
                $value['order_line'] = '已退押';   
            }
            if($value['order_line'] == '-4'){   // 已出快递单退单
                $value['order_line'] = '已退单';
            }
            if($value['order_line'] == '-5'){   // 待发货退回
                $value['order_line'] = '已退库';
            }
            if($value['order_line'] == 9){
                $value['order_line'] = '保留';
            }
            $objSheet->setCellValue("A".$j,$value['id'])
                    ->setCellValue("B".$j,$value['station'])
                    ->setCellValue("C".$j,$value['store'])
                    ->setCellValue("D".$j,$value['order_id'])
                    ->setCellValue("E".$j,$value['purchase_date'])
                    ->setCellValue("F".$j,$value['order_note'])
                    ->setCellValue("G".$j,$value['buyer_name'])
                    ->setCellValue("H".$j,$value['buyer_email'])
                    ->setCellValue("I".$j,$value['all_total_money'])
                    ->setCellValue("J".$j,$value['order_total_money'])
                    ->setCellValue("K".$j,$value['payment_method'])
                    ->setCellValue("L".$j,$value['pay_money'])
                    ->setCellValue("M".$j,$value['phone'])
                    ->setCellValue("N".$j,$value['post_code'])
                    ->setCellValue("O".$j,$value['address'])
                    ->setCellValue("P".$j,$value['receive_name'])
                    ->setCellValue("Q".$j,$value['goods_title'])
                    ->setCellValue("R".$j,$value['shipping_price'])
                    ->setCellValue("S".$j,$value['sku'])
                    ->setCellValue("T".$j,$value['goods_code'])
                    ->setCellValue("U".$j,$value['goods_num'])
                    ->setCellValue("V".$j,$value['pause_ch'])
                    ->setCellValue("W".$j,$value['pause_jp'])
                    ->setCellValue("X".$j,$value['repo_status'])
                    ->setCellValue("Y".$j,$value['unit_price'])
                    ->setCellValue("Z".$j,$value['item_price'])
                    ->setCellValue("AA".$j,$value['cod_money'])
                    ->setCellValue("AB".$j,$value['syn_day'])
                    ->setCellValue("AC".$j,$value['send_method'])
                    ->setCellValue("AD".$j,@$value['buyer_others'])
                    ->setCellValue("AE".$j,$value['want_date'])
                    ->setCellValue("AF".$j,$value['want_time'])
                    ->setCellValue("AG".$j,$value['order_line'])
                    ->setCellValue("AH".$j,$value['who_id'])
                    ;
            $j++;
        }
    // }
    
    

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/order_table.xlsx");   //保存在服务器
    echo "ok";
}