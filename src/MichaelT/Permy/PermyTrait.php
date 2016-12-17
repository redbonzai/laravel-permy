<?php
namespace MichaelT\Permy;

/**
 * Checks if the user has permissions for the provided route
 * To be used with the User Model
 *
 * @package michaeltintiuc/laravel-permy
 * @author Michael Tintiuc
 **/
trait PermyTrait
{
    /**
     * Define the relationship between the user and permission
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function permy()
    {
        return $this->belongsToMany('MichaelT\Permy\PermyModel', 'permy_user', 'user_id', 'permy_id');
    }
}
