<?php
$curl = curl_init();
$token = "TbNyCgwUK6XXTBl65zC68E4fGtzfU1rgFL7WIsjlardnP99C1FOfJE6";
$secret_key = "bEKgqeS4";
$page = "";
$limit = "";
$message_id = "";
curl_setopt($curl, CURLOPT_HTTPHEADER,
    array(
        "Authorization: $token.$secret_key",
    )
);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_URL,  "https://deu.wablas.com/api/report-realtime?page=$page&message_id=$message_id&limit=$limit");
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

$result = curl_exec($curl);
curl_close($curl);
echo "<pre>";
print_r($result);

?>