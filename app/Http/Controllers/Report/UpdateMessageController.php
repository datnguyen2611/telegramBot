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
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'), // optional
            'user' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_DATABASE'),
        ];
        try {
            // Create Telegram API object
            $telegram = new \Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
            // Handle telegram getUpdates request
            $telegram->enableMySql($mysql_credentials);
            $data = $telegram->handleGetUpdates();
            //check buoi toi
            $day = 'sáng';
            $chatId = '-549030259';
            $checkreport = $this->checkReport($day, $chatId);
            $this->sendmessage($checkreport);
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            // log telegram errors
            // echo $e->getMessage();
        }
    }

    private function sendmessage($message ,$chatId)
    {
        $a = new \Longman\TelegramBot\Request();
        $result = $a->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
        ]);
        return;
    }

    private function checkReport($day, $chatId)
    {
        $dateZone = Carbon::now('Asia/Ho_Chi_Minh');
        $today = $dateZone->toDateString();
        $realTime = $dateZone->toTimeString();
        $data = DB::table('message')
            ->join('user', 'user.id', 'message.user_id')
            ->join('chat', 'chat.id', 'message.chat_id')
            ->whereDate('date', $dateZone)
            ->where('message.chat_id', $chatId)
            ->select(['user.id', 'user.first_name', 'user.last_name', 'message.text', 'chat_id', 'message.date'])
            ->get()->toArray();
        $userReportSang = [];
        $userReportToi = [];

        foreach ($data as $val) {
            $isMorning = $this->checkBaoCaoBuoiSang($val->text, $val->date);
            $isAfternoon = $this->checkBaoCaoBuoiChieu($val->text, $val->date);
//            $userReportSang[] = $isMorning;
            if ($isMorning) {
                $userReportSang[] = $val->id;
            }
            if ($isAfternoon) {
                $userReportToi[] = $val->id;

            }
        }

//        $message = $this->getUsersNotReport($day, $chatId , $userReportSang , $userReportToi);
        $message = $this->getUsersNotReport($day, $chatId,$userReportSang,$userReportToi);
        echo "<pre>";
        print_r($message);
        die();
        return $message;
    }

    private function checkTimeSang($time1, $time2)
    {
        $time1 = Carbon::parse($time1);
        $time2 = Carbon::parse($time2);
       return  $time1->lt($time2);
    }

    private function checkTimeToi($time1, $time2)
    {
        $time1 = Carbon::parse($time1);
        $time2 = Carbon::parse($time2);
        return $time1->gt($time2);
    }

    private function checkBaoCaoBuoiSang($message, $timeMessage)
    {
        $flag = false;
        $message = $this->vn_to_str($message);
        $message = strtolower($message);
        $message = trim(preg_replace('/\s+/', ' ', $message));
        $text = "cong_viec_cua_em";
        $before12 = $this->checkTimeSang($timeMessage, '09:00:00');
        if (strpos("$message", "$text") !== false && $before12 !== false) {
            $flag = true;
        }
        return $flag;
    }

    private function checkBaoCaoBuoiChieu($message, $timeMessage)
    {
        $flag = false;
        $message = $this->vn_to_str($message);
        $message = strtolower($message);
        $message = trim(preg_replace('/\s+/', ' ', $message));
        $text = "cong_viec_hoan_thanh";
        $before12 = $this->checkTimeToi($timeMessage, '15:00:00');

        if (strpos("$message", "$text") !== false && $before12 !== false) {
            $flag = true;
        }
        return $flag;
    }

    private function vn_to_str($str)
    {
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        $str = str_replace(' ', '_', $str);
        return $str;
    }

    private function getUsersNotReport($day, $chatId,$userNotReportSang,$userNotReportToi)
    {
        $data = DB::table('user_chat')
            ->join('user', 'user.id', 'user_chat.user_id')
            ->join('chat', 'chat.id', 'user_chat.chat_id')
            ->where('user.is_bot', 0)
            ->where('has_report', 0)
            ->where('user_chat.chat_id', $chatId)
            ->select('user.first_name', 'user.last_name');
//            ->get()->toArray();
        $userNotReportSang = $data->whereNotIn('user.id', $userNotReportSang)->get()->toArray();
        $userNotReportToi = $data->whereNotIn('user.id', $userNotReportToi)->get()->toArray();
        $message = "";
//        $dateZone = Carbon::now('Asia/Ho_Chi_Minh');
        $timeZone = Carbon::now()->toTimeString();
        if ($userNotReportSang && $timeZone <'09:00:00'){
            foreach ($userNotReportSang as $val) {
                $day = 'Sáng';
                $text = $val->first_name . " " . $val->last_name . " : chưa bảo cáo buổi $day" . "\n";
                $message .= $text;
            }
        }
        if ($userNotReportToi && $timeZone >'20:00:00'){
            foreach ($userNotReportToi as $val) {
                $day = 'Tối';
                $text = $val->first_name . " " . $val->last_name . " : chưa bảo cáo buổi $day" . "\n";
                $message .= $text;
            }
        }
//        foreach ($data as $val) {
//            $text = $val->first_name . " " . $val->last_name . " : chưa bảo cáo buổi $day" . "\n";
//            $message .= $text;
//        }
        echo "Sussess!";
        return $message;
    }


}
