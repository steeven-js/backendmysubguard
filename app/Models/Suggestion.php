<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suggestion extends Model
{
    protected $fillable = [
        'name',
        'submission_count',
        'status',
        'approved_by',
        'catalogue_item_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // Relationships
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function catalogueItem(): BelongsTo
    {
        return $this->belongsTo(CatalogueItem::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    // Increment submission count for existing suggestion
    public static function incrementOrCreate(string $name): self
    {
        $normalizedName = strtolower(trim($name));

        $suggestion = self::whereRaw('LOWER(name) = ?', [$normalizedName])
            ->where('status', self::STATUS_PENDING)
            ->first();

        if ($suggestion) {
            $suggestion->increment('submission_count');
            return $suggestion;
        }

        return self::create(['name' => $name]);
    }
}
