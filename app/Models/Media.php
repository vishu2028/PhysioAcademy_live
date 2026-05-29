<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['file_name', 'file_path', 'file_size', 'file_type', 'folder', 'filename', 'path', 'mime_type', 'size', 'alt_text'];

    /**
     * Get URL for the media file.
     */
    public function getUrlAttribute(): string
    {
        $path = $this->file_path ?: $this->path;
        return $path ? asset('storage/' . $path) : '';
    }

    /**
     * Check if the media is an image.
     */
    public function getIsImageAttribute(): bool
    {
        $type = $this->file_type ?: $this->mime_type;
        return $type && str_starts_with($type, 'image/');
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $size = $this->file_size ?: $this->size ?: 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}
