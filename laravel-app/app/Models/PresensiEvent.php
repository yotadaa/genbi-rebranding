<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PresensiEvent extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_presensi_event';
    protected $primaryKey = 'presensi_event_id';
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = ['roles_json' => 'array'];

    public function submissions()
    {
        return $this->hasMany(PresensiSubmission::class, 'presensi_event_id', 'presensi_event_id');
    }

    public function members()
    {
        return $this->belongsToMany(TeamMember::class, 'tbl_presensi_event_member', 'presensi_event_id', 'team_id');
    }
}
