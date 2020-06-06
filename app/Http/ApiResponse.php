<?php

namespace App\Http;

use DateTime;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use JsonSerializable;

/**
 * Класс для формирования json ответов
 * @package App\Http
 */
class ApiResponse implements ApiResponseInterface, JsonSerializable
{

    private $status = 'ok';
    private $apiCode = null;
    private $httpCode = 200;
    private $message = null;
    private $data = null;
    /**
     * @var LengthAwarePaginator
     */
    private $paginator = null;

    /**
     * Sets the "ok" or "error" status
     *
     * @param string $status Status
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Sets an internal status code
     *
     * @param string $code internal API status code
     * @return void
     */
    public function setCode($code)
    {
        $this->apiCode = $code;
    }

    /**
     * Sets an HTTP code for the Response
     *
     * @param string $code HTTP code
     * @return void
     */
    public function setHttpCode($code)
    {
        $this->httpCode = $code;
    }

    /**
     * will be removed
     *
     * @return int
     * @deprecated
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Sets a message for the Response
     *
     * @param string $message A message
     * @return void
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Adds data with the specified key
     *
     * @param string|null $key The key in which the data will be stored
     * @param string|int|array|null $data Data
     * @return void
     */
    public function setData($key, $data)
    {
        if ($data instanceof LengthAwarePaginator) {
            $this->paginator = $data;
            return;
        }

        if ($key === null) {
            $this->data = $data;
            return;
        }

        $this->data[$key] = $data;
        return;
    }

    /**
     * Sets the error flag and adds a correcponding code
     * and message
     *
     * @param int $code API code
     * @param string|array $message the Description of the error
     * @return void
     */
    public function setError($code, $message)
    {
        $this->status = 'error';
        $this->apiCode = $code;
        $this->message = $message;
    }

    /**
     * Compiles the API response values into a message
     *
     * @return array
     * @throws Exception
     */
    public function jsonSerialize()
    {
        $response = [];

        $response['status'] = $this->status;

        if ($this->apiCode !== null) {
            $response['api_code'] = $this->apiCode;
        }

        if ($this->message !== null) {
            $response['message'] = $this->message;
        }

        if ($this->data !== null) {
            $response['data'] = $this->data;
        }

        if ($this->paginator !== null) {
            $response = array_merge($response, $this->paginator->toArray());
        }

        $response['ts'] = (new DateTime())->format(DateTime::ATOM);

        return $response;
    }
}
