<?php

namespace App\Models;

use App\Models\Bumdes as BumdesModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $table = 'referral';

    protected $primaryKey = 'id_referral';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_referral',
        'id_bumdes_pengaju',
        'id_bumdes_penerima',
        'kode_unik',
        'tanggal_generate',
        'tanggal_expired',
        'status',
        'tanggal_redeem',
        'batas_verifikasi',
        'tanggal_cair',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_generate' => 'datetime',
            'tanggal_expired' => 'datetime',
            'tanggal_redeem' => 'datetime',
            'batas_verifikasi' => 'datetime',
            'tanggal_cair' => 'datetime',
        ];
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(BumdesModel::class, 'id_bumdes_pengaju', 'id_bumdes');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(BumdesModel::class, 'id_bumdes_penerima', 'id_bumdes');
    }
}
