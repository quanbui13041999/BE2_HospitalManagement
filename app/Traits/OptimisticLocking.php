<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;

trait OptimisticLocking
{
    /**
     * Update with optimistic locking - checks version before updating
     * Throws if version mismatch (concurrent modification detected)
     */
    public function updateWithLocking(array $attributes, int $currentVersion): int
    {
        // Increment version
        $attributes['version'] = $currentVersion + 1;

        // Atomic update with version check
        $updated = static::query()
            ->where($this->getKeyName(), $this->{$this->getKeyName()})
            ->where('version', $currentVersion)
            ->update($attributes);

        if ($updated === 0) {
            throw new \RuntimeException(
                'Concurrency Conflict: Record was modified by another process. Version mismatch.'
            );
        }

        return $updated;
    }

    /**
     * Get current version of the model
     */
    public function getCurrentVersion(): int
    {
        return $this->version ?? 1;
    }
}
