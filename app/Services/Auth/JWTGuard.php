<?php

namespace App\Services\Auth;

use App\Http\Utils\JTokens;
use App\Models\Token;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class JWTGuard implements Guard
{
    use GuardHelpers;

    private $request;
    /**
     * The currently authenticated user.
     *
     * @var Authenticatable|User
     */
    protected $user;
    private $tokenModel;
    private $c1User;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->request = $request;
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return Authenticatable|null
     * @throws Exception
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        $this->user = $this->getUserByToken();

        if ($this->user !== null) {
            $this->user->setCurrentSession($this->getTokenModel()->userSession);
        }

        return $this->user;
    }

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenFromHeader()
    {
        return $this->request->headers->get('Authorization', null);
    }

    /**
     * Set the current request instance.
     *
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return Authenticatable|null
     * @throws Exception
     */
    public function getUserByToken()
    {
        $appKey = $this->request->query('key');
        $authHeader = $this->getTokenFromHeader();

        // @TODO Потом перенести в коллекцию регулярок
        $validPathPattern = '/file\/[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\?key\=/i';

        preg_match($validPathPattern, $this->request->getRequestUri(), $matches);

        if ($appKey && count($matches)) {
            $item = Token::where('app', $appKey)->get(['user_id', 'exp'])->first();

            if (!$item) {
                throw new HttpException(403, 'Токен недействителен');
            }

            $userId = $item->getAttributes()['user_id'];
            $exp = $item->getAttributes()['exp'];

            if (!empty(strtotime($exp)) && strtotime($exp) <= time()) {
                throw new HttpException(403, 'Токен неактуален');
            }

            return $this->provider->retrieveById($userId);
        }

        if (is_null($authHeader)) {
            throw new HttpException(400, 'No authorization header.');
        }

        $payload = JTokens::getTokenPayload($authHeader);

        $appHash = !empty($payload['app']) ? filter_var($payload['app'], FILTER_SANITIZE_STRING) : null;

        $tokenModel = Token::findUserTokenByAppHash($appHash);
        $token = $tokenModel->token ?? null;
        $isValidToken = JTokens::validateToken($authHeader, $token);

        if (!$isValidToken) {
            throw new HttpException(403, 'Invalid or inactive token.');
        }

        $this->setTokenModel($tokenModel);

        return $this->provider->retrieveById($tokenModel->user->id);
    }

    public function logout()
    {
        if (is_null($this->user)) {
            return false;
        }

        $this->getTokenModel()->userSession()->update([
            'logout_at' => Carbon::now(),
        ]);

        $this->getTokenModel()->delete();

        return true;
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        // TODO: Implement validate() method.
    }

    /**
     * @param $id
     * @return bool|Authenticatable
     */
    public function loginUsingId($id)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            return $this->user = $user;
        }

        return false;
    }

    /**
     * @param Token $tokenModel
     */
    public function setTokenModel($tokenModel): void
    {
        $this->tokenModel = $tokenModel;
    }

    /**
     * @return Token
     */
    public function getTokenModel()
    {
        return $this->tokenModel;
    }


    public function getPayload()
    {
        $authHeader = $this->getTokenFromHeader();
        $payload = JTokens::getTokenPayload($authHeader);
        return $payload;
    }


}
