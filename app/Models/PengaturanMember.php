<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanMember extends Model
{
    protected $table = 'pengaturan_members';

    protected $fillable = [
        'aktif',
        'minimal_belanja',
        'poin',
    ];
}
