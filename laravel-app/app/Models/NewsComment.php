<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsComment extends Model
{
    protected $table = 'tbl_news_comment';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function news()
    {
        return $this->belongsTo(News::class, 'news_id', 'news_id');
    }

    public function children()
    {
        return $this->hasMany(NewsComment::class, 'parent_id', 'id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
