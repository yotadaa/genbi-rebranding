<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PrestasiToken extends Model
{
    protected $table = 'tbl_prestasi_submission_token';

    protected $primaryKey = 'token_id';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'revoked_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function findAvailableByPlainToken(string $plainToken, bool $lockForUpdate = false): ?self
    {
        $query = static::query()
            ->where('token_hash', hash('sha256', trim($plainToken)))
            ->whereNull('revoked_at')
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function (Builder $query): void {
                $query->where('max_uses', '<=', 0)
                    ->orWhereColumn('used_count', '<', 'max_uses');
            });

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function recordUse(): void
    {
        $this->used_count = ((int) $this->used_count) + 1;
        $this->used_at = now();
        $this->save();
    }
}
