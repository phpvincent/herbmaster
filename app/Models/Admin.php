<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable implements JWTSubject
{
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['username', 'password','show_name', 'is_root','admin_group'];
    use Notifiable;

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

//    public function getAuthIdentifierName()
//    {
//        return 'id';
//    }

    public function group()
    {
        return $this->belongsTo(AdminGroup::class, 'admin_group','id');
    }
}
