<?php

namespace App\Http\Controllers;

use App\Http\Utils\JTokens;
use App\Models\Token;
use App\Models\User;
use App\Models\UserSession;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthController extends Controller
{
    /**
     * Login
     *
     * @param Request $request
     * @param JTokens $jwt
     * @return JsonResponse
     * @throws Exception
     */
    public function login(Request $request, JTokens $jwt)
    {
        $rules = [
            'login' => 'required|string',
            'password' => 'required|string',
        ];

        $this->validate($request, $rules);

        $user = User::where('email',$request->login)->where('password',$request->password)->first();


        $user = Auth::loginUsingId($user->getAuthIdentifier());
        UserSession::create([
            'user_id' => $user->id,
        ]);
        $appName = JTokens::generateHash();
        $tokenKey = JTokens::generateHash();

        $jwt->setKey($tokenKey);
        $jwt->setExpireMode('low');

        $jwt->setPayload([
            'app' => $appName,
        ]);

        $token = $jwt->makeToken();
        $payload = JTokens::getTokenPayload($token);
        $expiration = $payload['exp'];

        $tokenModel = Token::create([
            'user_id' => $user->id,
            'token' => $tokenKey,
            'app' => $appName,
            'exp' => date("Y-m-d H:i:s", $expiration)
        ]);

        $userSessionModel = UserSession::whereUserId($user->id)
            ->whereNull('token_id')
            ->latest('login_at')
            ->first();

        $userSessionModel->update([
            'token_id' => $tokenModel->id,
            'user_model_stamp' => $user->toJson(),
            'login_at' => Carbon::now(),
        ]);

        UserSession::whereUserId($user->id)
            ->whereNull('token_id')
            ->delete();

        $this->apiResponse->setData('token', $token);

        return response()->json($this->apiResponse);
    }

    public function logout()
    {
        Auth::logout();

        return response()->json($this->apiResponse);
    }


}
