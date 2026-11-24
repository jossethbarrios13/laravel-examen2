<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'id_item';
    public $timestamps = false;

    protected $fillable = ['id_lista', 'descripcion', 'completado'];

    protected $casts = [
        'completado' => 'boolean',
    ];

    public function lista(): BelongsTo
    {
        return $this->belongsTo(Lista::class, 'id_lista', 'id_lista');
    }
}