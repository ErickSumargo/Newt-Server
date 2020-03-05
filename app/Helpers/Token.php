<?php
/**
 * Created by PhpStorm.
 * User: Erick Sumargo
 * Date: 2/18/2018
 * Time: 11:05 AM
 */

namespace App\Helpers;

use App\Helpers\JWT;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Teacher;

class Token
{
    protected $secret, $alg;

    protected static $user;
    protected static $user_type;

    function __construct()
    {
        $this->secret = '!qewKOzueRPy^_@zZ@d@gd*Zm*lIn*kz';
        $this->alg = 'HS256';
    }

    public function validate($user_type, $token)
    {
        $data = JWT::decode($token, $this->secret, array($this->alg));

        self::$user_type = $user_type;
        switch ($user_type) {
            case 'student':
                self::$user = Student::where([
                    ['id', '=', $data->id],
                    ['code', '=', $data->code],
                    ['phone', '=', $data->phone],
                    ['active', '=', 1]
                ])
                    ->first();
                break;
            case 'teacher':
                self::$user = Teacher::where([
                    ['id', '=', $data->id],
                    ['code', '=', $data->code],
                    ['phone', '=', $data->phone],
                    ['active', '=', 1]
                ])
                    ->first();
                break;
            case 'admin':
                self::$user = Admin::where([
                    ['id', '=', $data->id],
                    ['code', '=', $data->code],
                    ['phone', '=', $data->phone]
                ])
                    ->first();
                break;
            case 'guest':
                self::$user = $data;
                break;
            default:
                break;
        }

        if (self::$user == null) {
            return false;
        }
        return true;
    }

    public function getToken($obj)
    {
        return JWT::encode($obj, $this->secret, $this->alg);
    }

    public function getUser()
    {
        return self::$user;
    }

    public function getUserType()
    {
        return self::$user_type;
    }
}