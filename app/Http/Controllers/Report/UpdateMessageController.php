<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Longman\TelegramBot\Telegram;
use DB;
use Carbon\Carbon;

class UpdateMessageController extends Controller
{

    public function updateMessage()
    {
        date_default_timezone_set('Asia/Bangkok');
        $a = new \Longman\TelegramBot\Request();
        $bot_api_key = '1065422651:AAG5xftZNiwBVs8-c40qVpOood1K-tg6qGY';
        $bot_username = 'TheSmartMovebot';
        $mysql_credentials = [
            'host' => 'localhost',
            'port' => 3306, // optional
            'user' => 'root',
            'password' => '',
            'database' => 'telegrambot',
        ];
        try {
            // Create Telegram API object
            $telegram = new \Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
            // Handle telegram getUpdates request
            $telegram->enableMySql($mysql_credentials);
            $data = $telegram->handleGetUpdates();
            //check buoi toi
            $like_text_1 = "cong viec cua em";
            $like_text_2 = "thoi gian thuc te";
            $day = 'sáng';
            $message_buoi_toi = $this->checkReport($day, $like_text_1, $like_text_2);
            $this->sendmessage($message_buoi_toi);
            //check buoi sang
//            $message_buoi_sang = $this->checkReport($buoi, $like_text_1, $like_text_2);
//            $buoi = "sang";
//            $this->sendmessage($message_buoi_sang);
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            // log telegram errors
            // echo $e->getMessage();
        }
    }
    private function sendmessage($message)
    {
        $a = new \Longman\TelegramBot\Request();
        $result = $a->sendMessage([
            'chat_id' => -290352932,
            'text' => $message,
        ]);
        return;
    }
    private function checkReport($day, $like_text_1, $like_text_2)
    {
        $dateZone = Carbon::now('Asia/Ho_Chi_Minh');
        $today = $dateZone->toDateString();
        $realTime = $dateZone->toTimeString();
        $data = DB::table('message')
            ->join('user', 'user.id', 'message.user_id')
            ->join('chat', 'chat.id', 'message.chat_id')
            ->whereDate('date', $dateZone)
            ->where('message.chat_id', '-339631790')
//            ->where('message.text', 'like', "%$like_text_1%")
//            ->orWhere('message.text', 'like', "%$like_text_2%")
            ->select(['user.id', 'user.first_name', 'user.last_name', 'message.text', 'chat_id'])
            ->get()->toArray();

        $user_has_report = [];
        foreach ($data as $val) {
            if ($realTime > '09:00:00' && strpos("$val->text","$like_text_1")  !== false ) {
                $day = 'sáng';
                $user_has_report[] = $val->id;

            }
           elseif($realTime > '16:00:00' && strpos("$val->text","$like_text_2")  !== false) {
                $day = 'tối';
                $user_has_report[] = $val->id;

            }
        }
//        echo "<pre>";
//        print_r($user_has_report);
//        echo $day;
//        die();


        $message = $this->getUsersNotReport($day, $user_has_report);
        return $message;
    }


    private function getUsersNotReport($day, $ids)
    {
        $data = DB::table('user')
            ->whereNotIn('id', $ids)
            ->get()->toArray();
        $message = "";
        foreach ($data as $val) {
            $text = $val->last_name ." ". $val->first_name . " : chưa bảo cáo buổi $day" . "\n";
            $message .= $text;
        }
        echo "Sussess!";
        return $message;
    }
}
