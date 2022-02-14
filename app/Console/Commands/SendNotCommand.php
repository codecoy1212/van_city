<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendNotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $vbl = DB::table('fee_details')
        ->where('status','=',false)
        ->join('students','students.id','=','fee_details.student_id')
        ->select('students.name as student_name','fee_details.*')
        ->get();

        foreach($vbl as $user){
            $SERVER_API_KEY = 'AAAAR0JMZJk:APA91bHlVp9p5nc6jse-m8QotoSH5d9RB_sdv9V9R9wsJnZV7SnqqZPqg0kfja7iZz03V9MicuUnpBggRn6LfmgjxJswmSuj4JGyeTtuuPXQwJmMVTH7eGQPwndv2Bs7jQ2j-bE82MCx';
            $data = [
                "to" => '/topics/topic',
                "data" => [
                    "title" => "Van City",
                    "message" => 'Fee is due of '.$user->student_name.' on this date '.$user->due_date,
                    "student_id" => $user->student_id,
                    "student_name" => $user->student_name,
                    ]
            ];
            $dataString = json_encode($data);
            $headers = [
                'Authorization: key=' . $SERVER_API_KEY,
                'Content-Type: application/json',
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            $response = curl_exec($ch);
            // dd($response);
            return 0;
        }
    }
}
