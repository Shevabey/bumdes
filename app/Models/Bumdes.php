<?php

namespace App\Models;

use App\Models\Feedback as FeedbackModel;
use App\Models\IuranBumdes as IuranBumdesModel;
use App\Models\KasBumdes as KasBumdesModel;
use App\Models\Referral as ReferralModel;
use App\Models\Region as RegionModel;
use App\Models\UnitUsaha as UnitUsahaModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function kas(): HasOne
    {
        return $this->hasOne(KasBumdesModel::class, 'id_bumdes', 'id_bumdes');
    }

    public function referralsDiajukan(): HasMany
    {
        return $this->hasMany(ReferralModel::class, 'id_bumdes_pengaju', 'id_bumdes');
    }

    public function referralsDiterima(): HasMany
    {
        return $this->hasMany(ReferralModel::class, 'id_bumdes_penerima', 'id_bumdes');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(FeedbackModel::class, 'ke_id_bumdes', 'id_bumdes');
    }
}
