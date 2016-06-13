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
        // TODO cookie 中有 access token 时，使用 access token 访问 API，
        // 否则跳转到open授权页面
        $access_token = $request->session()->get('access_token');
        if (!$access_token)
        {
            $redirect_uri = 'http://gift.radision.biz';
            $code = $request->input('code');
            $oauth_server = config('oauth.server');
            if (!$code)
            {
                $oauth_home = config('oauth.home');
                $authorize = config('oauth.authorize');
                $client_id = config('oauth.client_id');
                $params = array(
                    'response_type'     => 'code',
                    'client_id'         => $client_id,
                    'state'             => md5($client_id.$authorize.$oauth_server.time()),
                    'redirect_uri'      => $redirect_uri,
                );
                $params_str = http_build_query($params);
                $url = "{$oauth_server}{$home}?{$params_str}";
                return redirect($url);
            }
            $post_data = array(
                'grant_type'        => 'authorization_code',
                'client_id'         => config('oauth.client_id'),
                'client_secret'     => config('oauth.client_secret'),
                'redirect_uri'      => $redirect_uri,
                'code'              => $code,
            );

            $access_token_url = config('oauth.access_token');
            $fetch_access_token_url = $oauth_server.$access_token_url;

            $client = new \GuzzleHttp\Client();
            $res = $client->request('POST', $fetch_access_token_url, array('json' => $post_data), array('verify' => false));

            $code = $res->getStatusCode();
            if ($code == 200)
            {
                $body = $res->getBody();
                $content = $body->getContents();
                $data = json_decode($content, true);
                $data['expired'] = time() + $data['expires_in'];

                $request->session()->put('access_token', serialize($data));
            }
        }
        return $next($request);
    }

}
