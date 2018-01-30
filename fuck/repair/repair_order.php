<?php
require_once("../header.php");
require_once("../log.php");

// 删除订单
if(isset($_POST['del_repair_orders'])){
    $del_repair_items = $_POST['del_repair_orders'];
    $del_repair_items = '('.$del_repair_items.')';

    $sql = "SELECT order_id FROM repair_response_list WHERE id IN $del_repair_items";
    $res = $db->getAll($sql);

    foreach ($res as $value) {
        $now_order_id = $value['order_id'];
        $sql = "DELETE FROM repair_response_info WHERE order_id = '{$now_order_id}'";
        $res = $db->execute($sql);
        //日志
        $do = '[售后] 删除订单：'.$now_order_id;
        oms_log($u_name,$do,'change_order','repair','售后','-');
    }

    // 删除
    $sql = "DELETE FROM repair_response_list WHERE id IN $del_repair_items";
    $res = $db->execute($sql);
    echo 'ok';
}

// 读取拉取订单
if(isset($_GET['read_pass_orders'])){
    @$search_field = addslashes($_GET['search_field']);
    @$search_key = addslashes($_GET['search_key']);
    $s_date = addslashes($_GET['s_date']);
    $e_date = addslashes($_GET['e_date']);

    if($s_date == '' or $e_date == ''){
        if($search_field == ''){
            $sql = "SELECT * FROM repair_response_info,repair_response_list WHERE repair_response_list.order_id = repair_response_info.order_id";
        }else{
            $sql = "SELECT * FROM repair_response_info,repair_response_list WHERE repair_response_list.order_id = repair_response_info.order_id AND repair_response_list.{$search_field} LIKE '%{$search_key}%'";
        }
    }else{
        if($search_field == ''){
            $sql = "SELECT * FROM repair_response_info,repair_response_list WHERE repair_response_list.order_id = repair_response_info.order_id AND over_day BETWEEN '{$s_date}' AND '{$e_date}'";
        }else{
            $sql = "SELECT * FROM repair_response_info,repair_response_list WHERE repair_response_list.order_id = repair_response_info.order_id AND repair_response_list.{$search_field} LIKE '%{$search_key}%' AND over_day BETWEEN '{$s_date}' AND '{$e_date}'";
        }
    }

    $res = $db->getAll($sql);

    foreach ($res as $key => $value) {
        $res[$key]['is_click'] = false;
    }

    echo json_encode($res);
}