<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_feature';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $casts = [
        'show_on_home' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function images()
    {
        return $this->hasMany(FeatureImage::class, 'feature_id')->orderBy('sort_order', 'asc');
    }

    public function scopeHomeVisible($query, $limit = 12)
    {
        return $query->where('status', 'published')
                     ->where('show_on_home', 1)
                     ->orderBy('sort_order', 'asc')
                     ->take($limit)
                     ->get();
    }
}
