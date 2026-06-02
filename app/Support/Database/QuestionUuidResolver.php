<?php

namespace App\Support\Database;

class QuestionUuidResolver
{
    public const MAX_LENGTH = 36;

    public function toPersistent(string $uuid): string
    {
        $normalized = trim($uuid);

        if ($normalized === '') {
            return '';
        }

        if (strlen($normalized) <= self::MAX_LENGTH) {
            return $normalized;
        }

        $hash = substr(sha1($normalized), 0, 8);
        $prefixLength = self::MAX_LENGTH - strlen($hash) - 1;

        if ($prefixLength <= 0) {
            return substr($hash, 0, self::MAX_LENGTH);
        }

        $prefix = rtrim(substr($normalized, 0, $prefixLength), '-');

        if ($prefix === '') {
            return substr($hash, 0, self::MAX_LENGTH);
        }

        return $prefix . '-' . $hash;
    }

    /**
     * @param  array<int, string>  $uuids
     * @return array<int, string>
     */
    public function toPersistentMany(array $uuids): array
    {
        return array_map(fn (string $uuid) => $this->toPersistent($uuid), $uuids);
    }
}
