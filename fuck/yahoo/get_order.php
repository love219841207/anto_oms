<?php
/**
 * ルビ振りAPIへのリクエストサンプル（GET）
 *
 */

$api = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/ V1 / orderCount';
$appid = 'dj00aiZpPVY0UGJGWjMzYkg5UiZzPWNvbnN1bWVyc2VjcmV0Jng9ZDE-';
$params = array(
    'sentence' => '明鏡止水'
);
 
$ch = curl_init($api.'?'.http_build_query($params));
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERAGENT      => "Yahoo AppID: $appid"
));
 
$result = curl_exec($ch);
curl_close($ch);
?>
<pre>
<?php echo htmlspecialchars(
             print_r(new SimpleXMLElement($result), true)
           ) ?>
</pre>

<!--     $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSLCERT, 'SP266521_fBKi6O9ahPfT3ISW');
    curl_setopt($ch, CURLOPT_SSLKEY, 'SL266521_ZKayAawDUELnvjvF');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

    $result = curl_exec($ch);
    curl_close($sh);
    
    ?> -->