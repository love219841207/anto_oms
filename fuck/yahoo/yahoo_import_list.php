<?php
require_once("../header.php");
require_once("../PHPExcel/PHPExcel.php");//引入PHPExcel
require_once("../log.php");
//加大响应
set_time_limit(0); 
ini_set("memory_limit", "1024M"); 

//导入雅虎订单 = 手动
if(isset($_GET['import_add_list'])){
    if($_GET['import_add_list'] == 'yahoo_add_list'){

        // 导入List表
        $filename = $dir."/../uploads/yahoo_add_list.csv";

        function fgetcsv_reg(& $handle, $length = null, $d = ',', $e = '"') {
            $d = preg_quote($d);
            $e = preg_quote($e);
            $_line = "";
            $eof=false;
            while ($eof != true) {
                $_line .= (empty ($length) ? fgets($handle) : fgets($handle, $length));
                $itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
                if ($itemcnt % 2 == 0)
                    $eof = true;
            }
            $_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
            $_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
            preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
            $_csv_data = $_csv_matches[1];
            for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++) {
                $_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1', $_csv_data[$_csv_i]);
                $_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
            }
            return empty ($_line) ? false : $_csv_data;
        }
        //获取店铺
        $store = addslashes($_GET['store']);

        //日志
        $do = '[START] 导入订单order：'.$store;
        oms_log($u_name,$do,'yahoo_import','yahoo',$store,'-');

        $file = fopen($filename,'r'); 
        while ($data = fgetcsv_reg($file)) {
            $goods_list[] = $data;
        }
        array_shift($goods_list);
        foreach ($goods_list as $arr){
            // var_dump($arr);
            // echo count($arr);echo ' # ';
            if(is_array($arr) && !empty($arr)){
                $str = '';
                for($i=0; $i<114; $i++){
                    // echo $arr[$i]."<br>";
                    $str .= $arr[$i]."|*|";
                }
                $str = mb_convert_encoding($str,"UTF-8","shift-jis");
                $strs = explode("|*|",$str);
                $order_id = $strs[1];

                // 查询是否存在此订单
                $sql = "SELECT * from yahoo_response_list WHERE order_id = '{$order_id}'";
                $res = $db->getOne($sql);

                if(empty($res)){
                    $drives = $strs[3];
                    $purchase_date = $strs[7];
                    $receive_name = $strs[22].'['.$strs[30].']';
                    $phone = $strs[41];
                    $address = addslashes($strs[28].$strs[27].$strs[25].$strs[26]);
                    $post_code = $strs[29];
                    $strs[49] = str_replace('0', '', $strs[49]);
                    $strs[49] = str_replace('1', '当日达', $strs[49]);
                    $strs[49] = str_replace('2', '次日达', $strs[49]);
                    $buyer_send_method = $strs[45].$strs[49];
                    $want_date = $strs[46];
                    $want_time = $strs[47];
                    $buyer_post_code = $strs[72];
                    $buyer_address = addslashes($strs[71].$strs[70].$strs[68].$strs[69]);
                    $buyer_name = $strs[65].'['.$strs[73].']';
                    $buyer_email = $strs[86];
                    $buyer_phone = $strs[84];
                    $order_payment_method = $strs[88];
                    $payment_method = $strs[88];
                    $buyer_others = addslashes($strs[99]);
                    $shipping_price = $strs[102];
                    $cod_money = $strs[103];
                    $points = $strs[107];
                    $pay_money = $strs[109];
                    $order_total_money = $strs[110];
                    $coupon1 = $strs[112];
                    $coupon2 = $strs[113];
                    if($coupon1 == ''){
                        $coupon1 = 0;
                    }
                    if($coupon2 == ''){
                        $coupon2 = 0;
                    }
                    $coupon = $coupon1 - $coupon2;

                    $buyer_name = str_replace('[]', '', $buyer_name);
                    $receive_name = str_replace('[]', '', $receive_name);
                    // 地址大字
                    $address = str_replace('大字','',$address);
                    $buyer_address = str_replace('大字','',$buyer_address);

                    // 配送时间
                    $want_date = str_replace('/','-',$want_date);
                    if($want_time == '09:00-12:00'){
                        $want_time = '09:00～12:00';
                    }else if($want_time == '12:00-14:00'){
                        $want_time = '12:00～14:00';
                    }else if($want_time == '14:00-16:00'){
                        $want_time = '14:00～16:00';
                    }else if($want_time == '16:00-18:00'){
                        $want_time = '16:00～18:00';
                    }else if($want_time == '18:00-20:00'){
                        $want_time = '18:00～20:00';
                    }else if($want_time == '20:00-21:00'){
                        $want_time = '20:00～21:00';
                    }else if($want_time == '午前中'){
                        $want_time = '09:00～12:00';
                    }else{
                        $want_time = '';
                    }

                    // 配送方式
                    $payment_method = str_replace('商品代引','COD',$payment_method);
                    $buyer_send_method = str_replace('（宅*配*便のみ指定される商品の場合）', '', $buyer_send_method);
                    $buyer_send_method = str_replace('（宅*配*便のみ指定される商品の場合、選択不可）', '', $buyer_send_method);

                    // 修改手机格式
                    $buyer_phone = str_replace('-','',$buyer_phone);
                    $by3 = substr ($buyer_phone,-4,4);
                    $by2 = substr ($buyer_phone,-8,4);
                    $by1 = str_replace ($by2.$by3, '',$buyer_phone);
                    $buyer_phone = $by1.'-'.$by2.'-'.$by3;

                    $phone = str_replace('-','',$phone);
                    $re3 = substr($phone,-4,4);//从后往前截取4位
                    $re2 = substr($phone,-8,4);//从后往前截取4位
                    $re1 = str_replace($re2.$re3, '', $phone);//截取前几位
                    $phone = $re1.'-'.$re2.'-'.$re3;

                    // 邮编修改
                    $buyer_post_code = str_replace('-','',$buyer_post_code);
                    $po1 = substr($buyer_post_code,0,3);//从前面截取3位
                    $po2 = substr($buyer_post_code,3,4);//从前截取4位
                    $buyer_post_code = $po1.'-'.$po2;

                    $post_code = str_replace('-','',$post_code);
                    $pr1 = substr($post_code,0,3);//从前面截取3位
                    $pr2 = substr($post_code,3,4);//从前截取4位
                    $post_code = $pr1.'-'.$pr2;


                    //获取当前日期
                    $today = date('y-m-d',time());
                    $sql = "INSERT INTO yahoo_response_list(
                            store,          #店铺名
                            order_id,           #订单号
                            syn_day,    #导入日期
                            order_line,
                            drives,         #设备类型
                            purchase_date,  #订单日期
                            receive_name,   #收件人姓名
                            phone,  #收件人电话
                            address,    #收件地址
                            post_code,  #收件邮编
                            buyer_send_method,  #客人要求配送方式
                            want_date,
                            want_time,
                            buyer_post_code,    #购买者邮编  
                            buyer_address,  #购买者地址
                            buyer_name, #购买人
                            buyer_email,    #购买人邮箱
                            buyer_phone,    #购买人手机
                            order_payment_method,   #结算方式
                            payment_method,   #结算方式
                            buyer_others,   #客人备注（配送）
                            shipping_price, #运费
                            cod_money,  #代引手续费
                            points, #积分
                            pay_money,  #代引金额
                            order_total_money,  #合计金额
                            coupon,  #优惠券
                            send_id
                        )VALUES(
                            '{$store}', #店铺名
                            '{$order_id}',  #订单号
                            '{$today}',
                            CASE WHEN '{$payment_method}' = 'COD' THEN '1' ELSE '-2' END,
                            '{$drives}',  #设备类型
                            '{$purchase_date}', #订单日期
                            '{$receive_name}',  #收件姓名
                            '{$phone}', #收件人电话
                            '{$address}',   #收件地址
                            '{$post_code}', #收件邮编
                            '{$buyer_send_method}', #客人要求配送方式
                            '{$want_date}',
                            '{$want_time}',
                            '{$buyer_post_code}',   #购买人邮编
                            '{$buyer_address}', #购买者地址
                            '{$buyer_name}',    #购买人
                            '{$buyer_email}',   #购买人邮箱
                            '{$buyer_phone}',   #购买人手机
                            '{$order_payment_method}',  #结算方式
                            '{$payment_method}',  #结算方式
                            '{$buyer_others}',  #客人备注（配送）
                            '{$shipping_price}', #运费
                            '{$cod_money}', #代引手续费
                            '{$points}', #积分
                            '{$pay_money}', #代引金额
                            '{$order_total_money}', #合计金额
                            '{$coupon}', #优惠券
                            'ready'
                    )";
                    $res = $db->execute($sql);

                    //更新send_id
                    $sql = "UPDATE yahoo_response_list SET send_id = concat('yaho',id) WHERE send_id = 'ready'";
                    $res = $db->execute($sql);
                }
            }
        }
        echo 'order';
    }
    if($_GET['import_add_list'] == 'yahoo_add_info'){
        
        // 导入INFO表
        $filename = $dir."/../uploads/yahoo_add_info.csv";

        function fgetcsv_reg(& $handle, $length = null, $d = ',', $e = '"') {
            $d = preg_quote($d);
            $e = preg_quote($e);
            $_line = "";
            $eof=false;
            while ($eof != true) {
                $_line .= (empty ($length) ? fgets($handle) : fgets($handle, $length));
                $itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
                if ($itemcnt % 2 == 0)
                    $eof = true;
            }
            $_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
            $_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
            preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
            $_csv_data = $_csv_matches[1];
            for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++) {
                $_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1', $_csv_data[$_csv_i]);
                $_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
            }
            return empty ($_line) ? false : $_csv_data;
        }

        //获取店铺
        $store = addslashes($_GET['store']);

        //日志
        $do = '[START] 导入订单item：'.$store;
        oms_log($u_name,$do,'yahoo_import','yahoo',$store,'-');

        $file = fopen($filename,'r'); 
        while ($data = fgetcsv_reg($file)) {
            $goods_list[] = $data;
        }
        array_shift($goods_list);
        // 当前时间戳
        $now_time = time();
        foreach ($goods_list as $arr){
            if(is_array($arr) && !empty($arr)){
                $str = '';
                for($i=0; $i<31; $i++){
                    // echo $arr[$i]."<br>";
                    $str .= $arr[$i]."|*|";
                }
                $str = mb_convert_encoding($str,"UTF-8","shift-jis");
                $strs = explode("|*|",$str);
                $order_id = $strs[2];
                $goods_num = $strs[3];
                $goods_id = $strs[4];
                $sku = $strs[5];
                $goods_code = $strs[5];
                $goods_title = $strs[6];
                $goods_option = $strs[7];
                $goods_info = $strs[8];
                $unit_price = $strs[12]+$strs[19];
                $goods_send_info = $strs[30];

                // 提取运费代码
                $yfcode = substr($sku,0,1);

                // 拆分赠品开始 - - - - - - - - - - - - - - - - 
                // $sku_copy = str_replace($yfcode.'-', '', $sku);
                $sku_copy = preg_replace('/'.$yfcode.'-'.'/', '', $sku, 1); 
                $res_sku = explode('_', $sku_copy); 

                // 主商品代码    
                $count_goods_code = count($res_sku);
                $goods_code_main = trim($res_sku[$count_goods_code-1]);

                //插入详情
                $sql = "INSERT INTO yahoo_response_info (
                    store,
                    order_id,
                    goods_title,
                    goods_id,
                    goods_option,
                    goods_info,
                    sku,
                    goods_num,
                    unit_price,
                    import_time,
                    goods_send_info,
                    yfcode
                ) VALUES(
                    '{$store}', #店铺名
                    '{$order_id}',  #订单号
                    '{$goods_title}', #商品名
                    '{$goods_id}', #商品ID
                    '{$goods_option}', #商品选项
                    '{$goods_info}',    #选项信息
                    '{$goods_code_main}',   #SKU
                    '{$goods_num}',     #购买数量
                    '{$unit_price}',    #单价
                    '{$now_time}',
                    '{$goods_send_info}',    #发送文字内容
                    '{$yfcode}' #运费代码
                )";
                $res = $db->execute($sql);

                // 赠品
                
                if($count_goods_code == 1){

                }else{
                    for($i=0; $i<$count_goods_code-1; $i++){
                        $now_goods_code = trim($res_sku[$i]);
                        $sql = "INSERT INTO yahoo_response_info(
                            store,
                            order_id,
                            goods_title,
                            goods_id,
                            goods_option,
                            goods_info,
                            sku,
                            goods_num,
                            unit_price,
                            import_time,
                            goods_send_info,
                            yfcode
                        )VALUES(
                            '{$store}', #店铺名
                            '{$order_id}',  #订单号
                            '', #商品名
                            '{$goods_id}', #商品ID
                            '', #商品选项
                            '',    #选项信息
                            '{$now_goods_code}',   #SKU
                            '{$goods_num}',     #购买数量
                            '{$unit_price}',    #单价
                            '{$now_time}',
                            '{$goods_send_info}',    #发送文字内容
                            '{$yfcode}' #运费代码
                        )";
                        $res = $db->execute($sql);
                    }
                }
            }
        }
        // 更新 cod_money
        $sql = "UPDATE yahoo_response_info info,yahoo_response_list list SET info.cod_money = list.cod_money WHERE info.order_id = list.order_id AND import_time = '{$now_time}'";
        $res = $db->execute($sql);
        echo 'ok';
    }
}
