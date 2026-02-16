<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
    protected $fillable = [
        'action_number',
        'timestamp',
        'file_path',
        'change_type',
        'description',
        'commit_hash',
        'file_hash',
        'file_size',
    ];

    protected $casts = [
        'timestamp'     => 'datetime',
        'action_number' => 'integer',
        'file_size'     => 'integer',
    ];

    /**
     * Get the next sequential action number.
     */
    public static function nextActionNumber(): int
    {
        return (int) static::max('action_number') + 1;
    }

    /**
     * Check if an identical change was already logged (de-duplication).
     */
    public static function isDuplicate(string $filePath, string $changeType, ?string $fileHash): bool
    {
        if (!$fileHash) {
            return false;
        }

        return static::where('file_path', $filePath)
            ->where('change_type', $changeType)
            ->where('file_hash', $fileHash)
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();
    }
}
