<?php
require_once("../header.php");
require_once("../log.php");
require_once("../../pdo/repair.PdoMySQL.class.php");//REPAIR_PDO
$shdb = new RepairPdoMySQL();

// pass 订单
if(isset($_GET['pass_repair'])){
    // 导入pass 的订单
    $sql = "INSERT INTO repair_response_list (
        order_line,
        order_id,
        send_id,
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
        over_day
    ) SELECT 
        '2',
        order_id,
        order_id,
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
        over_day
    FROM repair_import_list WHERE is_pass = 1 GROUP BY order_id";
    $res = $db->execute($sql);

    $sql = "INSERT INTO repair_response_info (
        order_id,
        goods_code,
        goods_num,
        pause_ch,
        pause_jp,
        a_repo_num,
        b_repo_num,
        is_pause
    ) SELECT 
        order_id,
        goods_code,
        goods_num,
        0,
        0,
        0,
        0,
        'pause'
    FROM repair_import_list WHERE is_pass = 1";
    $res = $db->execute($sql);

    // 删除转入的订单
    $sql = "DELETE FROM repair_import_list WHERE is_pass = 1";
    $res = $db->execute($sql);
    echo 'ok';
}

// 删除订单
if(isset($_POST['del_repair_items'])){
    $del_repair_items = $_POST['del_repair_items'];
    $del_repair_items = '('.$del_repair_items.')';
    // 删除
    $sql = "DELETE FROM repair_import_list WHERE id IN $del_repair_items";
    $res = $db->execute($sql);
    echo 'ok';
}

// 读取拉取订单
if(isset($_GET['read_repair_order'])){
    $sql = "SELECT * FROM repair_import_list WHERE is_pass = 0";
    $res1 = $db->getAll($sql);
    $sql = "SELECT * FROM repair_import_list WHERE is_pass = 1";
    $res2 = $db->getAll($sql);
    foreach ($res1 as $key => $value) {
        $res1[$key]['is_click'] = false;
    }
    foreach ($res2 as $key => $value) {
        $res2[$key]['is_click'] = false;
    }
    $final_res['res1'] = $res1;
    $final_res['res2'] = $res2;
    echo json_encode($final_res);
}

// 售后远程拉取订单
if(isset($_POST['syn_repair_order'])){
    $start_time = $_POST['s_date'];
    $end_time = $_POST['e_date'];
    //日志
    $do = '[START] 拉取订单列表：开始，终了日：'.$start_time.' TO '.$end_time;
    oms_log($u_name,$do,'order_syn','repair','售后','-');
    // 清空 repair_import_list
    $sql = "TRUNCATE repair_import_list;";
    $res = $db->execute($sql);

    // 拉取原数据
    $sql = "
        SELECT re_good1 as goods_code,receive_phone,receive_code,receive_house,receive_name,id,receive_money,send_method,email,store,u_name,add_type,money,re_express,re_company,re_date,over_day from repair_list where  over_day between '{$start_time}' and '{$end_time}' union all select re_good2 as goods_code,receive_phone,receive_code,receive_house,receive_name,id,receive_money,send_method,email,store,u_name,add_type,money,re_express,re_company,re_date,over_day from repair_list where  over_day between '{$start_time}' and '{$end_time}' union all select re_good3 as goods_code,receive_phone,receive_code,receive_house,receive_name,id,receive_money,send_method,email,store,u_name,add_type,money,re_express,re_company,re_date,over_day from repair_list where  over_day between '{$start_time}' and '{$end_time}' union all select re_good4 as goods_code,receive_phone,receive_code,receive_house,receive_name,id,receive_money,send_method,email,store,u_name,add_type,money,re_express,re_company,re_date,over_day from repair_list where  over_day between '{$start_time}' and '{$end_time}' union all select re_good5 as goods_code,receive_phone,receive_code,receive_house,receive_name,id,receive_money,send_method,email,store,u_name,add_type,money,re_express,re_company,re_date,over_day from repair_list where  over_day between '{$start_time}' and '{$end_time}';";
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
        $over_day = $value['over_day'];

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
                over_day,
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
                '{$over_day}',
                '0'
            )
        ";
        $res = $db->execute($sql);
    }
    //日志
    $do = '[END] 拉取订单列表：结束';
    oms_log($u_name,$do,'order_syn','repair','售后','-');
    //日志
    $do = '[START] 订单自动验证：开始';
    oms_log($u_name,$do,'order_syn','repair','售后','-');

    // 订单验证
    $sql = "SELECT * FROM repair_import_list";
    $res = $db->getAll($sql);

    foreach ($res as $value) {
        $is_pass = 1;
        $id = $value['id'];
        $goods_code = $value['goods_code'];
        $goods_num = $value['goods_num'];
        $receive_phone = $value['receive_phone'];
        $post_code = $value['post_code'];
        $address = $value['address'];
        $receive_name = $value['receive_name'];
        $send_method = $value['send_method'];

        // 去掉空格
        $goods_code = str_replace(' ', '', $goods_code);
        $goods_num = str_replace(' ', '', $goods_num);

        // 格式化短横线
        $count_line = substr_count($goods_code,'-');
        $replace_line = '--';
        for($i=0;$i<$count_line;$i++){
            $goods_code = str_replace($replace_line,"-",$goods_code);
            $sql = "UPDATE repair_import_list SET goods_code = '{$goods_code}' WHERE id = '{$id}'";
            $res = $db->execute($sql);
        }

        // 1、判断商品代码是否存在
        $sql = "SELECT 1 FROM goods_type WHERE goods_code='{$goods_code}' limit 1";
        $res = $rdb->getOne($sql);
        if(empty($res)){
            $is_pass = 0;
        }

        // 2、判断数量是否是数字
        if(preg_match("/^\d+$/", $goods_num)){

        }else{
            $is_pass = 0;
        }

        // 3、判断字段是否为空
        if($goods_num == '' or $goods_code == '' or $receive_phone == '' or $post_code == '' or $address == '' or $receive_name == ''){
            $is_pass = 0;
        }

        // 4、send_method 为 -
        if($send_method == '-'){
            $is_pass = 0;
        }

        // 更新结果
        if($is_pass == 1){
            $sql = "UPDATE repair_import_list SET is_pass = 1 WHERE id = '{$id}'";
            $res = $db->execute($sql);
        }
    }

    
    //日志
    $do = '[END] 订单自动验证：结束';
    oms_log($u_name,$do,'order_syn','repair','售后','-');
    echo 'ok';
}