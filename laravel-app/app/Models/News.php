<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class News extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_news';
    protected $primaryKey = 'news_id';
    protected $guarded = [];
    public $timestamps = false;

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function scopePublished($query)
    {
        return $query->where(function($q) {
            $q->whereNull('status')->orWhere('status', 'published');
        });
    }

    public function scopeLatestNews($query)
    {
        return $query->orderByRaw('COALESCE(published_at, news_date) DESC');
    }

    public function scopeForAdmin($query)
    {
        return $query;
    }

    public function resolveImageUrl(string $path)
    {
        $path = trim($path);
        if ($path === '') return '';
        if (preg_match('#^https?://#i', $path)) return $path;
        $path = preg_replace('#^/?uploads/#i', '', $path) ?? $path;
        return url('/uploads/' . ltrim($path, '/'));
    }

    public function getSlugAttribute($value)
    {
        if ($value) {
            return $value;
        }
        return Str::slug($this->news_title) . '-' . $this->news_id;
    }
}
