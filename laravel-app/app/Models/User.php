<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'tbl_user';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'remember_token_hash',
        'token',
    ];

    /**
     * The column used as the "remember me" token.
     * We override it since the table uses remember_token_hash instead of remember_token.
     * Return null to disable remember-me token storage in this column.
     */
    public function getRememberTokenName(): ?string
    {
        // The table doesn't have a standard remember_token column; disable it.
        return null;
    }


    /**
     * Get the password for authentication.
     */
    public function getAuthPassword(): string
    {
        return $this->password ?? '';
    }
}
