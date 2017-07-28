<?php
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSLCERT, ‘SP266521_fBKi6O9ahPfT3ISW’);
    curl_setopt($ch, CURLOPT_SSLKEY, ‘SL266521_ZKayAawDUELnvjvF’);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

    $result = curl_exec($ch);
    curl_close($sh);
    
    ?>