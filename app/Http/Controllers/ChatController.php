<?php

namespace App\Http\Controllers;

use App\Helpers\AES;
use App\Helpers\Base;
use App\Models\Chat;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LRedis;

class ChatController extends Controller
{
    use Base;

    public function add(Request $req)
    {
        $sender = $this->getUser();

        $chat_data = [
            'sender_code' => $sender->code,
            'receiver_code' => $req['receiver_code'],
            'content' => $req['content'],
            'content_type' => intval($req['content_type']),
            'lesson_id' => intval($req['lesson_id']),
            'sent' => $req['sent']
        ];
        $chat = Chat::create($chat_data);

        if ($req['content_type'] == 1) {
            if ($req->file('image') != null) {
                $this->saveImage($req->file('image'), AES::decrypt($req['content']), 'chat');
            }
        } else if ($req['content_type'] == 2) {
            if ($req->file('document') != null) {
                $chat->content = AES::encrypt($this->getDocumentName($req->file('document'), $req['content'], 'chat'));
                $chat->save();
            }
        }
        $chat_data['content'] = $chat->content;
        $data = new \stdClass();
        $data->chat = $chat_data;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function loadQueue()
    {
        $user = $this->getUser();

        $dialogs = DB::select('select user_code, lesson_id as lesson from (
                                    select distinct sender_code user_code, lesson_id from chats where receiver_code = \'' . $user->code . '\'' . ' and sent = 0
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
                ['sender_code', $dialog->user_code],
                ['receiver_code', $user->code],
                ['lesson_id', $dialog->lesson->id],
                ['sent', 0]
            ])
                ->orderBy('id', 'desc')
                ->get();

            $dialog->user = $profile;
            $dialog->chats = array_reverse(json_decode($chats));
            $dialog->updated_at = $chats[0]->created_at->toDateTimeString();

            unset($dialog->user_code);
        }

        $data = new \stdClass();
        $data->dialogs = $dialogs;

        $this->response['data'] = $data;
        return $this->result();
    }

    public function markQueue()
    {
        $user = $this->getUser();

        $chats = Chat::where([
            ['receiver_code', $user->code],
            ['sent', 0]
        ])
            ->get();
        foreach ($chats as $chat) {
            $chat->sent = 1;
            $chat->save();
        }
        return $this->result();
    }
}