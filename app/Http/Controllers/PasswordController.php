<?php

namespace App\Http\Controllers;

use App\Helpers\AES;
use App\Helpers\Base;
use App\Helpers\SMS;
use App\Models\ResetPassword;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    use Base;

    public function validatePhone(Request $req)
    {
        $student = Student::where('phone', $req['phone'])->first();
        $teacher = Teacher::where('phone', $req['phone'])->first();

        $user = null;
        if ($student != null) {
            $user = $student;
        } else if ($teacher != null) {
            $user = $teacher;
        }

        if ($user != null) {
            $code = rand(0, 9999);
            $code .= '';
            while (strlen($code) < 4) {
                $code .= '0';
            }

            $res = ResetPassword::where([
                ['phone', $req['phone']],
                ['status', 0]
            ])
                ->first();

            if ($res == null) {
                $res = ResetPassword::where([
                    ['phone', $req['phone']],
                    ['status', 1]
                ])
                    ->first();

                if ($res == null) {
                    $res_data = [
                        'user_code' => $user->code,
                        'phone' => $req['phone'],
                        'code' => $code
                    ];
                    ResetPassword::store($res_data);

                    SMS::send($req['phone'], $code);
                } else {
                    $data = new \stdClass();
                    $data->skipped = true;
                    $this->response['data'] = $data;
                }
            } else {
                $res->code = $code;
                $res->save();

                SMS::send($req['phone'], $code);
            }
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }

    public function resetPassword(Request $req)
    {
        $res = ResetPassword::where([
            ['phone', $req['phone']],
            ['status', 1]
        ])
            ->first();

        if ($res != null) {
            $res->status = 2;
            $res->save();

            $parts = explode('_', $res->user_code);
            if ($parts[0] == 'STU') {
                $student = Student::find(intval($parts[1]));
                $student->password = Hash::make(AES::decrypt($req['password']));
                $student->save();
            } else if ($parts[0] == 'TEA') {
                $teacher = Teacher::find(intval($parts[1]));
                $teacher->password = Hash::make(AES::decrypt($req['password']));
                $teacher->save();
            }
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }
}