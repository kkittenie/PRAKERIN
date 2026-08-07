<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberStamp extends Model
{
    protected $table = 'member_stamps';

    protected $fillable = ['member_id', 'stamp_program_id', 'jumlah_stamp'];

    protected $casts = [
        'jumlah_stamp' => 'integer',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function stampProgram()
    {
        return $this->belongsTo(StampProgram::class);
    }

    public function getStampPenuhAttribute(): bool
    {
        return $this->jumlah_stamp >= $this->stampProgram->jumlah_stamp_dibutuhkan;
    }
}