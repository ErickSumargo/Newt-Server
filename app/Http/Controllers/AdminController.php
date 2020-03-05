<?php

namespace App\Http\Controllers;

use App\Helpers\AES;
use App\Helpers\Base;
use App\Helpers\Firebase;
use App\Helpers\SMS;
use App\Models\Admin;
use App\Models\Available;
use App\Models\ChallengeAttempt;
use App\Models\ChallengeQuestion;
use App\Models\ChallengeResetRank;
use App\Models\ChallengeSolution;
use App\Models\DaysOff;
use App\Models\Lesson;
use App\Models\Package;
use App\Models\PrivateLesson;
use App\Models\PrivateTeacher;
use App\Models\PrivateTeaching;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherLesson;
use App\Models\Tips;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    use Base;

    public function login(Request $req)
    {
        $admin = Admin::where('phone', $req['phone'])->first();
        if ($admin != null) {
            if (Hash::check(AES::decrypt($req['password']), $admin->password)) {
                $data = new \stdClass();
                $data->user = $admin;
                $data->user->type = 'admin';
                $data->token = $this->token->getToken($admin);

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

    public function loadLessons()
    {
        $lessons = Lesson::all();

        $data = new \stdClass();
        $data->lessons = $lessons;
        $this->response['data'] = $data;

        return $this->result();
    }

    public function registerTeacher(Request $req)
    {
        $student = Student::where('phone', $req['phone'])->first();
        $teacher = Teacher::where('phone', $req['phone'])->first();

        $user = null;
        if ($student != null) {
            $user = $student;
        } else if ($teacher != null) {
            $user = $teacher;
        }
        $teacher = $user;

        if ($teacher == null) {
            $password = $this->random_str(6);
            SMS::send($req['phone'], $password, 1);

            $teacher_data = [
                'phone' => $req['phone'],
                'name' => $req['name'],
                'password' => Hash::make($password),
            ];
            Teacher::store($teacher_data);

            $teacher = Teacher::where('phone', $req['phone'])->first();
            $teacher->code = 'TEA_' . $teacher->id;
            $teacher->save();

            foreach ($req['lessons[]'] as $lesson_id) {
                $teacher_lesson_data = [
                    'teacher_id' => $teacher->id,
                    'lesson_id' => $lesson_id
                ];
                TeacherLesson::store($teacher_lesson_data);
            }

            for ($i = 0; $i < count($req['starts[]']); $i++) {
                $start = $req['starts[]'][$i];
                $end = $req['ends[]'][$i];

                $available_data = [
                    'teacher_id' => $teacher->id,
                    'start' => $start,
                    'end' => $end,
                ];
                Available::store($available_data);
            }

            for ($i = 0; $i < count($req['days_off[]']); $i++) {
                $day = $req['days_off[]'][$i];

                $days_off_data = [
                    'teacher_id' => $teacher->id,
                    'day' => $day
                ];
                DaysOff::store($days_off_data);
            }
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }

    public function loadPrivateLessons()
    {
        $lessons = PrivateLesson::all();

        $data = new \stdClass();
        $data->lessons = $lessons;
        $this->response['data'] = $data;

        return $this->result();
    }

    public function registerPrivateTeacher(Request $req)
    {
        $teacher = PrivateTeacher::where('phone', $req['phone'])->first();
        if ($teacher == null) {
            $private_teacher_data = [
                'name' => $req['name'],
                'phone' => $req['phone'],
                'address' => $req['address'],
                'tuition' => $req['tuition'],
                'age' => $req['age'],
                'experience' => $req['experience'],
                'education' => $req['education']
            ];
            $private_teacher = PrivateTeacher::store($private_teacher_data);

            if ($req->file('image') != null) {
                $content = $private_teacher->id . '-' . time();
                $private_teacher->photo = $this->getPhotoName($req->file('image'), $content, 'private_teacher');

                $private_teacher->save();

                if ($req['private_checked'] == 'true') {
                    $lesson_ids = explode('%', $req['private_ids']);
                    $lessons = explode('_', $req['private_lessons']);

                    for ($i = 0; $i < count($lesson_ids); $i++) {
                        $private_teaching_data = [
                            'private_teacher_id' => $private_teacher->id,
                            'private_lesson_id' => $lesson_ids[$i],
                            'lesson' => $lessons[$i],
                            'arrival' => 1
                        ];
                        PrivateTeaching::store($private_teaching_data);
                    }
                }

                if ($req['tuition_checked'] == 'true') {
                    $lesson_ids = explode('%', $req['tuition_ids']);
                    $lessons = explode('_', $req['tuition_lessons']);

                    for ($i = 0; $i < count($lesson_ids); $i++) {
                        $private_teaching_data = [
                            'private_teacher_id' => $private_teacher->id,
                            'private_lesson_id' => $lesson_ids[$i],
                            'lesson' => $lessons[$i],
                            'arrival' => 0
                        ];
                        PrivateTeaching::store($private_teaching_data);
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

    public function sendNotification(Request $req)
    {
        if ($req['tar_checked']) {
            $type = explode('_', $req['code'])[0];
            if ($type == 'STU') {
                $user = Student::where('code', $req['code'])->first();
            } else {
                $user = Teacher::where('code', $req['code'])->first();
            }

            if ($user != null) {
                Firebase::pushNotification([
                    'title' => $req['title'],
                    'content' => $req['content'],
                    'type' => 0,
                    'firebase' => $user->firebase,
                    'pro' => $user->pro,
                ]);
            } else {
                $this->response['success'] = false;
                $this->response['error'] = 1;
            }
        } else {
            if ($req['stu_checked']) {
                $students = Student::all();
                foreach ($students as $student) {
                    Firebase::pushNotification([
                        'title' => $req['title'],
                        'content' => $req['content'],
                        'type' => 0,
                        'firebase' => $student->firebase,
                        'pro' => $student->pro,
                    ]);
                }
            }
            if ($req['tea_checked']) {
                $teachers = Teacher::all();
                foreach ($teachers as $teacher) {
                    Firebase::pushNotification([
                        'title' => $req['title'],
                        'content' => $req['content'],
                        'type' => 0,
                        'firebase' => $teacher->firebase,
                        'pro' => $teacher->pro,
                    ]);
                }
            }
        }
        return $this->result();
    }

    public function confirmPayment(Request $req)
    {
        $student = Student::where('phone', $req['phone'])->first();
        if ($student != null) {
            $package = Package::find($req['package']);
            if ($package != null) {
                if (Carbon::parse($student->subscription)->gt(Carbon::now())) {
                    $student->subscription = Carbon::parse($student->subscription)->addDays($package->days)->__toString();
                } else {
                    $student->subscription = Carbon::now()->addDays($package->days)->__toString();
                }
                $student->save();

                $transaction_data = [
                    'student_id' => $student->id,
                    'package_id' => $package->id
                ];
                Transaction::store($transaction_data);

                Firebase::pushNotification([
                    'title' => 'Pembayaran Diterima!',
                    'content' => $student->subscription,
                    'type' => 1,
                    'firebase' => $student->firebase,
                    'pro' => $student->pro,
                ]);
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

    public function submitTips(Request $req)
    {
        $tips_data = [
            'content' => $req['content']
        ];
        Tips::store($tips_data);

        return $this->result();
    }

    public function submitChallenge(Request $req)
    {
        $solution_data = [
            'content' => $req['solution_content']
        ];
        $solution = ChallengeSolution::store($solution_data);

        $question_data = [
            'teacher_id' => $req['teacher_id'],
            'challenge_lesson_id' => $req['challenge_lesson_id'],
            'challenge_solution_id' => $solution->id,
            'content' => $req['question_content'],
            'answer' => $req['answer'],
            'material' => $req['material'],
            'level' => $req['level'],
            'point' => $req['point'],
            'attempt' => $req['attempt']
        ];
        ChallengeQuestion::store($question_data);

        return $this->result();
    }

    public function activateChallenge()
    {
        $delayed_questions = ChallengeQuestion::where([
            ['status', '=', 2],
            ['active', '=', 1]
        ])
            ->get();
        if (count($delayed_questions) > 0) {
            $running_questions = ChallengeQuestion::where([
                ['status', '=', 1],
                ['active', '=', 1]
            ])
                ->get();
            foreach ($running_questions as $question) {
                $question->status = 0;
                $question->save();
            }

            foreach ($delayed_questions as $question) {
                $question->status = 1;
                $question->created_at = Carbon::now()->__toString();
                $question->save();
            }
        } else {
            $this->response['success'] = false;
            $this->response['error'] = 0;
        }
        return $this->result();
    }

    public function loadQueuesChallenge()
    {
        $questions = ChallengeQuestion::where([
            ['status', '=', 2],
            ['active', '=', 1]
        ])
            ->get();

        $challenges = [];
        foreach ($questions as $question) {
            $question->teacher;

            $challenge = new \stdClass();
            $challenge->question = $question;

            unset($question->teacher_id);
            unset($question->challenge_solution_id);

            $challenges[] = $challenge;
        }

        $data = new \stdClass();
        $data->challenges = $challenges;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function resetRank()
    {
        $solves = ChallengeAttempt::where('active', '=', 1)->get();
        foreach ($solves as $s) {
            $s->active = 0;
            $s->save();
        }

        $reset_rank_data = [
            'start' => Carbon::now()->__toString(),
            'end' => Carbon::now()->addDays(60)->__toString()
        ];
        ChallengeResetRank::store($reset_rank_data);

        return $this->result();
    }

    public function loadChallenges(Request $req)
    {
        if ($req['last_id'] == 0) {
            $questions = ChallengeQuestion::orderBy('id', 'desc')
                ->take(10)
                ->get();
        } else {
            $questions = ChallengeQuestion::where('id', '<', $req['last_id'])
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();
        }

        $challenges = [];
        foreach ($questions as $question) {
            $question->teacher;

            $challenge = new \stdClass();
            $challenge->question = $question;

            unset($question->teacher_id);
            unset($question->challenge_solution_id);

            $challenges[] = $challenge;
        }

        $data = new \stdClass();
        $data->challenges = $challenges;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function loadChallengeDetail(Request $req)
    {
        $question = ChallengeQuestion::find($req['id']);
        $question->teacher;

        $challenge = new \stdClass();
        $challenge->question = $question;
        $challenge->solution = $question->solution;

        unset($question->teacher_id);
        unset($question->challenge_solution_id);

        $data = new \stdClass();
        $data->challenge = $challenge;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function editChallenge(Request $req)
    {
        $question = ChallengeQuestion::find($req['challenge_id']);
        $solution = $question->solution;

        $question->teacher_id = $req['teacher_id'];
        $question->challenge_lesson_id = $req['challenge_lesson_id'];
        $question->content = $req['question_content'];
        $question->answer = $req['answer'];
        $question->material = $req['material'];
        $question->level = $req['level'];
        $question->point = $req['point'];
        $question->attempt = $req['attempt'];
        $question->save();

        $solution->content = $req['solution_content'];
        $solution->save();

        return $this->result();
    }

    public function loadTips(Request $req)
    {
        if ($req['last_id'] == 0) {
            $tips = Tips::orderBy('id', 'desc')
                ->take(10)
                ->get();
        } else {
            $tips = Tips::where('id', '<', $req['last_id'])
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();
        }

        $data = new \stdClass();
        $data->tips_list = $tips;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function loadTipsDetail(Request $req)
    {
        $tips = Tips::find($req['id']);

        $data = new \stdClass();
        $data->tips = $tips;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function editTips(Request $req)
    {
        $tips = Tips::find($req['tips_id']);
        $tips->content = $req['content'];
        $tips->save();

        return $this->result();
    }

    public function activateTips(Request $req)
    {
        $tips = Tips::where('active', '=', 1)->get();
        foreach ($tips as $t) {
            $t->active = 0;
            $t->save();
        }

        $tips = Tips::find($req['id']);
        $tips->active = 1;
        $tips->save();

        return $this->result();
    }
}