<?php

namespace Orbitali\Http\Middleware;

use Orbitali\Foundations\ResponseSerializer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class CacheRequest
{
    /**
     * Handle the request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws
     */
    public function handle($request, $next)
    {
        if ($this->shouldCacheRequest($request)) {
            $key = $this->getCacheKey($request);

            if (Cache::has($key)) {
                $response = Cache::get($key);
                return (new ResponseSerializer())->unSerialize($response);
            }

            $response = $next($request);
            if ($this->shouldCacheResponse($response)) {
                Cache::put($key, (new ResponseSerializer())->serialize($response), 60);
            }
            return $response;
        }
        return $next($request);
    }

    private function shouldCacheRequest($request): bool
    {
        return !$request->ajax() && $request->isMethod('get');
    }

    private function getCacheKey($request)
    {
        $arrayExceptingItems = ["_previous", '_flash'];
        if (Auth::guest()) {
            $arrayExceptingItems[] = "_token";
        }
        return "orbitali.cache.middleware." .
            mb_strtolower($request->getMethod()) . "." .
            hash("md4", $request->fullUrl() . "#" . app()->getLocale() .
                serialize(
                    array_except(
                        Session::all(), $arrayExceptingItems)));
    }

    private function shouldCacheResponse($response): bool
    {
        return $response->isSuccessful() || $response->isRedirection();
    }
}
