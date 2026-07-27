<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestasiToken extends Model
{
    protected $table = 'tbl_prestasi_submission_token';
    protected $primaryKey = 'id';
    protected $guarded = [];
}
