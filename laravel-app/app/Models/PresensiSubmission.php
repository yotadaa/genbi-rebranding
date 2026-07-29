<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiSubmission extends Model
{
    protected $table = 'tbl_presensi_submission';
    protected $primaryKey = 'submission_id';
    protected $guarded = [];
    public $timestamps = false;

    public function event()
    {
        return $this->belongsTo(PresensiEvent::class, 'presensi_event_id', 'presensi_event_id');
    }

    public function member()
    {
        return $this->belongsTo(TeamMember::class, 'team_id', 'id');
    }
}
