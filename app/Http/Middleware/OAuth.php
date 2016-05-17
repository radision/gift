<?php

namespace App\Http\Middleware;

use Closure;

class OAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        // cookie 中有 access token 时，使用 access token 访问 API，
        // 否则跳转到open授权页面
        $access_token = $request->cookie('access_token');
        if (!$access_token)
        {
            $oauth_server = config('oauth.server');
            $authorize = config('oauth.authorize');
            $client_id = config('oauth.client_id');
            $params = array(
                'response_type'     => 'code',
                'client_id'         => $client_id,
                'state'             => md5($client_id.$authorize.$oauth_server.time()),
                'redirect_uri'      => 'http://gift.radision.biz',
            );
            $params_str = http_build_query($params);
            $url = "{$oauth_server}{$authorize}?{$params_str}";
            return redirect($url);
        }
    }

}
