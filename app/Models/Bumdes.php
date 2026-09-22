<?php

namespace App\Models;

use App\Models\IuranBumdes as IuranBumdesModel;
use App\Models\Region as RegionModel;
use App\Models\UnitUsaha as UnitUsahaModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->belongsTo(RegionModel::class, 'id_kelurahan', 'id_region');
    }

    public function units(): HasMany
    {
        return $this->hasMany(UnitUsahaModel::class, 'id_bumdes', 'id_bumdes');
    }

    public function iuran(): HasMany
    {
        return $this->hasMany(IuranBumdesModel::class, 'id_bumdes', 'id_bumdes');
    }
}
