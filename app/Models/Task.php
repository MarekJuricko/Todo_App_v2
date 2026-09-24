<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'is_completed'];

    protected $attributes = [
        'is_completed' => false,
    ];

    public function scopeCompleted(Builder $query): void
    {
        $query->where('is_completed', true);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('is_completed', false);
    }

    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeWithTag(Builder $query, int $tagId): void
    {
        $query->whereHas('tags', fn(Builder $q) => $q->where('tags.id', $tagId));
    }

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
