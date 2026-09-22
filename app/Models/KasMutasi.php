<?php

namespace App\Models;

use App\Models\KasBumdes as KasBumdesModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KasMutasi extends Model
{
    use HasFactory;

    protected $table = 'kas_mutasi';

    protected $primaryKey = 'id_mutasi';

    protected $fillable = [
        'id_mutasi',
        'id_kas',
        'tipe',
        'jumlah',
        'sumber',
        'keterangan',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tanggal' => 'datetime',
        ];
    }

    public function kas(): BelongsTo
    {
        return $this->belongsTo(KasBumdesModel::class, 'id_kas', 'id_kas');
    }
}
