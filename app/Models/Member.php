<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Member extends Model
{
    use LogsActivity;

    protected $fillable = ['nama', 'no_hp', 'poin'];

    public function getLevelAttribute(): string
    {
        return $this->poin >= 500 ? 'Gold' : 'Silver';
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