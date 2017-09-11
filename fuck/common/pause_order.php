<?php
require_once("../header.php");
require_once("../log.php");
$dir = dirname(__FILE__);

set_time_limit(0);
ini_set("memory_limit", "1024M");

// 读取所有冻结订单list,info表
if(isset($_GET['pause_order'])){
	//	获取所有平台 ******************** select * (select * from t1 union all select * from t2) tmp order by tmp.createDate时间戳
	$sql = "SELECT info.id,info.station,list.pause_time,info.store,info.order_id,info.goods_code,info.goods_num,info.pause_ch,info.pause_jp,info.unit_price,info.item_price,info.cod_money,info.import_time FROM amazon_response_list list,amazon_response_info info WHERE list.order_id = info.order_id AND info.is_pause = 'pause' ORDER BY list.pause_time";
	$res1 = $db->getAll($sql);
    //  获取所有平台 ******************** select * (select * from t1 union all select * from t2) tmp order by tmp.createDate时间戳
    $sql = "SELECT info.id,info.station,list.pause_time,info.store,info.order_id,info.goods_code,info.goods_num,info.pause_ch,info.pause_jp,info.unit_price,info.item_price,info.cod_money,info.import_time FROM rakuten_response_list list,rakuten_response_info info WHERE list.order_id = info.order_id AND info.is_pause = 'pause' ORDER BY list.pause_time";
    $res2 = $db->getAll($sql);
    $res = array_merge($res1, $res2); 

	echo json_encode($res);
}

// 读取所有冻结退押订单info表
if(isset($_GET['back_order'])){
    //  获取所有平台 ******************** select * (select * from t1 union all select * from t2) tmp order by tmp.createDate时间戳
    $sql = "SELECT info.id,info.station,list.pause_time,info.store,info.order_id,info.goods_code,info.goods_num,info.pause_ch,info.pause_jp,info.unit_price,info.item_price,info.cod_money,info.import_time FROM amazon_response_list list,amazon_response_info info WHERE list.order_id = info.order_id AND info.is_pause = 'back' ORDER BY list.pause_time";
    $res1 = $db->getAll($sql);
    $sql = "SELECT info.id,info.station,list.pause_time,info.store,info.order_id,info.goods_code,info.goods_num,info.pause_ch,info.pause_jp,info.unit_price,info.item_price,info.cod_money,info.import_time FROM rakuten_response_list list,rakuten_response_info info WHERE list.order_id = info.order_id AND info.is_pause = 'back' ORDER BY list.pause_time";
    $res2 = $db->getAll($sql);
    $res = array_merge($res1, $res2); 
    echo json_encode($res);
}

// 查看上次冻结
if(isset($_GET['show_pause_order'])){
    $sql = "SELECT * FROM repo_pause";
    $res = $db->getAll($sql);

    echo json_encode($res);
}

// 读取一个info
if(isset($_GET['one_pause'])){
    $store = $_GET['store'];
    $station = strtolower($_GET['station']);
    $info_id = $_GET['one_pause'];

    $response_info = $station.'_response_info';

    $sql = "SELECT * FROM amazon_response_info WHERE id = '{$info_id}'";
    $res = $db->getAll($sql);

    echo json_encode($res);
}

// 退押
if(isset($_GET['back_pause'])){
    $store = $_GET['store'];
    $station = strtolower($_GET['station']);
    $info_id = $_GET['back_pause'];

    $response_info = $station.'_response_info';
    $response_list = $station.'_response_list';

    // 查询该 info_id 订单号、所押的中国和日本数及goods_code
    $sql = "SELECT order_id,pause_ch,pause_jp,goods_code FROM $response_info WHERE id = '{$info_id}'";
    $res = $db->getOne($sql);
    $order_id = $res['order_id'];
    $pause_ch = $res['pause_ch'];
    $pause_jp = $res['pause_jp'];
    $goods_code = $res['goods_code'];

    // 更改 order_line 为退押状态
    $sql = "UPDATE $response_list SET order_line = '-3' WHERE order_id = '{$order_id}'";
    $res = $db->execute($sql);

    // 还库存
    $sql = "UPDATE goods_type SET a_repo = a_repo + $pause_ch,b_repo = b_repo + $pause_jp WHERE goods_code = '{$goods_code}'";
    $res = $rdb->execute($sql);

    // 对押的数目清零及is_pause 状态修改
    $sql = "UPDATE $response_info SET pause_jp = 0,pause_ch = 0,is_pause = 'back' WHERE id = '{$info_id}'";
    $res = $db->execute($sql);

    // 查询OMS-ID
    $sql = "SELECT id FROM $response_list WHERE order_id = '{$order_id}'";
    $res = $db->getOne($sql);

    // 日志
    $oms_id = $res['id'];
    $do = '[退押]：'.$order_id;
    oms_log($u_name,$do,'change_order',$station,$store,$oms_id); 

    echo 'ok';
}

