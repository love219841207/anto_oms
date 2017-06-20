<?php

$ch = curl_init();

curl_setopt($ch,CURLOPT_URL,"https://mws.amazonservices.jp/Orders/2013-09-01?AWSAccessKeyId=AKIAJADEFRPERGFBPYCA&Action=ListOrders&CreatedAfter=2016-12-01T00%3A30%3A00Z&MarketplaceId.Id.1=A1VC38T7YXB528&OrderStatus.Status.1=Unshipped&OrderStatus.Status.2=PartiallyShipped&SellerId=A219DKR2R6TN19&SignatureMethod=HmacSHA256&SignatureVersion=2&Timestamp=2017-06-20T08%3A44%3A12Z&Version=2013-09-01&Signature=CeJ%2FvEMjQoR7rnfdyQ1oyoF3IaG7mFFghJ%2F3KFabfho%3D");

curl_setopt($ch,CURLOPT_HEADER,1);

curl_exec($ch);

curl_close($ch);
// phpinfo();