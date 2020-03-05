<?php

namespace App\Http\Controllers;

use App\Helpers\AES;
use App\Helpers\Firebase;
use App\Helpers\SMS;
use App\Helpers\Token;
use App\Models\Bank;
use App\Models\Challenger;
use App\Models\Package;
use App\Models\PromoActivation;
use App\Models\PromoCode;
use App\Models\Provider;
use App\Models\Rating;
use App\Models\Registration;
use App\Models\ResetPassword;
use App\Models\ResetPhone;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Helpers\Base;

class UserController extends Controller
{
    use Base;

    public function registerPhone(Request $req)
    {
        $student = Student::where('phone', $req['phone'])->first();
        $teacher = Teacher::where('phone', $req['phone'])->first();

        $user = null;
        if ($student != null) {
            $user = $student;
        } else if ($teacher != null) {
            $user = $teacher;
        }

        if ($user == null) {
            $code = rand(0, 9999);
            $code .= '';
            while (strlen($code) < 4) {
                $code .= '0';
            }

            $reg = Registration::where('phone', $req['phone'])->first();
            if ($reg == null) {
                $reg_data = [
                    'phone' => $req['phone'],
                    'code' => $code
                ];
                Registration::store($reg_data);

                SMS::send($req['phone'], $code);
            } else {
                if ($reg->status == 1) {
                    $data = new \stdClass();
                    $data->skipped = true;
                    $this->response['data'] = $data;
                } else {
                    $reg->code = $code;
                    $reg->save();

                    SMS::send($req['phone'], $code);
                }
            }
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }

    public function resendCode(Request $req)
    {
        $code = rand(0, 9999);
        $code .= '';
        while (strlen($code) < 4) {
            $code .= '0';
        }

        if ($req['reg_type'] == 0) {
            $reg = Registration::where('phone', $req['phone'])->first();
            $reg->code = $code;
            $reg->save();
        } else if ($req['reg_type'] == 1) {
            $res = ResetPassword::where([
                ['phone', $req['phone']],
                ['status', 0]
            ])
                ->first();

            $res->code = $code;
            $res->save();
        } else {
            $res = ResetPhone::where([
                ['phone', $req['phone']],
                ['status', 0]
            ])
                ->first();

            $res->code = $code;
            $res->save();
        }
        SMS::send($req['phone'], $code);

        return $this->result();
    }

    public function verifyCode(Request $req)
    {
        $reg = null;
        if ($req['reg_type'] == 0) {
            $reg = Registration::where([
                ['phone', $req['phone']],
                ['code', $req['code']],
            ])
                ->first();
        } else if ($req['reg_type'] == 1) {
            $reg = ResetPassword::where([
                ['phone', $req['phone']],
                ['code', $req['code']],
                ['status', 0]
            ])
                ->first();
        }

        if ($reg != null) {
            $reg->status = 1;
            $reg->save();
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }

    public function registerStudent(Request $req)
    {
        $reg = Registration::where([
            ['phone', $req['phone']],
            ['status', 1]
        ])
            ->first();

        if ($reg != null) {
            $student = Student::where('phone', $req['phone'])->first();
            if ($student == null) {
                $promo_code = PromoCode::where('code', 'SMARTWITH+NEWT')->first();
                if ($req['promo_code'] != null && $req['promo_code'] == $promo_code->code && $promo_code->active == 0) {
                    $this->response['success'] = false;
                    $this->response['error'] = 3;
                } else if ($req['promo_code'] != null && $req['promo_code'] != $promo_code->code) {
                    $this->response['success'] = false;
                    $this->response['error'] = 2;
                } else {
                    $device = Student::where('device', $req['device'])->first();
                    if ($device == null) {
                        if ($req['promo_code'] == null) {
                            $subscription = Carbon::now()->addDays(7)->__toString();
                        } else if ($req['promo_code'] == $promo_code->code) {
                            if ($promo_code->active == 1) {
                                $subscription = Carbon::now()->addDays(14)->__toString();
                            } else {
                                $subscription = Carbon::now()->addDays(7)->__toString();
                            }
                        }
                        $device_registered = false;
                    } else {
                        $subscription = Carbon::now()->__toString();
                        $device_registered = true;
                    }

                    $student_data = [
                        'name' => $req['name'],
                        'password' => Hash::make(AES::decrypt($req['password'])),
                        'phone' => $req['phone'],
                        'school' => $req['school'],
                        'device' => $req['device'],
                        'pro' => $req['pro'],
                        'subscription' => $subscription,
                        'firebase' => $req['firebase']
                    ];
                    Student::store($student_data);

                    $student = Student::where('phone', $req['phone'])->first();
                    $student->code = 'STU_' . $student->id;
                    $student->save();

                    if ($req['promo_code'] == $promo_code->code && $promo_code->active == 1 || $device_registered) {
                        $promo_activation_data = [
                            'student_id' => $student->id,
                            'promo_code_id' => $promo_code->id
                        ];
                        PromoActivation::store($promo_activation_data);
                    }

                    $data = new \stdClass();
                    $data->user = $student;
                    $data->user->type = 'student';
                    $data->user->challenger = false;
                    $data->token = $this->token->getToken($student);
                    $data->device_registered = $device_registered;

                    $this->response['data'] = $data;

                    if (!$device_registered) {
                        Firebase::pushNotification([
                            'title' => 'Welcome to Newt!',
                            'content' => 'Anda memiliki kesempatan trial menikmati layanan tanya-jawab terbaik dari kami s/d ' . Carbon::parse($student->subscription)->format('d F Y'),
                            'type' => 0,
                            'firebase' => $student->firebase,
                            'pro' => $student->pro,
                        ]);
                    }
                }
            } else {
                $this->response['success'] = false;
                $this->response['error'] = 1;
            }
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }

    public function login(Request $req)
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
            if (Hash::check(AES::decrypt($req['password']), $user->password)) {
                $user->pro = $req['pro'];
                $user->firebase = $req['firebase'];

                $parts = explode('_', $user->code);
                if ($parts[0] == 'TEA') {
                    $user->device = $req['device'];
                }
                $user->save();

                $data = new \stdClass();
                $data->user = $user;
                if ($parts[0] == 'STU') {
                    $challenger = Challenger::where('student_id', '=', $user->id)->first();
                    $user->challenger = $challenger != null;
                }
                $data->user->type = $parts[0] == 'STU' ? 'student' : 'teacher';
                $data->token = $this->token->getToken($user);

                $this->response['data'] = $data;
            } else {
                $this->response['success'] = false;
                $this->response['error'] = 1;
            }
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }

    public function registerChallenger()
    {
        $student = $this->getUser();

        $challenger = Challenger::where('student_id', '=', $student->id)->first();
        if ($challenger == null) {
            $challenger_data = [
                'student_id' => $student->id
            ];
            Challenger::store($challenger_data);
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }

    public function reregisterPhone(Request $req)
    {
        $student = Student::where('phone', $req['phone'])->first();
        $teacher = Teacher::where('phone', $req['phone'])->first();

        $user = null;
        if ($student != null) {
            $user = $student;
        } else if ($teacher != null) {
            $user = $teacher;
        }

        if ($user == null) {
            $code = rand(0, 9999);
            $code .= '';
            while (strlen($code) < 4) {
                $code .= '0';
            }

            $res = ResetPhone::where([
                ['phone', $req['phone']],
                ['status', 0]
            ])
                ->first();
            if ($res == null) {
                $res_data = [
                    'phone' => $req['phone'],
                    'code' => $code
                ];
                ResetPhone::store($res_data);
            } else {
                $res->code = $code;
                $res->save();
            }
            SMS::send($req['phone'], $code);
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }

    public function resetPhone(Request $req)
    {
        $res = ResetPhone::where([
            ['phone', $req['phone']],
            ['code', $req['code']],
            ['status', 0]
        ])
            ->first();

        if ($res != null) {
            $res->status = 1;
            $res->save();

            $user = $this->getUser();
            $user->phone = $req['phone'];
            $user->save();

            if ($this->getUserType() == 'student') {
                $challenger = Challenger::where('student_id', '=', $user->id)->first();
                $user->challenger = $challenger != null;
            }
            $data = new \stdClass();
            $data->user = $user;
            $data->user->type = $this->getUserType();
            $data->token = $this->token->getToken($user);

            $this->response['data'] = $data;
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }

    public function applyPromoCode(Request $req)
    {
        $promo_code = PromoCode::where('code', $req['promo_code'])->first();
        if ($promo_code == null) {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        } else if ($promo_code->active == 0) {
            $this->response['success'] = false;
            $this->response['error'] = 1;
        } else {
            $user = $this->getUser();
            $activation = PromoActivation::where([
                ['student_id', $user->id],
                ['promo_code_id', $promo_code->id]
            ])
                ->first();
            if ($activation == null) {
                $promo_activation_data = [
                    'student_id' => $user->id,
                    'promo_code_id' => $promo_code->id
                ];
                PromoActivation::store($promo_activation_data);

                if ($promo_code->code == 'SMARTWITH+NEWT') {
                    if (Carbon::parse($user->subscription)->gt(Carbon::now())) {
                        $user->subscription = Carbon::parse($user->subscription)->addDays(7)->__toString();
                    } else {
                        $user->subscription = Carbon::now()->addDays(7)->__toString();
                    }
                    $user->save();

                    Firebase::pushNotification([
                        'title' => 'Ekstra Masa Langganan',
                        'content' => $user->subscription,
                        'type' => 1,
                        'firebase' => $user->firebase,
                        'pro' => $user->pro,
                    ]);
                }
            } else {
                $this->response['success'] = false;
                $this->response['error'] = 2;
            }
        }
        return $this->result();
    }

    public function updateProfile(Request $req)
    {
        $user = $this->getUser();
        $user->name = $req['name'];
        $user->school = $req['school'];

        if (isset($req['photo_changed'])) {
            $this->deleteImage($user->photo, $this->getUserType());
            if ($req->file('image') != null) {
                $content = $user->code . '-' . time();
                $user->photo = $this->getPhotoName($req->file('image'), $content, $this->getUserType());
            } else {
                $user->photo = '';
            }
        }
        $user->save();

        $data = new \stdClass();
        $data->user = $user;
        if (explode('_', $user->code)[0] == 'STU') {
            $challenger = Challenger::where('student_id', '=', $user->id)->first();
            $data->user->challenger = $challenger != null;
        }
        $data->user->type = $this->getUserType();
        $data->token = $this->token->getToken($user);

        $this->response['data'] = $data;
        return $this->response;
    }

    public function getRating(Request $req)
    {
        $student = $this->getUser();
        $rating = Rating::where([
            ['student_id', $student->id],
            ['teacher_id', $req['teacher_id']],
            ['lesson_id', $req['lesson_id']],
        ])
            ->first();

        $data = new \stdClass();
        $data->rating = isset($rating) ? $rating->rating : 0;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function setRating(Request $req)
    {
        $student = $this->getUser();
        $rating = Rating::where([
            ['student_id', $student->id],
            ['teacher_id', $req['teacher_id']],
            ['lesson_id', $req['lesson_id']]
        ])
            ->first();

        if ($rating != null) {
            $rating->rating = $req['rating'];
            $rating->save();
        } else {
            $rating_data = [
                'student_id' => $student->id,
                'teacher_id' => $req['teacher_id'],
                'rating' => $req['rating'],
                'lesson_id' => $req['lesson_id']
            ];
            Rating::store($rating_data);
        }
        return $this->result();
    }

    public function getAvgRating(Request $req)
    {
        $teacher_id = $req['teacher_id'];

        $q = DB::select('select avg(rating) as avg_rating, count(*) as reviewers from ratings
                                    where teacher_id = ' . $teacher_id . '')[0];

        $data = new \stdClass();
        $data->avg_rating = isset($q->avg_rating) ? $q->avg_rating : 0;
        $data->reviewers = $q->reviewers;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function checkTransaction(Request $req)
    {
        $user = $this->getUser();
        $transaction = Transaction::where([
            ['student_id', $user->id],
            ['code', $req['code']],
            ['status', 0]
        ])
            ->first();

        $data = new \stdClass();
        if ($transaction != null) {
            if (Carbon::parse($user->subscription)->gt(Carbon::now())) {
                $user->subscription = Carbon::parse($user->subscription)->addDays(30)->__toString();
            } else {
                $user->subscription = Carbon::now()->addDays(30)->__toString();
            }
            $user->save();

            $transaction->status = 1;
            $transaction->save();

            $existed = true;
            $data->user = $user;
            $data->token = $this->token->getToken($user);
        } else {
            $existed = false;
        }
        $data->existed = $existed;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function getTransactionDatas()
    {
        $packages = Package::where('active', 1)->get();
        $banks = Bank::where('active', 1)->get();
        $providers = Provider::where('active', 1)->get();

        $data = new \stdClass();
        $data->packages = $packages;
        $data->banks = $banks;
        $data->providers = $providers;

        $this->response['data'] = $data;
        return $this->result();
    }
}