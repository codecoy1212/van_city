<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\LectureDay;
use App\Models\Student;
use App\Models\StudentLecture;
use App\Models\User;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MobileController extends Controller
{
    public function login(Request $request)
    {
        // return $request;

        $usn = $request->username;
        $pwd = $request->password;
        $dbpwd = "";
        $verification = User::where('username',$usn) -> first();
        // echo $verification;

        if($verification)
        {
            if($pwd == $verification->password)                  //main directory is here
            {
                $token = $verification->createToken($verification->username)->plainTextToken;

                $dbpwd = $verification->password;
                $str['status']=true;
                $str['message']="ADMIN LOGGED IN";
                $verification->token = $token;
                $str['data']=$verification;
                return $str;
            }
            else
            {
                $validator = Validator::make($request->all(),[
                'password' => ['required',Rule::in($dbpwd)],
                ], [
                'password.in' => 'Password is Incorrent.',
                'password.required' => 'Please enter your password.',
                ]);

                if ($validator->fails())
                {
                    $str['status']=false;
                    $error=$validator->errors()->toArray();
                    foreach($error as $x_value){
                        $err[]=$x_value[0];
                    }
                    $str['message'] =$err['0'];
                    return $str;
                }
            }

        }
        else
        {
            $validator = Validator::make($request->all(),[
            'username'=>'required|exists:users,username',
            'password' => 'required',
            ], [
            'password.required' => 'Please enter your Password.',
            'username.required' => 'Please enter your Username.',
            'username.exists' => 'Username is not Registered.',
            ]);

            if ($validator->fails())
            {
                $str['status']=false;
                $error=$validator->errors()->toArray();
                foreach($error as $x_value){
                    $err[]=$x_value[0];
                }
                $str['message'] =$err['0'];
                // $str['data'] = $validator->errors()->toArray();
                return $str;
            }
        }
    }

    public function log_out(Request $request)
    {
        // return $request;
        $vbl = User::find($request->user_id);

        if(empty($vbl))
        {
            $str['status']=false;
            $str['message']="LOGIN ID DOES NOT EXIST";
            return $str;
        }
        else
        {
            $request->user()->currentAccessToken()->delete();
            $str['status']=true;
            $str['message']="ADMIN LOG OUT SUCCESSFULL";
            return $str;
        }
    }

    public function add_class(Request $request)
    {
        // return $request;
        $validator = Validator::make($request->all(),[
            'name'=> 'required|min:3',
            'start_date'=> 'required|date_format:Y-m-d',
            'end_date'=> 'required|date_format:Y-m-d',
            'start_time'=> 'required|date_format:h:i:s A',
            'end_time'=> 'required|date_format:h:i:s A',
            'lecture_students' => 'exists:students,id',

        ], [
            'name.required' => 'Please enter Class Name.',
            'name.min' => 'Class name must consist of 3 characters.',
            'start_date.required' => 'Lecture Starting Date is compuslory.',
            'start_date.date_format' => 'Lecture Starting Date format is Incorrect.',
            'end_date.required' => 'Lecture Ending Date is compuslory.',
            'end_date.date_format' => 'Lecture Ending Date format is Incorrect.',
            'start_time.required' => 'Lecture Starting Time is compuslory.',
            'start_time.date_format' => 'Lecture Starting Time format is Incorrect.',
            'end_time.required' => 'Lecture Ending Time is compuslory.',
            'end_time.date_format' => 'Lecture Ending Time format is Incorrect.',
            'lecture_students.exists' => 'Student ID does not exists.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
             $str['message'] =$err['0'];
            // $str['data'] = $validator->errors()->toArray();
            return $str;
        }
        else
        {
            $days_done = array();

            if(count($request->lecture_days) != 0)
            {
                $vbl2 = $request->lecture_days;
                sort($vbl2);
                foreach ($vbl2 as $value) {
                    if($value == "Sun"){
                        if (in_array("Sun", $days_done)){}
                        else
                        array_push($days_done,"Sun");
                    }
                    if($value == "Mon"){
                        if (in_array("Mon", $days_done)){}
                        else
                        array_push($days_done,"Mon");
                    }
                    if($value == "Tue"){
                        if (in_array("Tue", $days_done)){}
                        else
                        array_push($days_done,"Tue");
                    }
                    if($value == "Wed"){
                        if (in_array("Wed", $days_done)){}
                        else
                        array_push($days_done,"Wed");
                    }
                    if($value == "Thu"){
                        if (in_array("Thu", $days_done)){}
                        else
                        array_push($days_done,"Thu");
                    }
                    if($value == "Fri"){
                        if (in_array("Fri", $days_done)){}
                        else
                        array_push($days_done,"Fri");
                    }
                    if($value == "Sat"){
                        if (in_array("Sat", $days_done)){}
                        else
                        array_push($days_done,"Sat");
                    }
                    if($value != "Sun" && $value != "Mon" && $value != "Tue" && $value != "Wed" &&
                    $value != "Thu" && $value != "Fri" && $value != "Sat")
                    {
                        $str['status']=false;
                        $str['message']="WEEK DAYS NOT VALID";
                        return $str;
                    }
                }
            }
            // // $today_date = date('Y-m-d');
            // // $today_date = "2022-01-23";
            $today_date = $request->start_date;
            $today = date('D',strtotime($today_date));
            if (in_array($today, $days_done)){}
            else
            array_push($days_done,$today);
            sort($days_done);
            // // return $days_done;

            $vbl3 = new Lecture;
            $vbl3->name = $request->name;
            $vbl3->start_date = $request->start_date;
            $vbl3->end_date = $request->end_date;
            $vbl3->start_time = $request->start_time;
            $vbl3->end_time = $request->end_time;
            $vbl3->save();
            foreach ($days_done as $value2) {
                $vbl5 = new LectureDay;
                $vbl5->lecture_id = $vbl3->id;
                $vbl5->lecture_day = $value2;
                $vbl5->save();
            }
            if (count($request->lecture_students) != 0)
            {
                $stu_list = array();
                foreach ($request->lecture_students as $value) {
                    if (in_array($value, $stu_list)){}
                    else{
                        $vbl4 = new StudentLecture;
                        $vbl4->lecture_id = $vbl3->id;
                        $vbl4->student_id = $value;
                        $vbl4->save();
                        array_push($stu_list,$value);
                    }
                }
            }

            $str['status']=true;
            $str['message']="NEW CLASS CREATED";
            return $str;
        }
    }

    public function remove_class(Request $request)
    {
        // return $request;
        $vbl = Lecture::find($request->class_id);

        if(empty($vbl))
        {
            $str['status']=false;
            $str['message']="CLASS DOES NOT EXIST";
            return $str;
        }
        else
        {
            Attendance::where('lecture_id',$vbl->id)->delete();
            LectureDay::where('lecture_id',$vbl->id)->delete();
            StudentLecture::where('lecture_id',$vbl->id)->delete();
            $vbl->delete();

            $str['status']=true;
            $str['message']="CLASS DELETED";
            return $str;
        }
    }

    public function show_class(Request $request)
    {
        $vbl = Lecture::find($request->class_id);

        if(empty($vbl))
        {
            $str['status']=false;
            $str['message']="CLASS DOES NOT EXIST";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="CLASS DETAILS SHOWN";
            $str['data']=$vbl;
            return $str;
        }
    }

    public function update_class(Request $request)
    {
        // return "HELLO";
        // return $request;
        $validator = Validator::make($request->all(),[
            'class_id'=> 'required|exists:lectures,id',
            'name'=> 'required|min:3',
            'start_date'=> 'required|date_format:Y-m-d',
            'end_date'=> 'required|date_format:Y-m-d',
            'start_time'=> 'required|date_format:h:i:s A',
            'end_time'=> 'required|date_format:h:i:s A',
        ], [
            'name.required' => 'Please enter Class Name.',
            'name.min' => 'Class name must consist of 3 characters.',
            'start_date.required' => 'Lecture Starting Date is compuslory.',
            'start_date.date_format' => 'Lecture Starting Date format is Incorrect.',
            'end_date.required' => 'Lecture Ending Date is compuslory.',
            'end_date.date_format' => 'Lecture Ending Date format is Incorrect.',
            'start_time.required' => 'Lecture Starting Time is compuslory.',
            'start_time.date_format' => 'Lecture Starting Time format is Incorrect.',
            'end_time.required' => 'Lecture Ending Time is compuslory.',
            'end_time.date_format' => 'Lecture Ending Time format is Incorrect.',
            'lecture_students.exists' => 'Student ID does not exists.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
             $str['message'] =$err['0'];
            // $str['data'] = $validator->errors()->toArray();
            return $str;
        }
        else
        {
            $days_done = array();

            if(count($request->lecture_days) != 0)
            {
                $vbl2 = $request->lecture_days;
                sort($vbl2);
                foreach ($vbl2 as $value) {
                    if($value == "Sun"){
                        if (in_array("Sun", $days_done)){}
                        else
                        array_push($days_done,"Sun");
                    }
                    if($value == "Mon"){
                        if (in_array("Mon", $days_done)){}
                        else
                        array_push($days_done,"Mon");
                    }
                    if($value == "Tue"){
                        if (in_array("Tue", $days_done)){}
                        else
                        array_push($days_done,"Tue");
                    }
                    if($value == "Wed"){
                        if (in_array("Wed", $days_done)){}
                        else
                        array_push($days_done,"Wed");
                    }
                    if($value == "Thu"){
                        if (in_array("Thu", $days_done)){}
                        else
                        array_push($days_done,"Thu");
                    }
                    if($value == "Fri"){
                        if (in_array("Fri", $days_done)){}
                        else
                        array_push($days_done,"Fri");
                    }
                    if($value == "Sat"){
                        if (in_array("Sat", $days_done)){}
                        else
                        array_push($days_done,"Sat");
                    }
                    if($value != "Sun" && $value != "Mon" && $value != "Tue" && $value != "Wed" &&
                    $value != "Thu" && $value != "Fri" && $value != "Sat")
                    {
                        $str['status']=false;
                        $str['message']="WEEK DAYS NOT VALID";
                        return $str;
                    }
                }
            }
            // // $today_date = date('Y-m-d');
            // // $today_date = "2022-01-23";
            $today_date = $request->start_date;
            $today = date('D',strtotime($today_date));
            if (in_array($today, $days_done)){}
            else
            array_push($days_done,$today);
            sort($days_done);
            // // return $days_done;

            $vbl3 = Lecture::find($request->class_id);
            $vbl3->name = $request->name;
            $vbl3->start_date = $request->start_date;
            $vbl3->end_date = $request->end_date;
            $vbl3->start_time = $request->start_time;
            $vbl3->end_time = $request->end_time;
            $vbl3->update();

            LectureDay::where('lecture_id',$vbl3->id)->delete();

            foreach ($days_done as $value2) {
                $vbl5 = new LectureDay;
                $vbl5->lecture_id = $vbl3->id;
                $vbl5->lecture_day = $value2;
                $vbl5->save();
            }

            $str['status']=true;
            $str['message']="CLASS UPDATED";
            return $str;
        }
    }

    public function show_classes()
    {
        $vbl = Lecture::all();

        if(count($vbl) == 0)
        {
            $str['status']=false;
            $str['message']="NO LECTURES ADDED YET";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="ALL LECTURES DETAILS SHOWN";
            $str['data']=$vbl;
            return $str;
        }
    }

    public function add_student(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'=> 'required|min:3',
            'email'=> 'required|email:rfc,dns|unique:students,email',
            'phone' => 'required|digits:11',

        ], [
            'name.required' => 'Please enter your Name.',
            'name.min' => 'Name must be at least 3 characters.',
            'email.required' => 'Please enter your Email.',
            'email.unique' => 'Email is already registered.',
            'email.email' => 'Email is invalid.',
            'phone.required' => 'Phone number is required.',
            'phone.digits' => 'Mobile number is not valid.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
             $str['message'] =$err['0'];
            // $str['data'] = $validator->errors()->toArray();
            return $str;
        }
        else
        {
            $var = new Student;
            $var->name = $request->name;
            $var->email = $request->email;
            $var->phone = $request->phone;
            $var->save();

            $str['status']=true;
            $str['message']="STUDENT ADDED TO THE DATABASE";
            // $str['data']=$var;
            return $str;

        }
    }

    public function update_student(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'student_id'=> 'required|exists:students,id',
            'name'=> 'required|min:3',
            'email'=> 'required|email:rfc,dns|unique:students,email,'.$request->student_id,
            'phone' => 'required|digits:11',

        ], [
            'name.required' => 'Please enter your Name.',
            'name.min' => 'Name must be at least 3 characters.',
            'email.required' => 'Please enter your Email.',
            'email.unique' => 'Email is already registered.',
            'email.email' => 'Email is invalid.',
            'phone.required' => 'Phone number is required.',
            'phone.digits' => 'Mobile number is not valid.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
             $str['message'] =$err['0'];
            // $str['data'] = $validator->errors()->toArray();
            return $str;
        }
        else
        {
            $var = Student::find($request->student_id);
            $var->name = $request->name;
            $var->email = $request->email;
            $var->phone = $request->phone;
            $var->update();

            $str['status']=true;
            $str['message']="STUDENT UPDATED TO THE DATABASE";
            // $str['data']=$var;
            return $str;

        }
    }

    public function remove_student(Request $request)
    {
        $vbl = Student::find($request->student_id);

        if(empty($vbl))
        {
            $str['status']=false;
            $str['message']="STUDENT DOES NOT EXIST";
            return $str;
        }
        else
        {
            $vbl->delete();
            $str['status']=true;
            $str['message']="STUDENT DELETED";
            return $str;
        }
    }

    public function show_student(Request $request)
    {
        $vbl = Student::find($request->student_id);

        if(empty($vbl))
        {
            $str['status']=false;
            $str['message']="STUDENT DOES NOT EXIST";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="STUDENT DETAILS SHOWN";
            $str['data']=$vbl;
            return $str;
        }
    }

    public function show_students()
    {
        $vbl = Student::all();

        if(count($vbl) == 0)
        {
            $str['status']=false;
            $str['message']="NO STUDENT ADDED YET";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="ALL STUDENTS DETAILS SHOWN";
            $str['data']=$vbl;
            return $str;
        }
    }

    public function send_all()
    {
        return "HELLO";
    }

    public function send_specific()
    {
        return "HELLO";
    }

    public function show_attendance(Request $request)
    {
        $vbl0 = Lecture::find($request->class_id);
        if(empty($vbl0))
        {
            $str['status']=false;
            $str['message']="LECTURE ID DOES NOT EXIST";
            return $str;
        }

        // return $request;
        // $vbl = Attendance::where('date',$request->date)->where('lecture_id',$request->class_id)->get();
        $vbl = DB::table('attendances')
        ->where('date','=',$request->date)
        ->where('lecture_id','=',$request->class_id)
        ->join('students','students.id','=','attendances.student_id')
        ->select('students.*')
        ->get();
        // return $vbl;

        if(count($vbl) == 0)
        {
            $str['status']=false;
            $str['message']="NO ATTENDANCE FOR CLASS OR INVALID DATE FORMAT";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="SHOWN ATTENDANCE SUBMITTED FOR TODAY";
            $str['data']=$vbl;
            return $str;
        }
    }

    public function mark_attendance(Request $request)
    {
        // return $request;

        $vbl0 = Attendance::where('lecture_id',$request->class_id)->where('date',date('Y-m-d'))->first();
        // return $vbl0;
        if(empty($vbl0)){}
        else
        {
            $str['status']=false;
            $str['message']="ATTENDANCE ALREADY SUBMITTED FOR TODAY";
            return $str;
        }

        $vbl = StudentLecture::where('lecture_id',$request->class_id)->get();
        // echo count($vbl);
        $vbl2 = $request->lecture_students;
        // echo $vbl2;

        foreach ($vbl2 as $value) {
            $vbl4 = Student::find($value['student_id']);
            if(empty($vbl4))
            {
                $str['status']=false;
                $str['message']="STUDENTS ID'S NOT REGISTERED";
                return $str;
            }
        }

        if(count($vbl) == count($vbl2))
        {
            $today_date = date('Y-m-d');
            $today = date('Y-m-d',strtotime($today_date));
            // echo $today;

            $stu_list = array();
            foreach ($vbl2 as $value) {
                if (in_array($value['student_id'], $stu_list)){}
                else{
                    $vbl3 = new Attendance;
                    $vbl3->date = $today;
                    $vbl3->student_id = $value['student_id'];
                    $vbl3->status = $value['status'];
                    $vbl3->lecture_id = $request->class_id;
                    $vbl3->save();
                    array_push($stu_list, $value['student_id']);
                }
            }

            $str['status']=true;
            $str['message']="ATTENDANCE SUBMITTED FOR TODAY";
            return $str;
        }
        else
        {
            $str['status']=false;
            $str['message']="Please SUBMIT ALL STUDENTS ATTENDANCE";
            return $str;
        }
    }

    public function classes_count(Request $request)
    {
        if($request->date == null || $request->date == "")
        {
            $first_day = date('Y-m-01'); // hard-coded '01' for first day
            $last_day = date('Y-m-t');

            $begin = new DateTime($first_day);
            $end = new DateTime($last_day);
            $end->modify('+1 day');

            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);

            $final_array = array();
            foreach ($period as $dt) {
                $vbl6 = $dt->format("Y-m-d");
                $day = date('D',strtotime($vbl6));
                // echo $day10 = date('Y-m-d',strtotime($vbl6));
                // echo "\n";

                $vbl = DB::table('lecture_days')
                ->join('lectures','lectures.id','=','lecture_days.lecture_id')
                ->select('lecture_days.lecture_day','lectures.*')
                ->get();

                if(count($vbl) == 0)
                {
                    $str['status']=false;
                    $str['message']="NO CLASSES ADDED YET TO THE DATABASE";
                    return $str;
                }

                foreach ($vbl as $value) {
                    // echo $value->lecture_day."\n";
                    if($value->lecture_day == $day)
                    {
                        $day2 = date('Y-m-d',strtotime($value->start_date));
                        $day4 = strtotime($day2);
                        // echo "\n";
                        $day3 = date('Y-m-d',strtotime($vbl6));
                        $day5 = strtotime($day3);
                        // echo "\n";

                        $day8 = date('Y-m-d',strtotime($value->end_date));
                        $day9 = strtotime($day8);
                        // echo "\n";
                        // echo "HELLO HELLO HELLO";
                        // echo "\n";

                        if($day4 <= $day5 && $day5 <= $day9)
                        {
                            // echo "HELLO HELLO HELLO HELLO HELLO HELLO ".$value->name;
                            // echo "\n";
                            $value->lecture_day = $day3;
                            array_push($final_array,$value);
                        }
                    }
                }

                // echo "\n";
                // $vbl = strtotime($last_day);
                // return date('Y-m-d', strtotime('Y-m-t +1 day'));
                // $datetime = new DateTime($last_day);
                // $datetime->modify('+1 day');
                // return $datetime->format('Y-m-d H:i:s');
                // $vbl2 = format($end);
                // echo date($end->format("Y-m-d\n"));

                // $vbl = DB::table('lecture_days')
                // ->join('lectures','lectures.id','=','lecture_days.lecture_id')
                // ->select('lecture_days.lecture_day','lectures.*')
                // ->get();
                // foreach ($vbl as $value) {
                //     // echo $value->lecture_day."\n";
                //     if($value->lecture_day == $day)
                //     {
                //         echo $day2 = date('Y-m-d',strtotime($value->start_date));
                //         echo "\n";
                //         echo $day3 = date('Y-m-d',strtotime($vbl6));
                //         echo "\n";
                //         echo "HELLO HELLO HELLO HELLO HELLO HELLO ".$value->name;
                //         echo "\n";
                //     }
                // }
            }
            $str['status']=true;
            $str['message']="ALL CLASSES OF THIS MONTH SHOWN";

            if($final_array == null || $final_array == "" || $final_array == [])
            {
                $str['status']=false;
                $str['message']="NO CLASSES ADDED TO THIS MONTH YET";
                return $str;
            }

            $str['data']=$final_array;
            return $str;
        }
        else
        {
            // return $request;
            // $vbl = Lecture::all();

            $today_date = $request->date;
            $day = date('D',strtotime($today_date));
            // return $day;
            $vbl = DB::table('lecture_days')
            ->where('lecture_day',$day)
            ->join('lectures','lectures.id','=','lecture_days.lecture_id')
            ->select('lecture_days.lecture_day','lectures.*')
            ->get();

            // return $vbl;

            if(count($vbl) == 0)
            {
                $str['status']=false;
                $str['message']="NO LECTURES ADDED TO THIS DATE YET";
                return $str;
            }
            else
            {
                $date1 = $request->date;
                $date1 = strtotime($date1);
                // echo $date1;

                $var2 = array();
                foreach ($vbl as $value) {
                    $date2 = $value->start_date;
                    $date2 = strtotime($date2);

                    $date3 = $value->end_date;
                    $date3 = strtotime($date3);
                    // echo $date3;
                    // echo $date1;
                    if($date2 <= $date1 && $date1 <= $date3)
                    {
                        array_push($var2,$value);
                    }
                }

                $str['status']=true;
                $str['message']="SPECIFIC DATE LECTURES SHOWN";

                if($var2 == null || $var2 == "" || $var2 == [])
                {
                    $str['status']=false;
                    $str['message']="NO CLASSES ADDED TO THIS DATE YET";
                    return $str;
                }

                $str['data']=$var2;
                return $str;
            }
        }
    }


    public function search_students(Request $request)
    {
        // $vbl = StudentLecture::where('lecture_id',$request->class_id)->get();
        $vbl = DB::table('students')
        ->select('students.*')
        ->where('id', 'like',"%".$request->search."%")
        ->orWhere('name', 'like',"%".$request->search."%")
        ->orWhere('email', 'like',"%".$request->search."%")
        ->orWhere('phone', 'like',"%".$request->search."%")
        ->get();
        // return $vbl;

        if(count($vbl) == 0)
        {
            $str['status']=false;
            $str['message']="NO STUDENT MACHED YOUR SEARCH QUERY";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="MATCHED SEARCH QUERY STUDENTS SHOWN";
            $str['data']=$vbl;
            return $str;
        }
    }

    public function show_class_students(Request $request)
    {
        // $vbl = StudentLecture::where('lecture_id',$request->class_id)->get();
        $vbl = DB::table('student_lectures')
        ->where('lecture_id',$request->class_id)
        ->join('students','students.id','=','student_lectures.student_id')
        ->select('students.*')
        ->get();

        if(count($vbl) == 0)
        {
            $str['status']=false;
            $str['message']="NO STUDENT ADDED YET";
            return $str;
        }
        else
        {
            $str['status']=true;
            $str['message']="ALL STUDENTS OF THIS CLASS SHOWN";
            $str['data']=$vbl;
            return $str;
        }
    }

    public function update_class_students(Request $request)
    {
        // return $request;
        $validator = Validator::make($request->all(),[
            'class_id'=> 'required|exists:lectures,id',
            'lecture_students' => 'exists:students,id',
        ], [
            'lecture_students.exists' => 'Student ID does not exists.',
            ]);
        if ($validator->fails())
        {
            $str['status']=false;
            $error=$validator->errors()->toArray();
            foreach($error as $x_value){
                $err[]=$x_value[0];
            }
             $str['message'] =$err['0'];
            // $str['data'] = $validator->errors()->toArray();
            return $str;
        }
        else
        {
            if (count($request->lecture_students) != 0)
            {
                StudentLecture::where('lecture_id',$request->class_id)->delete();

                $stu_list = array();
                foreach ($request->lecture_students as $value) {
                    if (in_array($value, $stu_list)){}
                    else{
                        $vbl4 = new StudentLecture;
                        $vbl4->lecture_id = $request->class_id;
                        $vbl4->student_id = $value;
                        $vbl4->save();
                        array_push($stu_list,$value);
                    }
                }
            }
            else
            {
                StudentLecture::where('lecture_id',$request->class_id)->delete();
            }

            $str['status']=true;
            $str['message']="CLASS STUDENT LIST UPDATED";
            return $str;
        }
    }
}
