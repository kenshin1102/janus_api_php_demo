<?php
error_reporting(E_ALL ^ E_NOTICE);

function generateRandomString($length = 12)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function CallAPI($method, $url, $data = false)
{
    try {
        $curl = curl_init();
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

$action = !empty($_POST['action']) ? $_POST['action'] : false;
$sessionId = !empty($_POST['sessionId']) ? $_POST['sessionId'] : false;
$plugin = !empty($_POST['plugin']) ? $_POST['plugin'] : false;
$handleId = !empty($_POST['handleId']) ? $_POST['handleId'] : false;
$message = !empty($_POST['message']) ? $_POST['message'] : false;
$candidate = !empty($_POST['candidate']) ? $_POST['candidate'] : false;
$jsep = !empty($_POST['jsep']) ? $_POST['jsep'] : false;

$server = 'http://localhost:8088/janus';
$password = 'janusrocks';

switch ($action) {
    case 'CreateSession' :
        {
            $data = '{"janus":"create","transaction":"' . generateRandomString() . '","apisecret":"' . $password . '"}';
            $result = CallAPI('POST', $server, $data);
            echo($result);
        }
        break;
    case 'Refresh' :
        {
            $date = date_create();
            date_timestamp_get($date);

            $longpoll = $server . "/" . $sessionId . "?rid=" . date_timestamp_get($date);
            $longpoll = $longpoll . '&maxev=1&_=' . date_timestamp_get($date) . '&apisecret=' . $password;
            $result = CallAPI('GET', $longpoll);
            echo($result);
        }
        break;
    case 'createHandle' :
        {
            $data = '{"janus": "attach", "plugin": "' . $plugin . '", "transaction": "' . generateRandomString() . '","apisecret":"' . $password . '"}';
            $server = $server . '/' . $sessionId;
            $result = CallAPI('POST', $server, $data);
            echo($result);
        }
        break;
    case 'destroySession' :
        {
            $data = '{"janus": "destroy", "transaction": "' . generateRandomString() . '","apisecret":"' . $password . '"}';
            $server = $server . '/' . $sessionId;
            $result = CallAPI('POST', $server, $data);
            echo($result);
        }
        break;
    case 'sendMessage' :
        {

            if ($jsep == '') {
                $request = '{"janus": "message", "body": ' . $message . ', "transaction": "' . generateRandomString() . '","apisecret":"' . $password . '"}';
            } else {
                $request = '{"janus": "message", "body": ' . $message . ', "transaction": "' . generateRandomString() . '","jsep":' . $jsep . ',"apisecret":"' . $password . '"}';
            }

            //	echo $request;
            $server = $server . '/' . $sessionId . '/' . $handleId;
            $result = CallAPI('POST', $server, $request);
            echo($result);
        }
        break;
    case 'sendTrickleCandidate' :
        {
            $request = '{"janus": "trickle", "candidate": ' . $candidate . ', "transaction": "' . generateRandomString() . '","apisecret":"' . $password . '"}';

            $server = $server . '/' . $sessionId . '/' . $handleId;
            $result = CallAPI('POST', $server, $request);
            echo($result);
        }
        break;
    case 'destroyHandle' :
        {
            $request = '{"janus":"detach","transaction":"' . generateRandomString() . '","apisecret":"' . $password . '"}';

            $server = $server . '/' . $sessionId . '/' . $handleId;
            $result = CallAPI('POST', $server, $request);
            echo($result);
        }
        break;

    default:
        {
            die('error');
        }
}

