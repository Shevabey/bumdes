<?php

namespace App\Models;

use App\Models\Akun as AkunModel;
use App\Models\UnitUsaha as UnitUsahaModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $primaryKey = 'id_transaksi';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_transaksi',
        'id_unit',
        'tipe',
        'jumlah',
        'detail',
        'tanggal',
        'dicatat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'detail' => 'array',
            'tanggal' => 'date',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitUsahaModel::class, 'id_unit', 'id_unit');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(AkunModel::class, 'dicatat_oleh', 'id_akun');
    }
}
