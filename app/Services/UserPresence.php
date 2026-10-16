<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class UserPresence
{
    public const TIMEOUT = 300;

    private function key(int $userId): string
    {
        return 'user-presence:'.$userId;
    }

    public function touch(int $userId, string $sessionId): void
    {
        $sessions = $this->recent(Cache::get($this->key($userId), []));
        $sessions[hash('sha256', $sessionId)] = now()->timestamp;
        Cache::put($this->key($userId), $sessions, self::TIMEOUT);
    }

    public function forget(int $userId, string $sessionId): void
    {
        $sessions = $this->recent(Cache::get($this->key($userId), []));
        unset($sessions[hash('sha256', $sessionId)]);
        Cache::put($this->key($userId), $sessions, self::TIMEOUT);
    }

    public function onlineIds(array $userIds): array
    {
        $records = Cache::many(array_map(fn ($id) => $this->key((int) $id), $userIds));

        return array_values(array_filter($userIds, fn ($id) => count($this->recent($records[$this->key((int) $id)] ?? [])) > 0));
    }

    private function recent(?array $sessions): array
    {
        return array_filter($sessions ?? [], fn ($timestamp) => $timestamp > now()->timestamp - self::TIMEOUT);
    }
}
