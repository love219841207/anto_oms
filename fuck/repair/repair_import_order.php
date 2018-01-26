<?php
require_once("../header.php");
require_once("../log.php");
require_once("../../pdo/repair.PdoMySQL.class.php");//REPAIR_PDO
$shdb = new RepairPdoMySQL();

// 读取拉取订单
if(isset($_GET['read_repair_order'])){
    $sql = "SELECT * FROM repair_import_list";
    $res = $db->getAll($sql);
    echo json_encode($res);
}

// 售后远程拉取订单
if(isset($_POST['syn_repair_order'])){
    //日志
    $do = '[START] 拉取订单列表：开始';
    oms_log($u_name,$do,'order_syn','repair','售后','-');
    $start_time = $_POST['s_date'];
    $end_time = $_POST['e_date'];
    // 清空 repair_import_list
    $sql = "TRUNCATE repair_import_list;";
    $res = $db->execute($sql);

    // 拉取原数据
    $sql = "
        SELECT re_good1 as goods_code,receive_phone,receive_code,receive_house,receive_name,id,receive_money,send_method,email,store,u_name,add_type,money,re_express,re_company,re_date from repair_list where  over_day between '{$start_time}' and '{$end_time}' union all select re_good2 as goods_code,receive_phone,receive_code,receive_house,receive_name,id,receive_money,send_method,email,store,u_name,add_type,money,re_express,re_company,re_date from repair_list where  over_day between '{$start_time}' and '{$end_time}' union all select re_good3 as goods_code,receive_phone,receive_code,receive_house,receive_name,id,receive_money,send_method,email,store,u_name,add_type,money,re_express,re_company,re_date from repair_list where  over_day between '{$start_time}' and '{$end_time}' union all select re_good4 as goods_code,receive_phone,receive_code,receive_house,receive_name,id,receive_money,send_method,email,store,u_name,add_type,money,re_express,re_company,re_date from repair_list where  over_day between '{$start_time}' and '{$end_time}' union all select re_good5 as goods_code,receive_phone,receive_code,receive_house,receive_name,id,receive_money,send_method,email,store,u_name,add_type,money,re_express,re_company,re_date from repair_list where  over_day between '{$start_time}' and '{$end_time}';";
    $res = $shdb->getAll($sql);

    $import_date  = date('Y-m-d H:i:s',time());
    // 数据处理
    foreach ($res as $key => $value) {
        //如果goods_code为空，跳过
        if($value['goods_code']==""){
            continue;
        }
        //售后id拼接,注文番号
        $oms_id="b".$value['id'];
        $order_id="b".$value['id'];
        $arr=array();
        $arr=explode("*",$value['goods_code']);
        if(isset($arr)){
            $goods_code = $arr[0];
            //如果不是sku，那么跳过
            // if($goods_code==$value['goods_code']){
            //  continue;
            // }
            @$num = $arr[1];
        }
        $email = $value['email'];
        $result = explode(' ',$email);
        $buyer_email = $result[0];

        $receive_phone = $value['receive_phone'];
        $post_code = $value['receive_code'];
        $address = $value['receive_house'];
        $receive_name = $value['receive_name'];
        $pay_money = $value['receive_money'];
        $order_type = $value['add_type'];
        $receive_phone = $value['receive_phone'];
        $send_method = $value['send_method'];
        $order_store = $value['store'];
        $holder = $value['u_name'];

        // 插入导入表
        $sql = "INSERT INTO repair_import_list(
                order_id,
                goods_code,
                goods_num,
                receive_phone,
                post_code,
                address,
                receive_name,
                pay_money,
                order_type,
                send_method,
                buyer_email,
                order_store,
                holder,
                import_date,
                is_pass
            )values(
                '{$order_id}',
                '{$goods_code}',
                '{$num}',
                '{$receive_phone}',
                '{$post_code}',
                '{$address}',
                '{$receive_name}',
                '{$pay_money}',
                '{$order_type}',
                '{$send_method}',
                '{$buyer_email}',
                '{$order_store}',
                '{$holder}',
                '{$import_date}',
                '0'
            )
        ";
        $res = $db->execute($sql);
    }
    //日志
    $do = '[END] 拉取订单列表：结束';
    oms_log($u_name,$do,'order_syn','repair','售后','-');
    echo 'ok';
}