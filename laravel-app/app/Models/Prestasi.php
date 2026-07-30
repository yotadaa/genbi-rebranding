<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prestasi extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_prestasi';
    protected $primaryKey = 'prestasi_id';
    protected $guarded = [];
    public $timestamps = false;

    public function getNameAttribute()
    {
        return $this->member_name;
    }

    public function getContentAttribute()
    {
        return $this->detail;
    }

    /** Resolve legacy local paths and Google Drive share URLs into renderable images. */
    public function resolveImageUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') return '';

        if (preg_match('#(?:drive|docs)\.google\.com#i', $path)) {
            preg_match('/[?&]id=([-\\w]{10,})/i', $path, $matches);
            if (empty($matches[1])) preg_match('#/file/d/([-\\w]{10,})#i', $path, $matches);
            if (!empty($matches[1])) {
                return 'https://drive.google.com/thumbnail?id=' . rawurlencode($matches[1]) . '&sz=w1000';
            }
        }

        if (preg_match('#^https?://#i', $path)) return $path;
        $path = preg_replace('#^/?uploads/prestasi/#i', '', $path) ?? $path;
        return url('/uploads/prestasi/' . ltrim($path, '/'));
    }

    public function scopePublished($query)
    {
        return $query->where(function($q) {
            $q->whereNull('status')->orWhere('status', 'published');
        });
    }

    public function scopeLatestPrestasi($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
