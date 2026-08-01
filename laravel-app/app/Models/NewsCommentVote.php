<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCommentVote extends Model
{
    protected $table = 'tbl_news_comment_vote';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function comment()
    {
        return $this->belongsTo(NewsComment::class, 'news_comment_id', 'id');
    }
}
