<?php
namespace App\Http;

interface ApiResponseInterface
{
    public function setStatus($status);
    public function setCode($code);
    public function setMessage($message);
    public function setData($key, $data);
    public function setError($code, $message);
}
