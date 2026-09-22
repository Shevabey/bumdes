<?php

namespace App\Models;

use App\Models\Akun as AkunModel;
use App\Models\Bumdes as BumdesModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IuranBumdes extends Model
{
    use HasFactory;

    protected $table = 'iuran_bumdes';

    protected $primaryKey = 'id_iuran';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_iuran',
        'id_bumdes',
        'bulan_tahun',
        'jumlah',
        'status',
        'sumber_dana',
        'metode_bayar',
        'bukti_pembayaran_url',
        'tanggal_bayar',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tanggal_bayar' => 'datetime',
            'tanggal_verifikasi' => 'datetime',
        ];
    }

    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(BumdesModel::class, 'id_bumdes', 'id_bumdes');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(AkunModel::class, 'diverifikasi_oleh', 'id_akun');
    }
}
