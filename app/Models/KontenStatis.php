<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KontenStatis extends Model
{
    protected $table = 'konten_statis';

    protected $fillable = [
        'slug',
        'judul',
        'konten',
    ];
}
