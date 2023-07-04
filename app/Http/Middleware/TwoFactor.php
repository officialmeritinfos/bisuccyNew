<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BaseController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $feedback = new BaseController();
        $user = Auth::user();
        if ($user->twoFactor !=1){
            return $next($request);
        }elseif ($user->twoFactor ==1 && $user->twoWayPassed == 1){
            return $next($request);
        }else{
            return $feedback->sendError('login.error',
                ['error'=>'2FA active on account and must be completed','401']);
        }
    }
}
