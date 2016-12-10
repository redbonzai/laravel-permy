<?php

namespace MichaelT\Permy;

/**
 * Permy Model
 *
 * @return void
 * @author Michael Tintiuc
 **/
class PermyModel extends \Eloquent
{
    protected $table = 'permy';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany(\Config::get('auth.model'), 'permy_user', 'permy_id', 'user_id');
    }

    public function scopeGetList($query, $merge=['' => 'NOT SET'])
    {
        return $merge + $query->orderBy('name', 'asc')->lists('name', 'id');
    }

    /**
     * Get the permission value based on controller/method
     * Returns value represents the index of [Restrict, Allow]
     *
     * @param string $controller
     * @param string $method
     * @return integer
     **/
    public function getPermission($controller, $method)
    {
        $controller = snake_case(str_replace('Controller', '', $controller));

        try
        {
            // Assume permission is set
            return (int) json_decode($this->$controller)->$method;
        }
        catch (Exception $e)
        {
            return 0;
        }
    }
}
