<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(){
        $locations = Location::all();

        $this->apiResponse->setData(null, $locations);

        return response()->json($this->apiResponse);
    }
    public function store(Request $request){

        $title = $request->get('title');
        $description = $request->get('description');

        $location = Location::create([
            'title'=>$title,
            'description'=>$description,
        ]);
        if(!$location){

            $this->apiResponse->setError(1, 'ошибка создания объекта');

            return response()->json($this->apiResponse);
        }

        $this->apiResponse->setData('location_id', $location->id);

        return response()->json($this->apiResponse);
    }
    public function destroy($id){
        $location = Location::find($id);
        if(!$location){
            $this->apiResponse->setError(6, 'Объект не найден');
            return response()->json($this->apiResponse);
        }
        try {
            $res = $location->delete();
        }catch (\Exception $e){
            $this->apiResponse->setError(7, 'Объект не удалось удалить');
            return response()->json($this->apiResponse);
        }
        return response()->json($this->apiResponse);
    }
    public function update(Request $request,$id){

        $location = Location::find($id);
        if(!$location){
            $this->apiResponse->setError(6, 'Объект не найден');
            return response()->json($this->apiResponse);
        }

        $res = $location->update($request->only(['title','description']));
        if(!$res){

            $this->apiResponse->setError(1, 'ошибка обновления объекта');

            return response()->json($this->apiResponse);
        }
        return response()->json($this->apiResponse);
    }
}
