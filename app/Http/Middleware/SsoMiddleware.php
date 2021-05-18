<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;

class SsoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $userInfo = Session::get('user_login');
        if ($userInfo) {
            // 获取 Cookie 中的 token
            $singletoken = $request->cookie('single_token');
            if ($singletoken) {
                // 从 Redis 获取 time
                $redisTime = Redis::get("single_token_" . $userInfo['name']);
                // 重新获取加密参数加密
                $secret = md5($userInfo['name'].$request->userAgent().$redisTime. $request->ip());
                if ($singletoken != $secret) {
                    Session::forget('user_login');
                    Auth::logout();
                    return redirect('/login')->with(['Msg' => '您的帐号在另一个地点登录..']);
                }
                return $next($request);
            } else {
                return redirect('/login');
            }
        } else {
            return redirect('/');
        }
    }
}
