<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'tbl_setting';
    protected $primaryKey = 'setting_key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    public $timestamps = false;

    public static function get(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        if (!$setting && !str_contains($key, '.')) {
            $setting = self::where('setting_key', 'site.' . $key)->first()
                    ?? self::where('setting_key', $key . '_url')->first()
                    ?? self::where('setting_key', 'site.' . $key . '_url')->first();
        }
        return $setting ? $setting->setting_value : $default;
    }

    public static function put(string $key, $value)
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}