// 还原
if(isset($_GET['to_pause'])){
    $store = $_GET['store'];
    $info_id = $_GET['to_pause'];
    $station = strtolower($_GET['station']);

    $response_info = $station.'_response_info';
    $response_list = $station.'_response_list';

    // 查询该 info_id 订单号
    $sql = "SELECT order_id FROM $response_info WHERE id = '{$info_id}'";
    $res = $db->getOne($sql);
    $order_id = $res['order_id'];

    // 对押的商品 is_pause 状态修改
    $sql = "UPDATE $response_info SET is_pause = 'pause' WHERE id = '{$info_id}'";
    $res = $db->execute($sql);

    // 查询是否有其他退押
    $sql = "SELECT is_pause FROM $response_info WHERE order_id = '{$order_id}'";
    $res = $db->getAll($sql);
    $can_back = 1;
    foreach ($res as $value) {
        if($value['is_pause'] == 'back'){
            $can_back = 0;
        }
    }
    if($can_back == 1){
        // 更改 order_line 为退押状态
        $sql = "UPDATE $response_list SET order_line = '3' WHERE order_id = '{$order_id}'";
        $res = $db->execute($sql);
    }

    echo "ok";
}

// 删除
if(isset($_GET['del_pause'])){
    $store = $_GET['store'];
    $info_id = $_GET['del_pause'];
    $order_id = $_GET['order_id'];
    $station = strtolower($_GET['station']);

    $response_info = $station.'_response_info';
    $response_list = $station.'_response_list';

    // 查询删除的item
    $sql = "SELECT * FROM $response_info WHERE id = '{$info_id}'";
    $res = $db->getOne($sql);
    $order_id = $res['order_id'];
    $goods_title = $res['goods_title'];
    $goods_code = $res['goods_code'];
    $goods_num = $res['goods_num'];
    $unit_price = $res['unit_price'];
    $item_price = $res['item_price'];
    $cod_money = $res['cod_money'];

    // 删除
    $sql1 = "DELETE FROM $response_info WHERE id = '{$info_id}'";
    $res1 = $db->execute($sql1);

    // 查询是否有其他退押
    $sql = "SELECT is_pause FROM $response_info WHERE order_id = '{$order_id}'";
    $res = $db->getAll($sql);
    $can_back = 1;
    foreach ($res as $value) {
        if($value['is_pause'] == 'back'){
            $can_back = 0;
        }
    }
    if($can_back == 1){
        // 更改 order_line 为退押状态
        $sql = "UPDATE $response_list SET order_line = '3' WHERE order_id = '{$order_id}'";
        $res = $db->execute($sql);
    }

    //查询OMS-ID
    $sql = "SELECT id FROM $response_list WHERE order_id = '{$order_id}'";
    $res2 = $db->getOne($sql);
    $oms_id = $res2['id'];

    $do = '[删除一单]：订单号【'.$order_id.'】商品代码【'.$goods_code.'】数量【'.$goods_num.'】子订单价格【'.$item_price.'】单价【'.$unit_price.'】代引金额【'.$cod_money.'】';

    oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
    echo 'ok';
}

// 新增一单
if(isset($_GET['new_pause_order'])){
    $order_id = $_GET['new_pause_order'];
    $store = $_GET['store'];
    $station = strtolower($_GET['station']);
    $new_goods_code = $_GET['new_goods_code'];
    $new_goods_num = $_GET['new_goods_num'];
    $new_unit_price = $_GET['new_unit_price'];
    $new_yfcode = $_GET['new_yfcode'];
    $new_cod_money = $_GET['new_cod_money'];

    $response_info = $station.'_response_info';
    $response_list = $station.'_response_list';

    //运费金额计算 ###############
    $new_yf_money = '0';    //暂时为0

    // 当前时间戳
    $now_time = time();

    // 添加
    $sql = "INSERT INTO $response_info (
        store,
        order_id,
        holder,
        goods_title,
        sku_ok,
        yfcode_ok,
        yfcode,
        yf_money,
        sku,
        goods_code,
        goods_num,
        is_pause,
        unit_price,
        cod_money,
        import_time) VALUES(
        '{$store}',
        '{$order_id}',
        '{$u_name}',
        '',
        '1',
        '1',
        '{$new_yfcode}',
        '{$new_yf_money}',
        '{$new_goods_code}',
        '{$new_goods_code}',
        '{$new_goods_num}',
        'pause',
        '{$new_unit_price}',
        '{$new_cod_money}',
        {$now_time}
        ) ";
    $res = $db->execute($sql);

    // 如果COD_money大于0，则为代引
    if($new_cod_money > 0){
        //日志
        $do = ' [新增一单]：订单号【'.$order_id.'】商品代码【'.$new_goods_code.'】数量【'.$new_goods_num.'】单价【'.$new_unit_price.'】运费代码【'.$new_yfcode.'】运费金额【'.$new_yf_money.'】代引金额【'.$new_cod_money.'】';

    }else{
        //日志
        $do = ' [新增一单]：订单号【'.$order_id.'】商品代码【'.$new_goods_code.'】数量【'.$new_goods_num.'】单价【'.$new_unit_price.'】运费代码【'.$new_yfcode.'】运费金额【'.$new_yf_money.'】';
    }
    //查询OMS-ID
    $sql = "SELECT id FROM $response_list WHERE order_id = '{$order_id}'";
    $res = $db->getOne($sql);
    $oms_id = $res['id'];

    oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
    echo 'ok';

}

