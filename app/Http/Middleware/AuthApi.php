<?php
/**
 * Created by PhpStorm.
 * User: alvarobanofos
 * Date: 23/5/16
 * Time: 16:01
 */

namespace App\Http\Middleware;


use App\RestApiModels\User;

class AuthApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, \Closure $next, $guard = null)
    {
        $headers = $request->header();

        if(!array_key_exists("content-type", $headers) || $headers["content-type"][0] != "application/json") {
            return response('Bad Request.', 400);
        }

        if(!array_key_exists("auth-key", $headers) || !array_key_exists("auth-token", $headers)) {
            return response('Unauthorized.', 401);
        }

        $key = $headers["auth-key"][0];
        $token = $headers["auth-token"][0];

        $user = User::where("auth_key", $key)->where("auth_token", $token)->first();


        if(!$user) {
            return response('Unauthorized.', 401);

        }

        \Session::put('user', $user);

        return $next($request);
    }
}