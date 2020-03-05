<?php

namespace App\Http\Controllers;

use App\Helpers\Base;
use App\Models\ChallengeAttempt;
use App\Models\ChallengeQuestion;
use App\Models\Challenger;
use App\Models\ChallengeResetRank;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    use Base;

    public function loadQuestions(Request $req)
    {
        if ($this->getUserType() == 'student') {
            $user = $this->getUser();
            $challenger = Challenger::where([
                ['student_id', '=', $user->id]
            ])
                ->first();

            $solved = $challenger->attempts->where('status', '=', 1);
            $point = 0;
            foreach ($solved as $s) {
                $point += $s->question->point;
            }
        }

        $easy_questions = [];
        $medium_questions = [];
        $hard_questions = [];

        $questions = ChallengeQuestion::where([
            ['challenge_lesson_id', '=', $req['lesson_id']],
            ['status', '=', 1],
            ['active', '=', 1]
        ])
            ->orderBy('point', 'asc')
            ->get();
        foreach ($questions as $question) {
            $question->teacher;

            $challenge = new \stdClass();
            $challenge->question = $question;
            $challenge->solution = $question->solution;

            if ($this->getUserType() == 'student') {
                $challenge_attempt = ChallengeAttempt::where([
                    ['challenger_id', '=', $challenger->id],
                    ['challenge_question_id', '=', $question->id]
                ])
                    ->first();
                if ($challenge_attempt == null) {
                    $challenge->attempt = -1;
                    $challenge->status = 0;
                } else {
                    $challenge->attempt = $challenge_attempt->attempt;
                    $challenge->status = $challenge_attempt->status;
                }
            } else {
                $challenge->attempt = -1;
                $challenge->status = 0;
            }

            unset($question->teacher_id);
            unset($question->challenge_solution_id);

            if ($req['lesson_id'] == 1) {
                $hard_questions[] = $challenge;
            } else {
                if ($question->level == 1) {
                    $easy_questions[] = $challenge;
                } else if ($question->level == 2) {
                    $medium_questions[] = $challenge;
                } else if ($question->level == 3) {
                    $hard_questions[] = $challenge;
                }
            }
        }

        $data = new \stdClass();
        if ($this->getUserType() == 'student') {
            $data->solved = $solved->count();
            $data->points = $point;
        } else {
            $data->solved = 0;
            $data->points = 0;
        }
        $data->easy_questions = $easy_questions;
        $data->medium_questions = $medium_questions;
        $data->hard_questions = $hard_questions;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function loadQuestionDetail(Request $req)
    {
        $user = $this->getUser();
        $question = ChallengeQuestion::find($req['id']);
        $question->teacher;

        $challenge = new \stdClass();
        $challenge->question = $question;
        $challenge->solution = $question->solution;

        $solved = ChallengeAttempt::where([
            ['challenge_question_id', '=', $req['id']],
            ['status', '=', 1]
        ])
            ->count();

        unset($question->teacher_id);
        unset($question->challenge_solution_id);

        $data = new \stdClass();
        if ($this->getUserType() == 'student') {
            $challenger = Challenger::where([
                ['student_id', '=', $user->id]
            ])
                ->first();

            $challenge_attempt = ChallengeAttempt::where([
                ['challenger_id', '=', $challenger->id],
                ['challenge_question_id', '=', $question->id]
            ])
                ->first();
            if ($challenge_attempt == null) {
                $challenge->attempt = -1;
                $challenge->status = 0;
            } else {
                $challenge->attempt = $challenge_attempt->attempt;
                $challenge->status = $challenge_attempt->status;
            }

            $infos = [];
            if ($question->status == 1) {
                if ($challenge->status == 0 && $challenge->attempt != 0) {
                    $infos[0] = 'Periode challenge s/d ' . Carbon::parse($question->created_at)->addDays(14)->format('d F\'y');
                    if ($solved > 0) {
                        $infos[1] = $solved . ' penantang telah menyelesaikan soal ini';
                        $infos[2] = 'Be the Next One!';
                    } else {
                        $infos[1] = 'Belum ada penantang yang menyelesaikan soal ini';
                        $infos[2] = 'Be the First!';
                    }
                    $infos[3] = 'May the Newt be with You :)';
                } else {
                    if ($challenge->status == 1) {
                        $infos[0] = 'Jawaban Benar';
                    } else {
                        $infos[0] = 'Jawaban Salah';
                    }
                    $infos[1] = 'Solusi lengkap akan ditampilkan pada periode berikutnya';
                }
            }
            $data->infos = $infos;
        } else {
            $data->infos = [$solved . ' penantang telah menyelesaikan soal ini'];
        }
        $data->challenge = $challenge;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function submitAnswer(Request $req)
    {
        $challenger = Challenger::where('student_id', '=', $this->getUser()->id)->first();
        if ($challenger->active == 1) {
            $question = ChallengeQuestion::find($req['question_id']);
            if ($question->active == 1) {
                if ($question->status == 1) {
                    if ($question->answer == $req['answer']) {
                        $challenge_attempt = ChallengeAttempt::where([
                            ['challenger_id', '=', $challenger->id],
                            ['challenge_question_id', '=', $req['question_id']]
                        ])
                            ->first();
                        if ($challenge_attempt == null) {
                            $attempt_data = [
                                'challenger_id' => $challenger->id,
                                'challenge_question_id' => $question->id,
                                'attempt' => $question->attempt,
                                'status' => 1
                            ];
                            $challenge_attempt = ChallengeAttempt::store($attempt_data);
                        } else {
                            $challenge_attempt->status = 1;
                            $challenge_attempt->save();
                        }

                        $data = new \stdClass();
                        $data->challenge_attempt = $challenge_attempt;
                        $this->response['data'] = $data;
                    } else {
                        $challenge_attempt = ChallengeAttempt::where([
                            ['challenger_id', '=', $challenger->id],
                            ['challenge_question_id', '=', $req['question_id']]
                        ])
                            ->first();
                        if ($challenge_attempt == null) {
                            $attempt_data = [
                                'challenger_id' => $challenger->id,
                                'challenge_question_id' => $question->id,
                                'attempt' => $question->attempt - 1,
                                'status' => 0
                            ];
                            $challenge_attempt = ChallengeAttempt::store($attempt_data);
                        } else {
                            $challenge_attempt->attempt = $challenge_attempt->attempt - 1;
                            $challenge_attempt->save();
                        }

                        $data = new \stdClass();
                        $data->challenge_attempt = $challenge_attempt;
                        $this->response['data'] = $data;
                    }
                } else {
                    $this->response['success'] = false;
                    $this->response['error'] = 2;
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

    public function loadRanks(Request $req)
    {
        $reset_rank = ChallengeResetRank::all()->last();
        $start = Carbon::parse($reset_rank->start);
        $end = Carbon::parse($reset_rank->end);
        if ($start->year == $end->year) {
            if ($start->month == $end->month) {
                $periode = 'Periode: ' . $start->format('d') . ' - ' . $end->format('d F Y');
            } else {
                $periode = 'Periode: ' . $start->format('d M') . ' - ' . $end->format('d M\'y');
            }
        } else {
            $periode = 'Periode: ' . $start->format('d M\'y') . ' - ' . $end->format('d M \'y');
        }

        $challengers = Challenger::with('attempts', 'attempts.question')->get();
        $c = new \Illuminate\Database\Eloquent\Collection;
        foreach ($challengers as $challenger) {
            $points = 0;
            $solved = 0;
            foreach ($challenger->attempts as $attempt) {
                if ($attempt->question->challenge_lesson_id == $req['lesson_id']) {
                    if ($attempt->status == 1 && $attempt->active == 1) {
                        $points += $attempt->question->point;
                        $solved += 1;
                    }
                }
            }
            $challenger->student;
            $challenger->points = $points;
            $challenger->solved = $solved;

            unset($challenger->attempts);
            if ($points > 0 && $solved > 0) {
                $c->add($challenger);
            }
        }
        if ($this->getUserType() == 'student') {
            $challenger = Challenger::where('student_id', '=', $this->getUser()->id)->first();

            $challengers = $c->sortByDesc('points')->values()->all();
            $rank = array_search($challenger->id, array_column($challengers, 'id'));
            if ($rank === false) {
                $rank = -1;
            } else {
                $rank += 1;
            }
            $challengers = array_slice($challengers, 0, 100);
        } else {
            $rank = -1;
            $challengers = array_slice($c->sortByDesc('points')->values()->all(), 0, 100);
        }

        $data = new \stdClass();
        $data->rank = $rank;
        $data->periode = $periode;
        $data->challengers = $challengers;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function loadRecords(Request $req)
    {
        $corrects = Challenger::find($req['id'])->attempts->where('status', '=', 1);
        $point = 0;
        $solved = 0;
        foreach ($corrects as $correct) {
            if ($correct->question->challenge_lesson_id == $req['lesson_id']) {
                $point += $correct->question->point;
                $solved += 1;
            }
        }

        $challenger = Challenger::with(['attempts' => function ($query) use ($req) {
            $query->where('status', '=', 1)
                ->orderBy('updated_at', 'desc')
                ->with('question');
        }])
            ->where('id', '=', $req['id'])
            ->first();

        $records = [];
        foreach ($challenger->attempts as $attempt) {
            if ($attempt->question->challenge_lesson_id == $req['lesson_id']) {
                $attempt->question->teacher;
                $records[] = ['question' => $attempt->question];
            }
        }
        $data = new \stdClass();
        $data->challenger_name = $challenger->student->name;
        $data->solved = $solved;
        $data->points = $point;
        $data->records = $records;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function loadHistories(Request $req)
    {
        if ($req['last_id'] == 0) {
            $questions = ChallengeQuestion::where([
                ['challenge_lesson_id', '=', $req['lesson_id']],
                ['status', '=', 0],
                ['active', '=', 1]
            ])
                ->take(10)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $questions = ChallengeQuestion::where([
                ['id', '<', $req['last_id']],
                ['challenge_lesson_id', '=', $req['lesson_id']],
                ['status', '=', 0],
                ['active', '=', 1]
            ])
                ->take(10)
                ->orderBy('id', 'desc')
                ->get();
        }

        $histories = [];
        foreach ($questions as $question) {
            $question->teacher;

            $challenge = new \stdClass();
            $challenge->question = $question;

            unset($question->teacher_id);
            unset($question->challenge_solution_id);

            $histories[] = $challenge;
        }

        $data = new \stdClass();
        $data->histories = $histories;

        $this->response['data'] = $data;
        return $this->result();
    }
}