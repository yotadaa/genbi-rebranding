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

    public function getNameAttribute()
    {
        return $this->member_name;
    }

    public function getContentAttribute()
    {
        return $this->detail;
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
