<?php

namespace App\Models;

use App\Models\Akun as AkunModel;
use App\Models\Bumdes as BumdesModel;
use App\Models\UnitUsaha as UnitUsahaModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedback';

    protected $primaryKey = 'id_feedback';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_feedback',
        'dari_id_akun',
        'ke_id_bumdes',
        'ke_id_unit',
        'isi_catatan',
        'status_tindak_lanjut',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
        ];
    }

    public function pengirim(): BelongsTo
    {
        return $this->belongsTo(AkunModel::class, 'dari_id_akun', 'id_akun');
    }

    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(BumdesModel::class, 'ke_id_bumdes', 'id_bumdes');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitUsahaModel::class, 'ke_id_unit', 'id_unit');
    }
}
