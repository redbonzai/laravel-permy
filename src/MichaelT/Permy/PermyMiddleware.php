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
                ? \Response::json(['status' => 403, 'errors' => ['Unauthorized']], 403)
                : \Response::make('403 - Forbidden', 403);
        }

        return $next($request);
    }
}