// 下载冻结订单表
if(isset($_GET['down_pause_orders_table'])){
    require_once($dir."/../PHPExcel/PHPExcel.php");//引入PHPExcel
    
    //制作时间
    date_default_timezone_set("Asia/Shanghai");
    $now_time = date("Y-m-d H'i's");

    //PHPExcel
    $objPHPExcel = new PHPExcel();
    $objSheet = $objPHPExcel->getActiveSheet();
    $objSheet->setTitle('冻结订单表@'.$now_time);//表名
    $objSheet->setCellValue("A1","冻结日期")
            ->setCellValue("B1","注文番号")
    		->setCellValue("C1","OMS-ID")
    		->setCellValue("D1","商品代码")
    		->setCellValue("E1","数量")
    		->setCellValue("I1","收件人")
            ->setCellValue("M1","店铺");    //表头值
    $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(12);  //默认字体
    $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A:M')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);//垂直居中
    $objPHPExcel->getActiveSheet()->getStyle('A1:M1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);//前景色
    $objSheet->getStyle('A1:M1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $objSheet->getStyle('A1:M1')->getFill()->getStartColor()->setRGB('1d9c73'); //背景色
    // $objSheet->getDefaultRowDimension()->setRowHeight(28);   //单元格高
    $objSheet->getColumnDimension('A')->setWidth(12);;//单元格宽
    $objSheet->getColumnDimension('B')->setWidth(20);//单元格宽
    $objSheet->getColumnDimension('D')->setWidth(34);//单元格宽
    $objSheet->freezePane('A2');//冻结表头
    $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);//左对齐

    //SQL
    $sql = "SELECT FROM_UNIXTIME(list.pause_time, '%Y-%m-%d %H:%I:%S') as pause_time,list.order_id,list.id,info.goods_code,(info.goods_num-info.pause_ch-info.pause_jp) as pause_num,list.receive_name,list.store FROM amazon_response_list list,amazon_response_info info WHERE list.order_id = info.order_id AND info.is_pause = 'pause' ORDER BY pause_time";
	$res1 = $db->getAll($sql);
    $sql = "SELECT FROM_UNIXTIME(list.pause_time, '%Y-%m-%d %H:%I:%S') as pause_time,list.order_id,list.id,info.goods_code,(info.goods_num-info.pause_ch-info.pause_jp) as pause_num,list.receive_name,list.store FROM rakuten_response_list list,rakuten_response_info info WHERE list.order_id = info.order_id AND info.is_pause = 'pause' ORDER BY pause_time";
    $res2 = $db->getAll($sql);
    $res = array_merge($res1,$res2);
    sort($res);

    $j=2;
    $now_order_id = '';
    foreach ($res as $key => $value) {
        if($now_order_id == $value['order_id']){
            $value['pause_time'] = '';
            $value['order_id'] = '';
            $value['receive_name'] = '';
        }else{
            $now_order_id = $value['order_id'];
        }
        $objSheet->setCellValue("A".$j,$value['pause_time'])
        		->setCellValue("B".$j,$value['order_id'])
                ->setCellValue("C".$j,$value['id'])
                ->setCellValue("D".$j,$value['goods_code'])
                ->setCellValueExplicit("E".$j,$value['pause_num'],PHPExcel_Cell_DataType::TYPE_STRING)
                ->setCellValue("I".$j,$value['receive_name'])
                ->setCellValue("M".$j,$value['store']);
        $j++;
    }

    // $objPHPExcel->getActiveSheet()->getColumnDimension()->setAutoSize(true);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($dir."/../../down/pause_orders_table.xlsx");   //保存在服务器
    echo "ok";
}

// 检测订单号
if(isset($_GET['check_order_id'])){
    $order_id = $_GET['check_order_id'];
    $station = strtolower($_GET['station']);
    $store = $_GET['store'];
    $response_list = $station.'_response_list';
    
    $sql = "SELECT count(1) AS count FROM $response_list WHERE order_id = '{$order_id}' AND store = '{$store}'";
    $res = $db->getOne($sql);
    echo $res['count'];
}