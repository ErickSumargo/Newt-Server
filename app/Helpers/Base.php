<?php
/**
 * Created by PhpStorm.
 * User: Erick Sumargo
 * Date: 2/18/2018
 * Time: 10:49 AM
 */

namespace App\Helpers;


trait Base
{
    protected $response;
    protected $token;

    public function __construct()
    {
        $this->response['success'] = true;
        $this->response['error'] = -1;
        $this->response['data'] = new \stdClass();

        $this->token = new Token();
    }

    public function result()
    {
        return $this->response;
    }

    public function getUser()
    {
        return $this->token->getUser();
    }

    public function getUserType()
    {
        return $this->token->getUserType();
    }

    public function getPhotoName($image, $content, $typePath)
    {
        $name = $content . '.' . $image->getClientOriginalExtension();
        $image->move(base_path() . '/public/media/image/' . $typePath . '/', $name);

        return $name;
    }

    public function saveImage($image, $name, $typePath)
    {
        $image->move(base_path() . '/public/media/image/' . $typePath . '/', $name);
    }

    public function deleteImage($name, $typePath)
    {
        $file = base_path() . '/public/media/image/' . $typePath . '/' . $name;
        if (!is_dir($file) && is_file($file)) {
            unlink($file);
        }
    }

    public function getDocumentName($file, $content, $typePath)
    {
        $name = time() . '-' . $content;
        $file->move(base_path() . '/public/media/document/' . $typePath . '/', $name);

        return $name;
    }

    public function getDialogId($code1, $code2, $lesson)
    {
        $parts_code1 = explode('_', $code1);
        $parts_code2 = explode('_', $code2);

        if ($parts_code1[0] == 'STU' && $parts_code2[0] == 'TEA') {
            $id = $code1 . '-' . $code2 . '-' . $lesson;
        } else {
            $id = $code2 . '-' . $code1 . '-' . $lesson;
        }
        return $id;
    }

    function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}