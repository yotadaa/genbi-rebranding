<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use SoftDeletes;

    protected $table = 'teams';
    protected $primaryKey = 'id';
    protected $guarded = [];
    public $timestamps = false;

    public function scopeBpiCore($query)
    {
        return $query->where('show_on_home', 1)->orderBy('home_sort_order', 'asc');
    }

    public function komsatRelation()
    {
        return $this->belongsTo(Komsat::class, 'komsat_id', 'id');
    }

    public function divisiRelation()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id', 'id');
    }
}
