<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotFilamentAdmin 
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guard = Filament::auth();
        $user = $guard->user();

        if ($user->role == 'employee' && Filament::getPanel('employee')->getPath() != $request->path()) {
            return redirect('/employee');
        }

        return $next($request);
    }
}