<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StampProgramHadiah extends Model
{
    protected $table = 'stamp_program_hadiah';

    protected $fillable = ['stamp_program_id', 'produk_id', 'jumlah',];

    public function stampProgram()
    {
        return $this->belongsTo(StampProgram::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}