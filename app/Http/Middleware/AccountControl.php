<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BaseController;
use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $feedback = new BaseController();
        $user = Auth::user();

        $permission = Permission::where('id',$user->role)->first();
        if ($permission->account==1 || $user->role==1){
            return $next($request);//continue because it's allowed
        }else{
            return back()->with('error','You do not have the permission to perform this action.');
        }
    }
}
