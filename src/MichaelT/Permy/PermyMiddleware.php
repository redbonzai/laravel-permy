<?php
namespace MichaelT\Permy;

use Closure;

/**
 * Permissions middleware
 *
 * @package michaeltintiuc/laravel-permy
 * @author  Michael Tintiuc
 **/
class PermyMiddleware
{
    public function handle($request, Closure $next)
    {
        // Check if user is logged-in first
        // Check if user is authorized to access this route
        if (\Auth::check() && !\Permy::can($request->route())) {
            return \Request::ajax() || \Request::wantsJson()
                ? \Response::json(['status' => 401, 'errors' => ['Unauthorized']], 401)
                : \Response::make('401 - Forbidden', 401);
        }

        return $next($request);
    }
}
