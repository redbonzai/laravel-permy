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
        return $this->belongsToMany('App\User', 'permy_user', 'permy_id', 'user_id');
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
        $controller = \Permy::formatControllerName($controller);
        $permission_obj = json_decode($this->{$controller});

        return isset($permission_obj->{$method})
            ? $permission_obj->{$method}
            : 0;
    }
}
