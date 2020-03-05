<?php
/**
 * Created by PhpStorm.
 * User: Erick Sumargo
 * Date: 2/18/2018
 * Time: 11:59 AM
 */

namespace App\Helpers;

use Twilio\Rest\Client;

class SMS
{
    public static function send($phone, $code, $type = 0)
    {
        $sid = 'AC10dfab1c333fdc7279dba74f41cdfc4b';
        $token = 'd043710b676ac916787b6661973ae8b3';
        $client = new Client($sid, $token);

        $phone = '+62' . ltrim($phone, '0');
        $client->messages->create(
            $phone, [
                'from' => '+16172997317',
                'body' => $type == 0 ? 'Welcome to Newt. Kode verifikasi: ' . $code : 'Welcome to Newt. Password login: ' . $code
            ]
        );
    }
}