<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhotoGallery extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_photo_gallery';
    protected $primaryKey = 'photo_id';
    protected $guarded = [];
}
