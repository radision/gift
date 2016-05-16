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
        // 包含 HTTP_AUTHORIZE ，做为access token使用，
        // 否则跳转到open授权页面
        $access_token = $request->cookie('access_token');
        if (!$access_token)
        {
            $oauth_server = config('oauth.server');
            $authorize = config('oauth.authorize');
            echo "oauth_server = $oauth_server\n";
            echo "authorize = $authorize\n";
            exit();
            // return redirect();
        }
    }

}
