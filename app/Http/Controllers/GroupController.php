<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(){
        $groups = Group::all();

        $this->apiResponse->setData(null, $groups);

        return response()->json($this->apiResponse);
    }
    public function store(Request $request){

        $title = $request->get('title');
        $description = $request->get('date');

        $group = Group::create([
            'title'=>$title,
            'date'=>$description,
        ]);
        if(!$group){

            $this->apiResponse->setError(1, 'ошибка создания группы');

            return response()->json($this->apiResponse);
        }

        $this->apiResponse->setData('group_id', $group->id);

        return response()->json($this->apiResponse);
    }
    public function destroy($id){
        $object = Group::find($id);
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

        $object = Group::find($id);
        if(!$object){
            $this->apiResponse->setError(6, 'Объект не найден');
            return response()->json($this->apiResponse);
        }

        $res = $object->update($request->only(['title','date']));
        if(!$res){

            $this->apiResponse->setError(1, 'ошибка обновления объекта');

            return response()->json($this->apiResponse);
        }
        return response()->json($this->apiResponse);
    }
}
