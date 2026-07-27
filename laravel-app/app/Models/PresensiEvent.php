<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresensiEvent extends Model
{
    protected $table = 'tbl_presensi_event';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function submissions()
    {
        return $this->hasMany(PresensiSubmission::class, 'event_id', 'id');
    }
}
