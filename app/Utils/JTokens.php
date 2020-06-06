<?php
namespace App\Http\Utils;

use Exception;

class JTokens
{
    private $secretKey = '';
    private $algorythm;
    private $type;
    private $payload = [];
    private $urlSafe = true;

    /**
     * this option affects how much time will be given to the token
     * strict - 1 day
     * middle - 1 week
     * low - 1 month
     * */
    private $expireMode = 'middle';

    /**
     * this prop is used to generate expires payload when not null.
     * Probably will be used as default flag in next versions
     *
     * @var int|null
     */
    private $expiresTs = null;

    /**
     * The next two vars are intended to be used in the future
     * when we will be supporting more algorythms and token types
     *
     * @var array
     */
    private static $algorythmsMap = [
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512'
    ];

    private static $supportedTokenTypes = [
        'JWT'
    ];

    /**
     * Initialize the token object
     *
     * @param string $algorythm Algorythm
     * @param string $type Type
     * @param string $expireMode Expire mode
     * @throws Exception
     */
    public function __construct($algorythm = 'HS256', $type = 'JWT', $expireMode = 'strict')
    {
        $algorythm = strtoupper($algorythm);

        if (!array_key_exists($algorythm, self::$algorythmsMap)) {
            throw new Exception('Sorry, the provided algorythm is not supported at the moment');
        }

        if (!in_array($type, self::$supportedTokenTypes)) {
            throw new Exception('Sorry, the provided token type is not supported at the moment');
        }

        $this->algorythm = $algorythm;
        $this->type = strtoupper($type);
        $this->expireMode = $expireMode;
    }

    /**
     * Makes a JWT token URL safe by replacing symbols / and +
     *
     * @param bool $safe safety flag
     * @return void
     */
    public function setUrlSafe($safe)
    {
        $this->urlSafe = $safe;
    }

    /**
     * Sets the secret key
     *
     * @param string $key The key
     * @return void
     */
    public function setKey(String $key)
    {
        $this->secretKey = $key;
    }

    /**
     * Sets the payload data for the token
     *
     * @param array $payload Array of the payload data
     * @return void
     */
    public function setPayload(Array $payload = [])
    {
        $this->payload = $payload;
    }

    /**
     * Encrypts the header of our JWT.
     * For now it only returns very basic header such as
     * {"alg": "HS256", "typ": "JWT"}
     *
     * @return string
     */
    private function makeHeader()
    {
        $data = [
            'alg' => $this->algorythm,
            'typ' => $this->type
        ];

        $json = json_encode($data);

        return $this->encode64($json);
    }

    /**
     * Sets expires mode
     *
     * @param string $mode Sets one of predefined modes (strict|middle|low)
     * @return void
     */
    public function setExpireMode($mode)
    {
        $this->expireMode = $mode;
    }

    /**
     * Sets $this->expires based on the value of
     * $period
     *
     * @param string $period A period in Human readable format. Ex.: "+ 10 minutes" or "+ 2 hours" etc.
     * @return void
     */
    public function setExpiresTsHr($period)
    {
        $this->expiresTs = strtotime($period, time());
    }

    /**
     * Returns a string which represents a timestamp when
     * the token must be invalidated. The returning value is based on
     * the value of $this->expireMode
     *
     * @return string A timestamp value
     */
    private function getExpires()
    {
        if (!is_null($this->expiresTs) && is_numeric($this->expiresTs)) {
            return $this->expiresTs;
        }

        // This is quite a long period. So, lets think that the token never expires
        $expires = strtotime("+ 10 years", time());

        switch ($this->expireMode) {
            case 'strict':
                $expires = strtotime("+ 1 day", time());
                break;
            case 'middle':
                $expires = strtotime("+ 1 week", time());
                break;
            case 'low':
                $expires = strtotime("+ 1 month", time());
                break;
        }

        return $expires;
    }

    /**
     * Encodes payload
     *
     * @return string
     */
    private function makePayload()
    {
        $this->payload['exp'] = $this->getExpires();
        $json = json_encode($this->payload);

        return $this->encode64($json);
    }

