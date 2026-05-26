<?php

namespace App\Services\AI;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AIUsageTracker
{
    public function count(string $modelId): int
    {
        return (int) Cache::get($this->key($modelId), 0);
    }

    public function increment(string $modelId): int
    {
        $key = $this->key($modelId);
        $current = (int) Cache::get($key, 0);
        $next = $current + 1;

        Cache::put($key, $next, Carbon::now()->endOfMonth()->addDay());

        return $next;
    }

    public function reset(string $modelId): void
    {
        Cache::forget($this->key($modelId));
    }

    private function key(string $modelId): string
    {
        return 'ai_blueprint_usage:'.$modelId.':'.Carbon::now()->format('Y-m');
    }
}
