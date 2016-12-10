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
     * Base filter
     * Check permissions on all routes to which this filter is applied to
     *
     * @return default or custom response
     **/
    public function filter($route, $request)
    {
        // Check if user is logged-in first
        // Check if user is authorized to access this route
        // User PermyTrait@can
        if (\Auth::check() && !\Auth::user()->can($route))
        {
            // return a custom response
            if (method_exists($this, 'getCustomResponse'))
                return $this->getCustomResponse($route, $request);

            // return a default response
            return \Request::ajax() || \Request::wantsJson()
                ? \Response::json(['status' => 401, 'errors' => ['Unauthorized']], 401)
                : '401 - Forbidden';
        }
    }

    /**
     *
     * @return custom response
     **/
    public function getCustomResponse($route, $request)
    {
        //
    }
}
