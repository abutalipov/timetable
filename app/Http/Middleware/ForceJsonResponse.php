<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForceJsonResponse
{
    /**
     * Всегда отдаем JSON. Проверяем заголовок на JSON.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        $contentType = $request->headers->get('Content-type');

        if ($request->routeIs('file.store') && mb_stristr($contentType, 'multipart/form-data')) {
            return $next($request);
        }

        if ($contentType !== 'application/json') {
            throw new HttpException(400, 'Wrong request content type. Use JSON headers.');
        }

        return $next($request);
    }
}
