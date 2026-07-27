<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiSubmission extends Model
{
    protected $table = 'tbl_presensi_submission';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function event()
    {
        return $this->belongsTo(PresensiEvent::class, 'event_id', 'id');
    }

    public function member()
    {
        return $this->belongsTo(TeamMember::class, 'team_id', 'id');
    }
}
