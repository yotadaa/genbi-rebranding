<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GenBIPoint extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_genbi_point_activity';
    protected $primaryKey = 'activity_id';
    protected $guarded = [];
    public $timestamps = false;

    public function member()
    {
        return $this->belongsTo(TeamMember::class, 'team_id', 'id');
    }
}
