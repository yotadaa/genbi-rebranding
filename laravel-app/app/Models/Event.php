<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_event';
    protected $primaryKey = 'event_id';
    protected $guarded = [];
    public $timestamps = false;

    public function getSlugAttribute()
    {
        if (!empty($this->attributes['slug'])) {
            return $this->attributes['slug'];
        }
        return Str::slug($this->event_title) . '-' . $this->event_id;
    }

    public function getStatusAttribute()
    {
        if (isset($this->attributes['status']) && !empty($this->attributes['status'])) {
            return $this->attributes['status'];
        }
        if (!empty($this->event_end_date) && Carbon::parse($this->event_end_date)->isPast()) {
            return 'completed';
        }
        return 'upcoming';
    }

    public function scopePublished($query)
    {
        // Actually Event doesn't have a status in legacy, but just in case
        return $query;
    }

    public function scopeLatestEvent($query)
    {
        return $query->orderBy('event_start_date', 'desc');
    }
}
