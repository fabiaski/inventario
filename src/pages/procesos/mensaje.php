<?php

$url = 'https://graph.facebook.com/v25.0/974404015762245/messages';
$token = 'EAAeo3pOZBwTYBSUbzC3EBn1wuyl20Nl6HDjfFwAaSrUJyCOoqKdgspcnaBQp1REn67t0XNLtEavcQPLz5DneUckEW93J2toOQZC39IcIcOg541M6nbao8JKyQfhmzZABwNrQP1n9AcE9AAa8gzjOSnmqvqriw5FGywBRsp0BJYjBdbZCU3TXMBPMZCeVgL9XBfwZDZD';

$nombre = "fabian";

$data = array(
    "messaging_product" => "whatsapp",
    "recipient_type" => "individual",
    "to" => "+573229619350",
    "type" => "template",
    "template" => array(
        "name" => "prueba_mss",
        "language" => array(
            "code" => "es_AR"
        ),
        "components" => array(
            array(
                "type" => "body",
                "parameters" => array(
                    array(
                        "type" => "text",
                        "text" => $nombre
                    )
                )
            )
        )
    )
);

$data_string = json_encode($data);

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string))
);

$result = curl_exec($curl);
curl_close($curl);
echo $result;

?>
