<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'precioInicial',
        'numMax',
        'user_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaController::class, 'categoria_id');
    }
}

