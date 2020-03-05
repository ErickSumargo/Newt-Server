<?php

namespace App\Http\Controllers;

use App\Helpers\Base;
use App\Models\ChallengeAttempt;
use App\Models\ChallengeLesson;
use App\Models\ChallengeQuestion;
use App\Models\Challenger;
use App\Models\Chat;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Tips;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BaseController extends Controller
{
    use Base;

    public function estConnection()
    {
        return $this->result();
    }

    public function updateApp()
    {
        $lessons = Lesson::all();

        $data = new \stdClass();
        $data->lessons = $lessons;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function loadFeatures()
    {
        $user = $this->getUser();
        $total = ChallengeLesson::count();
        for ($i = 0; $i < $total; $i++) {
            $unsolved[$i] = 0;
        }
        if ($this->getUserType() == 'student') {
            $challenger = Challenger::where('student_id', '=', $user->id)->first();
            if ($challenger != null) {
                $lessons = ChallengeLesson::all();
                foreach ($lessons as $lesson) {
                    $questions = ChallengeQuestion::where([
                        ['challenge_lesson_id', '=', $lesson->id],
                        ['status', '=', 1]
                    ])
                        ->get();
                    foreach ($questions as $question) {
                        $attempt = ChallengeAttempt::where([
                            ['challenger_id', '=', $challenger->id],
                            ['challenge_question_id', '=', $question->id]
                        ])
                            ->first();
                        if ($attempt == null) {
                            $unsolved[$lesson->id - 1] += 1;
                        } else if ($attempt->attempt > 0 && $attempt->status == 0) {
                            $unsolved[$lesson->id - 1] += 1;
                        }
                    }
                }
            }
        }
        $tips = Tips::where('active', '=', 1)->first();

        $data = new \stdClass();
        $data->version_code = 22;
        $data->unsolved = $unsolved;
        $data->tips = $tips;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function loadDialogs()
    {
        $user = $this->getUser();

        $cts = Chat::where([
            ['receiver_code', $user->code],
            ['sent', 0]
        ])
            ->get();
        foreach ($cts as $c) {
            $c->sent = 1;
            $c->save();
        }

        $dialogs = DB::select('select user_code, lesson_id as lesson from (
                                    (select receiver_code user_code, lesson_id from chats where sender_code = \'' . $user->code . '\'' . ') union
                                    (select sender_code user_code, lesson_id from chats where receiver_code = \'' . $user->code . '\'' . ')
                                ) chat');
        foreach ($dialogs as $dialog) {
            $parts = explode('_', $dialog->user_code);
            if ($parts[0] == 'STU') {
                $profile = Student::where('code', $dialog->user_code)->first();
                $profile->type = 'student';
            } else {
                $profile = Teacher::where('code', $dialog->user_code)->first();
                $profile->type = 'teacher';
            }
            $dialog->id = $this->getDialogId($user->code, $dialog->user_code, $dialog->lesson);

            $lesson = new \stdClass();
            $lesson->{'id'} = $dialog->lesson;
            $dialog->lesson = $lesson;

            $chats = Chat::where([
                ['sender_code', $user->code],
                ['receiver_code', $dialog->user_code],
                ['lesson_id', $dialog->lesson->id]
            ])
                ->orWhere([
                    ['sender_code', $dialog->user_code],
                    ['receiver_code', $user->code],
                    ['lesson_id', $dialog->lesson->id]
                ])
                ->orderBy('id', 'desc')
                ->get();
            foreach ($chats as $chat) {
                if ($chat->sent == 0) {
                    $chat->sent = 1;
                }
            }

            $dialog->user = $profile;
            $dialog->chats = array_reverse(json_decode($chats));
            $dialog->updated_at = $chats[0]->created_at->toDateTimeString();

            unset($dialog->user_code);
        }
        $lessons = Lesson::all();

        $data = new \stdClass();
        $data->dialogs = $dialogs;
        $data->lessons = $lessons;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function loadTeachers()
    {
        $teachers = [];
        $max_id = Teacher::max('id');
        for ($i = 1; $i <= $max_id; $i++) {
            $item = Teacher::find($i);

            $teacher = new \stdClass();
            $teacher->user = $item;
            $teacher->lessons = $item->lessons;
            $teacher->availables = $item->availables;
            $teacher->days_off = $item->days_off;

            unset($teacher->user->lessons);
            unset($teacher->user->availables);
            unset($teacher->user->days_off);
            $teachers[] = $teacher;
        }

        $data = new \stdClass();
        $data->teachers = $teachers;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function fetchTeachers()
    {
        $teachers = [];

        $teacher_list = Teacher::all();
        foreach ($teacher_list as $t) {
            $teacher = new \stdClass();
            $teacher->user = $t;
            $teacher->lessons = $t->lessons;
            $teacher->availables = $t->availables;
            $teacher->days_off = $t->days_off;

            unset($teacher->user->lessons);
            unset($teacher->user->availables);
            unset($teacher->user->days_off);
            $teachers[] = $teacher;
        }

        $data = new \stdClass();
        $data->teachers = $teachers;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function fetchProfiles(Request $req)
    {
        $codes = explode('-', $req['codes']);

        $users = [];
        foreach ($codes as $code) {
            $parts = explode('_', $code);
            if ($parts[0] == 'STU') {
                $profile = Student::where('code', $code)->first();
                $profile->type = 'student';
            } else {
                $profile = Teacher::where('code', $code)->first();
                $profile->type = 'teacher';
            }
            $users[] = $profile;
        }

        $data = new \stdClass();
        $data->users = $users;

        $this->response['data'] = $data;
        return $this->response;
    }

    public function loadPrivateTeachers(Request $req)
    {
        $private_promos = [
            'Khawatir guru tidak cocok?', 'Free mengajar dari guru selama 1 hari penuh!',
            'By Newt'
        ];
        $tuition_promos = [];

        $private_teachers = DB::table('private_teachers')
            ->join('private_teachings', 'private_teachers.id', '=', 'private_teachings.private_teacher_id')
            ->select('private_teachers.*', 'lesson')
            ->where([
                ['private_lesson_id', $req['private_lesson_id']],
                ['arrival', 1]
            ])
            ->get();

        $tuition_teachers = DB::table('private_teachers')
            ->join('private_teachings', 'private_teachers.id', '=', 'private_teachings.private_teacher_id')
            ->select('private_teachers.*', 'lesson')
            ->where([
                ['private_lesson_id', $req['private_lesson_id']],
                ['arrival', 0]
            ])
            ->get();
        foreach ($tuition_teachers as $t) {
            $t->address = '
            Untuk memperoleh \(x=3\), gunakan rumus:
            $$
                x_{1,2} = \frac{-b \pm \sqrt{b^2-4ac}}{2a}    
            $$';
        }

        $data = new \stdClass();
        $data->private_promos = $private_promos;
        $data->tuition_promos = $tuition_promos;
        $data->private_teachers = $private_teachers;
        $data->tuition_teachers = $tuition_teachers;

        $this->response['data'] = $data;
        return $this->result();
    }
}