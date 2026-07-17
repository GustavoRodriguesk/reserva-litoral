<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'iam.users';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password_hash',
        'phone',
        'avatar_file_id',
        'locale',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'id' => 'string',
        'tenant_id' => 'string',
        'avatar_file_id' => 'string',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // A tabela iam.users não possui coluna remember_token, então sobrescrevemos os métodos relacionados
    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        // Sem ação
    }

    public function getRememberTokenName()
    {
        return '';
    }

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Hotel currently in scope for the user.
     *
     * A user may be assigned to a specific hotel through iam.user_roles.
     * When the role applies to every hotel, use the first hotel of its tenant
     * until the application gains an explicit hotel-switching screen.
     */
    public function getHotelIdAttribute(): ?string
    {
        $assignedHotelId = DB::table('iam.user_roles')
            ->where('user_id', $this->id)
            ->whereNotNull('hotel_id')
            ->value('hotel_id');

        return $assignedHotelId ?: DB::table('core.hotels')
            ->where('tenant_id', $this->tenant_id)
            ->orderBy('created_at')
            ->value('id');
    }
}
