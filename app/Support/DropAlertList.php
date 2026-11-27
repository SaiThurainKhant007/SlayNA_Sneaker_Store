<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Email list for the footer "Get the Drop" signup, kept in a JSON file on the private local disk
 * (storage/app/private/drop-alerts.json) instead of a database table, so the class diagram stays unchanged.
 * Each entry: ['email' => string, 'signed_up' => 'Y-m-d H:i:s'].
 */
class DropAlertList
{
    private const FILE = 'drop-alerts.json';

    /** All signups, newest first. */
    public function all(): Collection
    {
        $disk = Storage::disk('local');
        $entries = $disk->exists(self::FILE) ? json_decode($disk->get(self::FILE), true) : [];

        return collect(is_array($entries) ? $entries : [])->sortByDesc('signed_up')->values();
    }

    /** Adds an email; returns false when it was already on the list. */
    public function add(string $email): bool
    {
        $email = mb_strtolower(trim($email));
        $added = false;

        $this->update(function (array $entries) use ($email, &$added) {
            if (collect($entries)->contains('email', $email)) {
                return $entries;
            }
            $added = true;
            $entries[] = ['email' => $email, 'signed_up' => now()->format('Y-m-d H:i:s')];

            return $entries;
        });

        return $added;
    }

    public function remove(string $email): void
    {
        $email = mb_strtolower(trim($email));
        $this->update(fn (array $entries) => array_values(array_filter($entries, fn ($e) => $e['email'] !== $email)));
    }

    /** Read-change-write under a file lock, so two signups at the same moment can't overwrite each other. */
    private function update(callable $change): void
    {
        $disk = Storage::disk('local');
        if (! $disk->exists(self::FILE)) {
            $disk->put(self::FILE, '[]');
        }

        $handle = fopen($disk->path(self::FILE), 'c+');
        try {
            flock($handle, LOCK_EX);
            $entries = json_decode(stream_get_contents($handle) ?: '[]', true);
            $entries = $change(is_array($entries) ? $entries : []);
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            fflush($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
