<?php

namespace App\Http\Controllers;

use App\Models\Occupation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OccupationController extends Controller
{
    public function index(){
        $occupations = Occupation::all();

        $this->apiResponse->setData(null, $occupations);

        return response()->json($this->apiResponse);
    }
    public function store(Request $request){

        $title = $request->get('title');
        $slug=Str::slug($title);
        $occupation = Occupation::create([
            'slug'=>$slug,
            'title'=>$title,
        ]);
        if(!$occupation){

            $this->apiResponse->setError(1, 'ошибка создания дисциплины');

            return response()->json($this->apiResponse);
        }

        $this->apiResponse->setData('occupation_id', $occupation->id);

        return response()->json($this->apiResponse);
    }
    public function destroy($id){
    $object = Occupation::find($id);
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

        $object = Occupation::find($id);
        if(!$object){
            $this->apiResponse->setError(6, 'Объект не найден');
            return response()->json($this->apiResponse);
        }
        $title = $request->get('title');
        $slug=Str::slug($title);
        $res = $object->update([
            'slug'=>$slug,
            'title'=>$title,
        ]);
        if(!$res){

            $this->apiResponse->setError(1, 'ошибка обновления объекта');

            return response()->json($this->apiResponse);
        }
        return response()->json($this->apiResponse);
    }
}
