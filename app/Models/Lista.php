<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lista extends Model
{
    protected $table = 'listas';
    protected $primaryKey = 'id_lista';
    public $timestamps = false;

    protected $fillable = ['id_usuario', 'titulo', 'token'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'id_lista', 'id_lista');
    }
}