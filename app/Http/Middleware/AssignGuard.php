<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Tymon\JWTAuth\Facades\JWTAuth;

class AssignGuard extends Middleware
{

    /**
     * Exclude these routes from authentication check.
     *
     * @var array
     */
    protected $except = [];


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {

            foreach ($this->except as $excluded_route) {
                if ($request->path() === $excluded_route) {

                    logger("Skipping $excluded_route from auth check...");
                    return $next($request);
                }
            }

            logger('Authenticating... ' . $request->url());

            JWTAuth::parseToken()->authenticate();

            $guards = empty($guards) ? [null] : $guards;

            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    return $next($request);
                }
            }
            return response()->json([
                'success' => false,
                'message' => __('app.app.not_have_permission'),
            ]);

        } catch (\Exception $e) {
            logger('Token is Invalid'. $request->url());

            return response()->json([
                "success" => false,
                "message" => 'Token is Invalid'
            ]);
        }
    }
}
