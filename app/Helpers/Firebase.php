<?php

/**
 * Created by PhpStorm.
 * User: Erick Sumargo
 * Date: 2/18/2018
 * Time: 11:59 AM
 */

namespace App\Helpers;

class Firebase
{

    public static function pushNotification($data)
    {
        $payload = Array(
            'to' => $data['firebase'],
            'data' => array(
                'title' => $data['title'],
                'body' => $data['content'],
                'type' => $data['type']
            )
        );
        $key_firebase = $data['pro'] == 0 ?
            'AAAAkhA6rVA:APA91bF31opoDbDfm2gekxnI7q0qIdl4LGi7ExESNzOQbiwD7k-ELi0r04nVJzVSZg-DYkSgdazoXdtaog2FIF4ygMyvzGQi2uk7o3NDYE_8vIJrNbCnL7Jhfd-R8NOHJgEZNKu80-nc' :
            'AAAA7-r-Sts:APA91bHCfYIrkBnsReXIRgWUaWm-c6iLt901QsFLK0dKtI8lSyfI7sLjkbDUwqeGxA2K1o7Jj93C8_MFiK8aQN5Flp3mOLY6470-QbxKGZq-QmgdYVj3CWsmaJaeYi3clWrMJXJwZnLd';

        $url = 'https://fcm.googleapis.com/fcm/send';
        $headers = array(
            'Authorization: key=' . $key_firebase,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        if ($result == false) {
            $result = 'Curl failed: ' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
}