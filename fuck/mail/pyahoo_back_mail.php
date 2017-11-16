<?php
require_once("../PHPMailer/PHPMailerAutoload.php");
$dir = dirname(__FILE__);
require_once($dir."/../../pdo/PdoMySQL.class.php");//OMS_PDO
require_once("../log.php");

ignore_user_abort(true);
set_time_limit(0);
$db = new PdoMySQL();

if(isset($_POST['store'])){
    // POST参数
    $u_name = $_POST['u_name'];
    $u_num = $_POST['u_num'];
    $store = $_POST['store'];
    $order_items = $_POST['order_items'];
    $mail_tpl = $_POST['mail_tpl'];
    $station = 'p_yahoo';

    //读取店铺配置
    $sql = "SELECT * FROM conf_P_Yahoo WHERE store_name = '{$store}'";
    $res = $db->getOne($sql);

    $mail_name = $res['mail_name'];
    $mail_id = $res['mail_id'];
    $mail_pwd = $res['mail_pwd'];
    $mail_smtp = $res['mail_smtp'];
    $mail_port = $res['mail_port'];
    $mail_answer_addr = $res['mail_answer_addr'];
    $mail_over_send = $res['mail_over_send'];

    // 按照合单号发送
    $sql = "SELECT send_id FROM p_yahoo_response_list WHERE order_id IN ({$order_items})";
    $res = $db->getAll($sql);
    $sss = array();
    foreach ($res as $value) {
        if (in_array($value['send_id'], $sss)) {

        }else{
            array_push($sss,$value['send_id']);
        }
    }

    foreach ($sss as $keyvalue) {
        $value = $keyvalue;

        if($mail_tpl == 'send_express'){    
            // 如果是发货通知信
            $sql = "UPDATE p_yahoo_express SET over_mail = 1 WHERE pyahoo_order_id IN ($order_items)";
            $res = $db->execute($sql);
            $sql = "UPDATE history_send SET over_mail = 1 WHERE order_id IN ($order_items)";
            $res = $db->execute($sql);
            //读取信件内容
            $sql = "SELECT * FROM mail_tpl WHERE store_name = '{$store}' AND model_name = 'send_express'";
            $res = $db->getOne($sql);
        }else{
            if($mail_tpl == 'custom'){
                //读取信件内容
                $sql = "SELECT * FROM mail_tpl WHERE store_name = '{$u_num}' AND model_name = 'custom'";
                $res = $db->getOne($sql);
            }else{
                //读取信件内容
                $sql = "SELECT * FROM mail_tpl WHERE id = '{$mail_tpl}'";
                $res = $db->getOne($sql);
            }
        }
        $mail_topic = $res['mail_topic'];
        $mail_html = $res['mail_html'];
        $mail_txt = $res['mail_txt'];

        //读取邮箱、收件人等信息
        $sql = "SELECT * FROM p_yahoo_response_list WHERE send_id = '{$value}'";
        $res = $db->getOne($sql);
        $purchase_date = $res['purchase_date']; #付款日期

        $oms_id = $res['id'];   #OMS-ID
        $to_mail = $res['buyer_email']; #邮箱
        $buyer_name = $res['buyer_name'];   #购买人
        $receive_name = $res['receive_name'];   #收货人
        $order_id = $res['order_id'];   #订单号
        $express_company = $res['express_company']; #快递公司
        $send_method = $res['send_method']; #配送方式
        $post_code = $res['post_code']; #客人邮编
        $address = $res['address']; #配送地址
        $express_num = $res['oms_order_express_num'];   #快递单号
        $express_day = $res['express_day']; #快递日期
        $all_total_money = $res['all_total_money']; 
        $order_total_money = $res['order_total_money']; 
        $payment_method = $res['payment_method'];   
        // 替换[]
        $buyer_name = preg_replace('/\[.*?\]/', '', $buyer_name);
        $receive_name = preg_replace('/\[.*?\]/', '', $receive_name);

        $sql = "SELECT sum(order_tax) as order_tax,sum(points) as points,sum(coupon) as coupon,shipping_price FROM p_yahoo_response_list WHERE send_id = '{$value}'";
        $res = $db->getOne($sql);

        $order_tax = $res['order_tax']; 
        $points = $res['points'];   
        $coupon = $res['coupon'];   
        $shipping_price = $res['shipping_price'];   
        $all_total_money = $all_total_money - $points - $coupon;
        if($coupon == ''){
            $coupon = 0;
        }

        // 初始化title
        $u_info = '';
        $cod_money = '';

        // 读取购买信息
        // 查询该send_id下的订单
        $sql = "SELECT order_id FROM p_yahoo_response_list WHERE send_id = '{$value}'";
        $res = $db->getAll($sql);
        $now_order_id = '';
        foreach ($res as $value) {
            $now_order_id = $now_order_id.'\''.$value['order_id'].'\',';
        }
        $now_order_ids = rtrim($now_order_id, ",");

        $sql = "SELECT * FROM p_yahoo_response_info WHERE order_id IN ({$now_order_ids})";
        $res = $db->getAll($sql);
        $goods_money = 0;
        foreach ($res as $val) {
            $goods_title = $val['goods_title'];
            $sku = $val['goods_code'];
            $goods_num = $val['goods_num'];
            $unit_price = $val['unit_price'];
            $item_price = $val['item_price'];
            $cod_money = $val['cod_money'];

            $u_info = $u_info.'<tr >
            <td style="color: #616161;">'.$goods_title.'</td>
            <td>'.$sku.'</td>
            <td style="text-align: right;font-family: monospace;">'.$unit_price.' * '.$goods_num.' = '.$item_price.'円</td>
            </tr>';

            $goods_money = $goods_money + $item_price;
        }

        $order_info = '
        <table width="100%" border="1" bordercolor="no" cellspacing="1" cellpadding="6" style="border-collapse: collapse;font-size:12px;border-color: #ddd;width:100%; font-family: Meiryo;">
        <tr style="background: #009688;color: #FFF;">
        <td style="text-align: center;">商品名/商品オプション</td>
        <td width="25%">商品コード/サブコード</td>
        <td style="text-align:right;" width="20%">単価 * 数量 = 小計</td>
        </tr>
        '.$u_info.'
        <tr>
        <td rowspan="7" style="text-align: left; font-size:14px;color: #018276;">
        </td>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">商品金額合計:</span>
        <span style="width:80px;display: inline-block;">'.$goods_money.'円</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">消费税:</span>
        <span style="width:80px;display: inline-block;">'.$order_tax.'円</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">送料:</span>
        <span style="width:80px;display: inline-block;">'.$shipping_price.'円</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">値引き:</span>
        <span style="width:80px;display: inline-block;">'.$coupon.'円</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">ポイント利用分:</span>
        <span style="width:80px;display: inline-block;">'.$points.'</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">手数料:</span>
        <span style="width:80px;display: inline-block;">'.$cod_money.'</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">合計金額（税込）:</span>
        <span style="width:80px;display: inline-block;color:#ff5722;font-weight: bold;font-size: 14px;">'.$all_total_money.'円</span>
        </td>
        </tr>
        </table>';

        $pin_book = '
        <table width="100%" border="1" bordercolor="no" cellspacing="1" cellpadding="6" style="border-collapse: collapse;font-size:12px;border-color: #ddd;width:100%; font-family: Meiryo;">
        <tr style="border-color: #FFF;">
        <td>'.$store.'</td>
        <td colspan="2" style="text-align: right;">発行日：'.$express_day.'</td>
        </tr>
        <th colspan="3" style="border-color: #FFF;border-bottom:4px solid #009688;color:#009688;text-align: center;font-size:18px;">
        納 品 書
        </th>
        </table>
        <table width="100%" border="1" bordercolor="no" cellspacing="1" cellpadding="6" style="border-collapse: collapse;font-size:12px;border-color: #FFF;width:100%;line-height: 10px; font-family: Meiryo;">
        <tr>
        <td>〒'.$post_code.'</td>
        <td style="text-align:right;">'.$store.'</td>
        </tr>
        <tr>
        <td>'.$address.'</td>
        <td style="text-align:right;">〒270-1437</td>
        </tr>
        <tr>
        <td>'.$buyer_name.' 様</td>
        <td style="text-align:right;">千葉県 白井市</td>
        </tr>
        <tr>
        <td></td>
        <td style="text-align:right;">木833-15</td>
        </tr>
        <tr>
        <td colspan="3" style="line-height: 18px;">この度は、「'.$store.'」にてお買い上げいただきまして、誠にありがとうございました。
        お買い上げ明細書を送付いたしますので、ご確認いただけますようお願い申し上げます。</td>
        </tr>
        <tr style="line-height:30px;border-bottom: 2px solid #009688;color:#009688;font-size: 14px;text-align: center;">
        <td colspan="3">お買い上げ明細</td>
        </tr>   

        <tr>
        <td></td>
        <td colspan="3" style="text-align: right;">
        <span style="color:#009688;">ご注文日：</span>
        <span style="width:150px;text-align:left;display: inline-block;">'.$purchase_date.'</span>
        </td>
        </tr>
        <tr>
        <td>〒'.$post_code.'</td>
        <td colspan="3" style="text-align: right;">
        <span style="color:#009688;">ご注文番号：</span>
        <span style="width:150px;text-align:left;display: inline-block;">'.$now_order_ids.'</span>
        </td>
        </tr>
        <tr>
        <td>'.$address.'</td>
        <td colspan="3" style="text-align: right;">
        <span style="color:#009688;">お支払方法：</span>
        <span style="width:150px;text-align:left;display: inline-block;">'.$payment_method.'</span>
        </td>
        </tr>
        <tr>
        <td>'.$buyer_name.' 様</td>
        <td colspan="3" style="text-align: right;">
        <span style="color:#009688;">お届け方法：</span>
        <span style="width:150px;text-align:left;display: inline-block;">'.$send_method.'</span>
        </td>
        </tr>
        <tr>
        <td></td>
        <td colspan="3" style="text-align: right;">
        <span style="color:#009688;">お届け希望日：</span>
        <span style="width:150px;text-align:left;display: inline-block;">希望日なし</span>
        </td>
        </tr>
        <tr>
        <td></td>
        <td colspan="3" style="text-align: right;">
        <span style="color:#009688;">お届け希望時間：</span>
        <span style="width:150px;text-align:left;display: inline-block;">希望時間なし</span>
        </td>
        </tr>
        </table>
        <table width="100%" border="1" bordercolor="no" cellspacing="1" cellpadding="6" style="border-collapse: collapse;font-size:12px;border-color: #ddd;width:100%; font-family: Meiryo;">
        <tr style="background: #009688;color: #FFF;">
        <td style="text-align: center;">商品名/商品オプション</td>
        <td width="25%">商品コード/サブコード</td>
        <td style="text-align:right;" width="20%">単価 * 数量 = 小計</td>
        </tr>
        '.$u_info.'
        <tr>
        <td rowspan="7" style="text-align: left; font-size:14px;color: #018276;">
        ■ 備考
        お買い上げ明細書についてご不明な点がございましたら、上記連絡先までお問い合わせください。
        </td>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">商品金額合計:</span>
        <span style="width:80px;display: inline-block;">'.$goods_money.'円</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">消费税:</span>
        <span style="width:80px;display: inline-block;">'.$order_tax.'円</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">送料:</span>
        <span style="width:80px;display: inline-block;">'.$shipping_price.'円</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">値引き:</span>
        <span style="width:80px;display: inline-block;">'.$coupon.'円</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">ポイント利用分:</span>
        <span style="width:80px;display: inline-block;">'.$points.'</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">手数料:</span>
        <span style="width:80px;display: inline-block;">'.$cod_money.'</span>
        </td>
        </tr>
        <tr>
        <td colspan="2" style="text-align: right;">
        <span style="color:#009688;">合計金額（税込）:</span>
        <span style="width:80px;display: inline-block;color:#ff5722;font-weight: bold;font-size: 14px;">'.$all_total_money.'円</span>
        </td>
        </tr>
        </table>
        ';

        //替换信件变量
        $mail_topic = str_replace('#buyer_name#', $buyer_name, $mail_topic);
        $mail_topic = str_replace('#receive_name#', $receive_name, $mail_topic);
        $mail_topic = str_replace('#order_id#', $now_order_ids, $mail_topic);
        $mail_topic = str_replace('#express_company#', $express_company, $mail_topic);
        $mail_topic = str_replace('#send_method#', $send_method, $mail_topic);
        $mail_topic = str_replace('#express_num#', $express_num, $mail_topic);
        $mail_topic = str_replace('#order_info#', '', $mail_topic);
        $mail_topic = str_replace('#pin_book#', '', $mail_topic);

        $mail_html = str_replace('#buyer_name#', $buyer_name, $mail_html);
        $mail_html = str_replace('#receive_name#', $receive_name, $mail_html);
        $mail_html = str_replace('#order_id#', $now_order_ids, $mail_html);
        $mail_html = str_replace('#express_company#', $express_company, $mail_html);
        $mail_html = str_replace('#send_method#', $send_method, $mail_html);
        $mail_html = str_replace('#express_num#', $express_num, $mail_html);
        $mail_html = str_replace('#order_info#', $order_info, $mail_html);
        $mail_html = str_replace('#pin_book#', $pin_book, $mail_html);

        //发送
        $mail = new PHPMailer;
        $mail->CharSet = "UTF-8";
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = $mail_smtp;  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // 开启SMTP验证
        $mail->Username = $mail_id;                 // SMTP 账号
        $mail->Password = $mail_pwd;                           // SMTP 密码
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = $mail_port;                                    // 邮件端口
        $mail->setFrom($mail_answer_addr, $mail_name);      // 发件箱与收件箱相同
        $mail->addAddress($to_mail, $buyer_name);     // 收件人
        $mail->addBCC('329331097@qq.com');    //秘密抄送
        $mail->addReplyTo($mail_answer_addr, '');   //邮件回复地址
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $mail_topic;   //邮件标题
        $mail->Body    = $mail_html;    //邮件内容
        $mail->AltBody = $mail_txt; //未设置HTML将会收到的内容

        if(!$mail->send()) {
            // echo '邮件发送失败.';
            $error_info = $mail->ErrorInfo;
            $error_info = date("Y-m-d H-i-s").$mail_topic.$error_info;
            // 发信失败，记录在案
            $sql = "INSERT INTO mail_error (error_order_id,error_info) VALUES ('{$now_order_ids}','{$error_info}')";
            $res = $db->execute($sql);
        } else {
            if($mail_tpl == 'send_express'){
                // 日志
                $do = '发送发货通知信';
                oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
            }else{
                // 日志
                $do = '发信： <'.$mail_topic.'>';
                oms_log($u_name,$do,'change_order',$station,$store,$oms_id);
            }

            $now_order_ids = str_replace('\'', '', $now_order_ids);
            // 插入mail_history表
            $sql="INSERT INTO mail_history (store,order_id,mail_tpl,buyer_name,mail_title,do_time,who_name)VALUES('{$store}','{$now_order_ids}','{$mail_tpl}','{$buyer_name}','{$mail_topic}',NULL,'{$u_name}');";
            $res = $db->execute($sql);
        }

        usleep(90000);
    }
}