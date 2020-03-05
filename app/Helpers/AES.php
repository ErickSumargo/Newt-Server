<?php
/**
 * Created by PhpStorm.
 * User: Erick Sumargo
 * Date: 2/19/2018
 * Time: 10:00 AM
 */

namespace App\Helpers;


class AES
{
    private const OPENSSL_CIPHER_NAME = 'aes-256-cbc';

    private const KEY = '&AqV0EzqI2$(@Ukz2p0OjU3MlHaDb_R_';
    private const IV = '#aQkh)Z2HW9-Hx1e';

    static function encrypt($data)
    {
        $encodedEncryptedData = base64_encode(openssl_encrypt($data, AES::OPENSSL_CIPHER_NAME, AES::KEY, OPENSSL_RAW_DATA, AES::IV));
        $encodedIV = base64_encode(AES::IV);
        $encryptedPayload = $encodedEncryptedData . ':' . $encodedIV;

        return $encryptedPayload;
    }

    static function decrypt($data)
    {
        $parts = explode(':', $data);
        $encrypted = $parts[0];
        $iv = $parts[1];
        $decryptedData = openssl_decrypt(base64_decode($encrypted), AES::OPENSSL_CIPHER_NAME, AES::KEY, OPENSSL_RAW_DATA, base64_decode($iv));

        return $decryptedData;
    }
}