<?php

namespace MichaelT\Permy;

/**
 * Permissions filter
 *
 * @package michaeltintiuc/laravel-permy
 * @author  Michael Tintiuc
 **/
class PermyFilter
{
    /**
     * Base before filter
     * Check permissions on all routes to which this filter is applied to
     *
     * @param  Illuminate\Routing\Route $route
     * @param  Illuminate\Http\Request  $request
     * @param  mixed $param
     * @return Response
     **/
    public function filter($route, $request, $param=null)
    {
        // Check if user is logged-in first
        // Check if user is authorized to access this route
        if (\Auth::check() && !\Auth::user()->can($route))
        {
            return \Request::ajax() || \Request::wantsJson()
                ? \Response::json(['status' => 401, 'errors' => ['Unauthorized']], 401)
                : '401 - Forbidden';
        }
    }
}
