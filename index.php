<?php
header('Access-Control-Allow-Origin:*'); // cross access from remote domain
$json = file_get_contents('php://input'); // get HTTP post content
$rJson = json_decode($json, true); // decoded JSON
date_default_timezone_set('UTC');

$hook = $rJson["hook"];
$content_type = $rJson["content_type"];
unset($rJson["hook"]);
unset($rJson["content_type"]);

$response = new stdClass;

$ch = curl_init($hook);
switch ($content_type) {
    case 'application/json':
        $jsonString = json_encode($rJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonString);
        break;
    case 'application/x-www-form-urlencoded':
        $query = '';
        $count = 0;
        foreach ($rJson as $key => $value) {
            if ($count < count($rJson) - 1) {
                $query = $query . $key . '=' . $value . '&';
            } else {
                $query = $query . $key . '=' . $value;
            }
            $count++;
        }
        $response->query = $query;

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        break;
    default:
        # code...
        break;
}

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 50);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$response->result = $result;


switch ($httpCode) {
    case 200:
        $msg = 'Success';
        break;
    case 214:
        $msg = 'Already Subscribed';
        break;
    default:
        $msg = 'Oops, please try again.[msg_code=' . $httpCode . ']';
        break;
}

$response->code = $httpCode;
$response->msg = $msg;


$respjson = json_encode($response);
header('Content-Type: application/json');
echo $respjson;
?>
