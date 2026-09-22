<?php

namespace App\Models;

use App\Models\Bumdes as BumdesModel;
use App\Models\Transaksi as TransaksiModel;
use App\Models\UnitUsaha as UnitUsahaModel;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Akun extends Model implements AuthenticatableContract
{
    use AuthenticatableTrait;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    protected $table = 'akun';

    protected $primaryKey = 'id_akun';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_akun',
        'nama',
        'username',
        'password_hash',
        'role',
        'id_bumdes',
        'id_unit',
        'status_aktif',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function bumdes(): BelongsTo
    {
        return $this->belongsTo(BumdesModel::class, 'id_bumdes', 'id_bumdes');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitUsahaModel::class, 'id_unit', 'id_unit');
    }

    public function pelanggan(): HasOne
    {
        return $this->hasOne(Pelanggan::class, 'id_akun', 'id_akun');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiModel::class, 'dicatat_oleh', 'id_akun');
    }
}
