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
            $redirect_uri = 'http://gift.radision.biz';
            $code = $request->input('code');
            $oauth_server = config('oauth.server');
            if (!$code)
            {
                $authorize = config('oauth.authorize');
                $client_id = config('oauth.client_id');
                $params = array(
                    'response_type'     => 'code',
                    'client_id'         => $client_id,
                    'state'             => md5($client_id.$authorize.$oauth_server.time()),
                    'redirect_uri'      => $redirect_uri,
                );
                $params_str = http_build_query($params);
                $url = "{$oauth_server}{$authorize}?{$params_str}";
                return redirect($url);
            }
            $post_data = array(
                'grant_type'        => 'authorization_code',
                'client_id'         => config('oauth.client_id'),
                'client_secret'     => config('oauth.client_secret'),
                'redirect_uri'      => $redirect_uri,
                'code'              => $code,
            );
            $access_token = config('oauth.access_token');
            $fetch_access_token_url = $oauth_server.$access_token;
            $handle = curl_init($fetch_access_token_url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
            $resp = curl_exec($handle);
            echo $resp;exit();
            if (curl_errno($handle))
            {
                echo curl_error($handle);
                exit();
            }
            curl_close();
            $result = json_decode($resp, true);
            print_r($result);exit();
        }
    }

}
