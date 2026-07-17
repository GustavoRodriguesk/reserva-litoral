<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'crm.guests';
    
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'full_name',
        'email',
        'phone',
        'document_type',
        'document_number',
        'birth_date',
        'nationality',
        'preferred_language',
        'notes',
    ];

    protected $casts = [
        'id' => 'string',
        'tenant_id' => 'string',
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            // Em uma arquitetura MT, o tenant_id geralmente vem da auth/sessão.
            if (empty($model->tenant_id)) {
                $model->tenant_id = request()->attributes->get('current_tenant') 
                                    ?? config('app.current_tenant') 
                                    ?? auth()->user()?->tenant_id;
            }
        });
    }
}
