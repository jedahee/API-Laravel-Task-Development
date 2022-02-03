<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puja extends Model
{
    use HasFactory;

    protected $fillable = [
        'dineroPujado',
        'prod_id',
        'user_id',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'prod_id');
    }
}
