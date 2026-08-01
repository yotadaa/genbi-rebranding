<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    protected $table = 'tbl_social';
    protected $primaryKey = 'social_id';
    public $timestamps = false;
    protected $guarded = [];
}
