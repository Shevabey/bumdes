<?php

namespace App\Models;

use App\Models\Akun as AkunModel;
use App\Models\Tagihan as TagihanModel;
use App\Models\UnitUsaha as UnitUsahaModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected $primaryKey = 'id_pelanggan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_pelanggan',
        'id_unit',
        'nama',
        'kontak',
        'id_akun',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitUsahaModel::class, 'id_unit', 'id_unit');
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(AkunModel::class, 'id_akun', 'id_akun');
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(TagihanModel::class, 'id_pelanggan', 'id_pelanggan');
    }
}
