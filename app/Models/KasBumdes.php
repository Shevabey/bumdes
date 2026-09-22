<?php

namespace App\Models;

use App\Models\Bumdes as BumdesModel;
use App\Models\KasMutasi as KasMutasiModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KasBumdes extends Model
{
    use HasFactory;

    protected $table = 'kas_bumdes';

    protected $primaryKey = 'id_kas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_kas',
        'id_bumdes',
        'saldo',
    ];

    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
        ];
    }

    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(BumdesModel::class, 'id_bumdes', 'id_bumdes');
    }

    public function mutasi(): HasMany
    {
        return $this->hasMany(KasMutasiModel::class, 'id_kas', 'id_kas');
    }
}
