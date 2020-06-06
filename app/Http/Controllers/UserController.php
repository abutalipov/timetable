<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(){
        $users=User::all();

        $this->apiResponse->setData(null, $users);
        return response()->json($this->apiResponse);
    }
        public function store(Request $request){
            $rules=[
                'first_name'=>'required|string',
                'second_name'=>'required|string',
                'patronymic'=>'sometimes|string',
                'email'=>'required|email',
                'password'=>'required|string',
            ];
            $this->validate($request,$rules);
            $user = Auth::user();
            if($user->id!=1){
                $this->apiResponse->setError(5, 'не достаточно прав');
                return response()->json($this->apiResponse);
            }
            $create=[
              'first_name'=>$request->get('first_name'),
              'second_name'=>$request->get('second_name'),
              'patronymic'=>$request->get('patronymic',null),
              'email'=>$request->get('email'),
              'password'=>$request->get('password'),
            ];
            $user = User::create($create);
            if(!$user){
                $this->apiResponse->setError(6, 'не удалось создать юзера');
                return response()->json($this->apiResponse);
            }
            $this->apiResponse->setData('user_id', $user->id);
            return response()->json($this->apiResponse);
        }
    public function destroy($id){
        if($id==1){
            $this->apiResponse->setError(9, 'Запрещено!');
            return response()->json($this->apiResponse);
        }
        $object = User::find($id);
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
            'first_name'=>'sometimes|string',
            'second_name'=>'sometimes|string',
            'patronymic'=>'sometimes|string',
            'email'=>'sometimes|email',
            'password'=>'sometimes|string',
        ];
        $this->validate($request,$rules);
        $object = User::find($id);
        if(!$object){
            $this->apiResponse->setError(6, 'Объект не найден');
            return response()->json($this->apiResponse);
        }

        $res = $object->update($request->only(['first_name','second_name','patronymic','email','password']));
        if(!$res){

            $this->apiResponse->setError(1, 'ошибка обновления объекта');

            return response()->json($this->apiResponse);
        }
        return response()->json($this->apiResponse);
    }
}
