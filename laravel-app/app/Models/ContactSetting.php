<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    protected $table = 'tbl_contact_setting';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $appends = ['map_embed_url'];

    public function getMapEmbedUrlAttribute()
    {
        if (!empty($this->latitude) && !empty($this->longitude)) {
            return sprintf(
                'https://www.google.com/maps?q=%s,%s&z=17&output=embed',
                rawurlencode($this->latitude),
                rawurlencode($this->longitude)
            );
        }
        return '';
    }
}
