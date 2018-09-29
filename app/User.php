<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles', 'users_id', 'roles_id');
    }
}
