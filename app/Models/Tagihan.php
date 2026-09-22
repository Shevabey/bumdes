<?php

namespace App\Models;

use App\Models\Akun as AkunModel;
use App\Models\Pelanggan as PelangganModel;
use App\Models\UnitUsaha as UnitUsahaModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tagihan';

    protected $primaryKey = 'id_tagihan';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_tagihan',
        'id_pelanggan',
        'id_unit',
        'jumlah',
        'jatuh_tempo',
        'status',
        'metode',
        'bukti_transfer_url',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'jatuh_tempo' => 'date',
            'tanggal_verifikasi' => 'datetime',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(PelangganModel::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitUsahaModel::class, 'id_unit', 'id_unit');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(AkunModel::class, 'diverifikasi_oleh', 'id_akun');
    }
}
