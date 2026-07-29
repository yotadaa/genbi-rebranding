<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestasiToken extends Model
{
    protected $table = 'tbl_prestasi_submission_token';
    protected $primaryKey = 'token_id';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'revoked_at' => 'datetime',
        'used_at'    => 'datetime',
    ];
}