    /**
     * Creates a JWT token
     *
     * @return string
     * @throws Exception
     */
    public function makeToken()
    {
        if (empty($this->secretKey)) {
            throw new Exception('The secret key is empty. Can not make a token');
        }

        $header = $this->makeHeader();
        $payload = $this->makePayload();
        $hash = hash_hmac($this->getAlgorythm(), $header . '.' . $payload, $this->secretKey, true);
        $signature = $this->encode64($hash);

        return $header . '.' . $payload . '.' . $signature;
    }

    /**
     * Splits a token and checks that all its parts are valid
     *
     * @param string $token a JWT token
     * @return array
     * @throws Exception
     */
    public static function splitToken($token)
    {
        $parts = explode(".", trim($token));

        /**
         * @TODO: add regex check if a part is base64 encoded
         * ^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{2}==)?$
         * https://stackoverflow.com/questions/8571501/how-to-check-whether-a-string-is-base64-encoded-or-not
         */
        if (count($parts) !== 3) {
            throw new Exception('Wrong token format');
        }

        return $parts;
    }

    /**
     * Returns token's payload without validating it
     *
     * @param string $token A JWT token
     * @return array
     * @throws Exception
     */
    public static function getTokenPayload($token)
    {
        $parts = self::splitToken($token);
        $payload = base64_decode(self::base64Compat($parts[1]));
        $payload = json_decode($payload, true);

        if (!is_array($payload)) {
            throw new Exception(__('Wrong token format'));
        }

        return $payload;
    }

    /**
     * Validates provided token using provided $key
     * Checks token for expiration
     *
     * @param string $token A JWT token
     * @param string $key a key that was used to encrypt the token
     * @param string $algo
     * @return bool
     * @throws Exception
     */
    public static function validateToken($token, $key, $algo = 'HS256')
    {
        $parts = self::splitToken($token);
        $headerToken = $parts[0];
        $payloadToken = $parts[1];
        $payload = self::getTokenPayload($token);

        $algorythm = self::mapAlgorythm($algo);

        $signature = base64_encode(hash_hmac($algorythm, $headerToken . '.' . $payloadToken, $key, true));

        if (!empty($payload['exp']) && $payload['exp'] <= time()) {
            return false;
        }

        return hash_equals(self::base64Compat($parts[2]), $signature);
    }

    /**
     * Generates a hash which can be used for different purposes
     *
     * @param string $prefix Concat the hash with this prefix
     * @param string $algo The algo that this method will use to generate a hash
     * @param int $numBytes The number of random bytes
     * @return string
     */
    public static function generateHash($prefix = '', $algo = 'sha256', $numBytes = 10000)
    {
        return $prefix . hash($algo, openssl_random_pseudo_bytes($numBytes) . openssl_random_pseudo_bytes($numBytes));
    }

    /**
     * Rebuilds a URL compatible base64_encoded string into a native one
     *
     * @param string $encString base64url_encoded string
     * @return string
     * @throws Exception
     */
    public static function base64Compat(String $encString)
    {
        $encString = str_replace('=', '', $encString);
        $encString = str_replace(['-', '_'], ['+', '/'], $encString);

        switch (strlen($encString) % 4) {
            case 0:
                break;
            case 2:
                $encString .= '==';
                break;
            case 3:
                $encString .= '=';
                break;
            default:
                throw new Exception('The string is not Base64 encoded');
        }

        return $encString;
    }

    /**
     * Makes a base64_encoded string URL safe
     *
     * @param string $encString base64_encoded string
     * @return string
     */
    public static function base64UrlSafe(String $encString)
    {
        $encString = str_replace('=', '', $encString);
        $encString = str_replace(['+', '/'], ['-', '_'], $encString);

        return $encString;
    }

    /**
     * Encodes into a base64 string based on URL safety flag
     *
     * @param string $str string to encode
     * @return string
     */
    private function encode64(String $str)
    {
        return ($this->urlSafe) ? self::base64UrlSafe(base64_encode($str)) : base64_encode($str);
    }

    /**
     * Returns the algo of current instance
     *
     * @return string
     */
    public function getAlgorythm()
    {
        return self::$algorythmsMap[$this->algorythm];
    }

    /**
     * Get hashing algorythm name from its JWT representation
     *
     * @param string $algorythm The algorythm
     *
     * @return string
     */
    public static function mapAlgorythm(String $algorythm)
    {
        return self::$algorythmsMap[$algorythm];
    }
}
