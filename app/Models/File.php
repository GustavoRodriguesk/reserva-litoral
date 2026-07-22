<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    use HasUuids;

    protected $table = 'core.files';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public const UPDATED_AT = null;

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->storage_path);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
