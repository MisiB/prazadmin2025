<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KnowledgeBase extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'external_url',
        'category',
        'tags',
        'status',
        'is_featured',
        'views_count',
        'author_id',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($knowledgeBase) {
            if (empty($knowledgeBase->slug)) {
                $knowledgeBase->slug = Str::slug($knowledgeBase->title);
            }
        });

        static::updating(function ($knowledgeBase) {
            if ($knowledgeBase->isDirty('title') && empty($knowledgeBase->slug)) {
                $knowledgeBase->slug = Str::slug($knowledgeBase->title);
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, $search)
    {
        return $query->whereFullText(['title', 'content', 'excerpt'], $search);
    }
}
