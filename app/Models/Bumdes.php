<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bumdes extends Model
{
    use HasFactory;

    protected $table = 'bumdes';
    protected $primaryKey = 'id_bumdes';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_bumdes',
        'id_kelurahan',
        'nama_bumdes',
        'status_aktif',
        'tanggal_berdiri',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
            'tanggal_berdiri' => 'date',
        ];
    }

    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'id_kelurahan', 'id_region');
    }
}
