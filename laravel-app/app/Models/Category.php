<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'tbl_category';
    protected $primaryKey = 'category_id';
    protected $guarded = [];
    public $timestamps = false;

    public function news()
    {
        return $this->hasMany(News::class, 'category_id', 'category_id');
    }
}
