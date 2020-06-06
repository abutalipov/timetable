<?php

namespace App\Http\Controllers;

use App\Models\Occupation;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TimetableController extends Controller
{
    public function index(){

        $timetables = Timetable::with(['user','group','location','occupation'])->get();

        $this->apiResponse->setData(null, $timetables);

        return response()->json($this->apiResponse);
    }
    public function store(Request $request){
        $rules=[
            'time_start'=>'required|date_format:H:i',
            'time_end'=>'required|date_format:H:i',
            'event_date'=>'required|date',
            'user_id'=>'required|integer',
            'group_id'=>'required|integer',
            'location_id'=>'required|integer',
            'occupation_id'=>'required|integer',
        ];
        $this->validate($request,$rules);

        $time_start=$request->get('time_start');
        $time_end=$request->get('time_end');
        $event_date=$request->get('event_date');
        $user_id=$request->get('user_id');
        $group_id=$request->get('group_id');
        $location_id=$request->get('location_id');
        $occupation_id=$request->get('occupation_id');

        $rows = Timetable::check($time_start,$time_end,$event_date,$user_id,$group_id,$location_id);
        if(count($rows->get())>0){
            $this->apiResponse->setError(3, 'конфликт');
            return response()->json($this->apiResponse);
        }
        $create = [
            'time_start'=>$time_start,
            'time_end'=>$time_end,
            'event_date'=>$event_date,
            'user_id'=>$user_id,
            'group_id'=>$group_id,
            'location_id'=>$location_id,
            'occupation_id'=>$occupation_id,
        ];
        $timetable = Timetable::create($create);

        if(!$timetable){
            $this->apiResponse->setError(2, 'не удалось создать запись');
            return response()->json($this->apiResponse);
        }
        $this->apiResponse->setData('timetable_id', $timetable->id);

        return response()->json($this->apiResponse);
    }

    public function destroy($id){
        $object = Timetable::find($id);
        if(!$object){
            $this->apiResponse->setError(6, 'Объект не найден');
            return response()->json($this->apiResponse);
        }
        try {
            $object->delete();
        }catch (\Exception $e){
            $this->apiResponse->setError(7, 'Объект не удалось удалить');
            return response()->json($this->apiResponse);
        }
        return response()->json($this->apiResponse);
    }
    public function update(Request $request,$id){

        $rules=[
            'time_start'=>'required|date_format:H:i',
            'time_end'=>'required|date_format:H:i',
            'event_date'=>'required|date_format:Y-m-d',
            'user_id'=>'required|integer|exists:users,id',
            'group_id'=>'required|integer|exists:groups,id',
            'location_id'=>'required|integer|exists:locations,id',
            'occupation_id'=>'required|integer|exists:occupations,id',
        ];
        $this->validate($request,$rules);
        $object = Timetable::find($id);
        if(!$object){
            $this->apiResponse->setError(6, 'Объект не найден');
            return response()->json($this->apiResponse);
        }

        $res = $object->update($request->only(['time_start','time_end','event_date','user_id','group_id','location_id','occupation_id']));
        if(!$res){

            $this->apiResponse->setError(1, 'ошибка обновления объекта');

            return response()->json($this->apiResponse);
        }
        return response()->json($this->apiResponse);
    }
}
