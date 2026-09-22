<?php

namespace App\Models;

use App\Models\Bumdes as BumdesModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitUsaha extends Model
{
    use HasFactory;

    protected $table = 'unit_usaha';

    protected $primaryKey = 'id_unit';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_unit',
        'id_bumdes',
        'jenis_unit',
        'nama_unit',
        'skema_field',
        'status_aktif',
    ];

    protected function casts(): array
    {
        return [
            'skema_field' => 'array',
            'status_aktif' => 'boolean',
        ];
    }

    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(BumdesModel::class, 'id_bumdes', 'id_bumdes');
    }
}
