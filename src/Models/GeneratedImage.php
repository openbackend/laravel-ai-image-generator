<?php

namespace OpenBackend\AiImageGenerator\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * AI Generated Image Model
 *
 * @property string $id
 * @property string $provider
 * @property string $prompt
 * @property array|null $options
 * @property string|null $model
 * @property string|null $original_url
 * @property string|null $file_path
 * @property string|null $file_name
 * @property int|null $file_size
 * @property string|null $mime_type
 * @property int|null $width
 * @property int|null $height
 * @property array|null $thumbnails
 * @property string $status
 * @property string|null $error_message
 * @property array|null $metadata
 * @property float|null $cost
 * @property string|null $user_id
 * @property string|null $session_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class GeneratedImage extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'ai_generated_images';

    protected $fillable = [
        'provider',
        'prompt',
        'options',
        'model',
        'original_url',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'width',
        'height',
        'thumbnails',
        'status',
        'error_message',
        'metadata',
        'cost',
        'user_id',
        'session_id',
    ];

    protected $casts = [
        'options' => 'array',
        'thumbnails' => 'array',
        'metadata' => 'array',
        'cost' => 'decimal:4',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the full URL to the stored image
     */
    public function getUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return $this->original_url;
        }

        $disk = config('aiimagegenerator.storage.disk', 'public');
        return Storage::disk($disk)->url($this->file_path);
    }

    /**
     * Get the full path to the stored image
     */
    public function getFullPathAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        $disk = config('aiimagegenerator.storage.disk', 'public');
        return Storage::disk($disk)->path($this->file_path);
    }

    /**
     * Check if the image generation was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the image generation failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the image generation is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get thumbnail URL by size
     */
    public function getThumbnailUrl(string $size = 'medium'): ?string
    {
        if (!$this->thumbnails || !isset($this->thumbnails[$size])) {
            return $this->url;
        }

        $disk = config('aiimagegenerator.storage.disk', 'public');
        return Storage::disk($disk)->url($this->thumbnails[$size]);
    }

    /**
     * Get human readable file size
     */
    public function getHumanFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope for successful generations
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed generations
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for pending generations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for specific provider
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for recent generations
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
