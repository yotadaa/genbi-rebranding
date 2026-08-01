<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureImage extends Model
{
    protected $table = 'tbl_feature_image';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
