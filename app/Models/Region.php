<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    protected $table = 'region';

    protected $primaryKey = 'id_region';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_region',
        'jenis_wilayah',
        'nama_lengkap',
        'parent_id',
        'is_koordinator',
    ];

    protected function casts(): array
    {
        return [
            'is_koordinator' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id_region');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id_region');
    }
}
