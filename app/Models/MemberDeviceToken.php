<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberDeviceToken extends Model
{
    protected $fillable = [
        'member_id',
        'token',
        'platform',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
