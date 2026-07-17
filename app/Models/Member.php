<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Member extends Model
{
    use LogsActivity;

    protected $fillable = ['nama', 'no_hp', 'poin'];

    protected $appends = ['level'];

    public function getLevelAttribute(): string
    {
        if ($this->poin >= 500) {
            return 'Gold';
        }

        if ($this->poin >= 300) {
            return 'Silver';
        }

        return 'Bronze';
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }

    protected static function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nama', 'no_hp', 'poin'])
            ->logOnlyDirty()
            ->useLogName('member');
    }
}