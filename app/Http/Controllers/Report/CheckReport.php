<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckReport extends Controller
{
    public function sendmessage()
    {
        $a = new \Longman\TelegramBot\Request();
        $Connect = new ConnectTele();
        $this->index();
        $getMessage = DB::table('message')
            ->join('user', 'user.id', '=', 'message.user_id')
            ->join('chat', 'chat.id', '=', 'message.chat_id');
        $dateZone = Carbon::now('Asia/Ho_Chi_Minh');
        $time = $dateZone->toTimeString();
        $date = $dateZone->toDateString();
//        $dateNotification = '16:34:00';
        $message = "cong viec cua em";
        $users = $getMessage->select(['user.id', 'user.first_name', 'user.last_name', 'message.text', 'chat_id'])
            ->where('message.chat_id', '=', '-290352932')->whereDate('date', $dateZone)
            ->get();
        $userGroups = $users->groupBy('id')->toArray();
//        echo "<pre>";
//        print_r($userGroups);
        foreach ($userGroups as $id=>$rowObject){
            echo "user id {$id}";
            echo "<br>";
            foreach ($rowObject as $textObject){
                echo $textObject->text;
                echo "<br>";
                if (strpos("$textObject->text", "$message") === false) {}
//                echo $textObject->text;
//                echo "<br>";
            }
            echo "<hr>";
        }
//        $groupIns = [];
////        $group = [];
////       foreach ($userGroups as $userGroup){
//           foreach ($users as $user) {
////               $groupIns[] = $user;
//               if (strpos("$user->text", "$message") === false) {
////                   $groupIns[$user->id] = $user;
//
//                   $groupIns[] = $user;
//               }
//           }
////       }
//        echo "<pre>";
//        print_r($groupIns);

//        foreach ($groupIns as $groupIn){
//            $result = $a->sendMessage([
//                        'chat_id' => '-290352932',
//                        'text' => " $groupIn->first_name báo cáo nhá",
//                    ]);
//            $group[]  =$groupIn;
//        }
//        echo "<pre>";
//        print_r($groupIn);



    }
}
