<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'tbl_user';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function getAuthIdentifierName()
    {
        return 'email';
    }
}
