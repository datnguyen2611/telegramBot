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
        date_default_timezone_set('Asia/Ho_Chi_Minh');

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
            $time = 'AM'; // PM
            $chat_id = '-452474869';
            $check_report = $this->checkReport($time, $chat_id);
            echo '<pre>';
            print_r($check_report);
            die();
            $this->sendmessage($check_report, $chat_id);
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            // log telegram errors
            // echo $e->getMessage();
        }
    }

    private function sendmessage($message, $chatId)
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

        $data = DB::table('message')
            ->join('user', 'user.id', 'message.user_id')
            ->join('chat', 'chat.id', 'message.chat_id')
            ->whereDate('date', $dateZone)
            ->where('message.chat_id', $chatId)
            ->select(['user.id', 'user.first_name', 'user.last_name', 'message.text', 'chat_id', 'message.date'])
            ->get()->toArray();

        /* @todo: sua tien bien */
        $userReportMorning = [];
        $userReportAfternoon = [];
        $timeReportMorning = [];
        $timeReportNight = [];

        #var_dump($data);die;

        foreach ($data as $val) {
            $isLateMorning = $this->checkBaoCaoBuoiSang($val->text, $val->date);
            $isLateAfternoon = $this->checkBaoCaoBuoiChieu($val->text, $val->date);

//            $userReportSang[] = $isMorning;
            if ($isLateMorning) {
                $userReportMorning[] = $val->id;

            }
            if ($isLateAfternoon) {
                $userReportAfternoon[] = $val->id;

            }
        }

        $userReportMorning = array_unique($userReportMorning);
        $userReportAfternoon = array_unique($userReportAfternoon);
        $message = $this->getUsersNotReport
        ($chatId, $userReportMorning, $userReportAfternoon);

//        var_dump($userReportAfternoon);die;

        return $message;
    }

    private function checkTimeMorning($time1, $time2)
    {
        $time = date("H:i:s", strtotime($time1));
        return $time < $time2;
    }

    private function checkTimeNight($time1, $time2)
    {
        $time = date("h:i:s", strtotime($time1));
        return $time > $time2;
    }

    private function checkBaoCaoBuoiSang($message, $timeMessage)
    {
        $flag = false;

        $message = $this->vn_to_str($message);
        $message = strtolower($message);
        $message = trim(preg_replace('/\s+/', ' ', $message));
        $text = "cong viec cua em";
        $before12 = $this->checkTimeMorning($timeMessage, '12:00:00');
        if (strpos($message, $text) !== false && $before12) {
            $flag = true;
            echo 'sang';
        }
        return $flag;
    }

    private function checkBaoCaoBuoiChieu($message, $timeMessage)
    {
        $flag = false;
        $message = $this->vn_to_str($message);
        $message = strtolower($message);
        $message = trim(preg_replace('/\s+/', ' ', $message));
        $text = "cong viec hoan thanh";
        $after20 = $this->checkTimeNight($timeMessage, '20:00:00');
        if (strpos($message, $text) !== false && $after20) {
            $flag = true;
            echo 'chieu';
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
        return preg_replace('/\s+/', ' ', $str);
    }

    private function getUsersNotReport($chatId, $userNotReportMorning, $userNotReportNight)
    {
        $data = DB::table('user_chat')
            ->join('user', 'user.id', 'user_chat.user_id')
            ->join('chat', 'chat.id', 'user_chat.chat_id')
            ->where('user.is_bot', 0)
            ->where('has_report', 0)
            ->where('user_chat.chat_id', $chatId)
            ->select('user.first_name', 'user.last_name', 'user.id');
        echo '<pre>';
        $userIdsNotReportNight = $data->whereNotIn('user.id', $userNotReportNight)->get()->toArray();
        $userIdsNotReportMorning = $data->whereNotIn('user.id', $userNotReportMorning)->get()->toArray();

        $message = "";
        echo '<pre>';
        print_r($userIdsNotReportNight);
        die();
        if (count($userIdsNotReportMorning) && $checkTimeMorning) {
            $message .= "Báo cáo buổi sáng: " . PHP_EOL;
            foreach ($userIdsNotReportMorning as $val) {
                $day = 'Sáng';
                $text = $val->first_name . " " . $val->last_name . " : chưa bảo cáo buổi $day" . "\n";
                $message .= "   " . $text;
            }
        }
        if (count($userIdsNotReportNight) && $checkTimeNight) {
            $message .= "Báo cáo buổi tối: " . PHP_EOL;
            foreach ($userIdsNotReportNight as $val) {
                $day = 'Tối';
                $text = $val->first_name . " " . $val->last_name . " : chưa bảo cáo buổi $day" . "\n";
                $message .= "   " . $text;
            }
        }
        return $message;
    }


}
