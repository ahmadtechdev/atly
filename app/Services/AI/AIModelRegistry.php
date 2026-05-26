<?php

namespace App\Services\AI;

class AIModelRegistry
{
    public function __construct(private readonly AIUsageTracker $usage) {}

    /**
     * Models that can be offered to end users right now.
     * Filters by: enabled flag, provider key present, monthly limit not exhausted.
     *
     * @return array<int, array<string, mixed>>
     */
    public function available(): array
    {
        return array_values(array_filter(
            $this->all(),
            fn (array $model): bool => $model['available'] === true,
        ));
    }

    /**
     * Every configured model, decorated with availability + remaining quota.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $models = config('ai.models', []);
        $providers = config('ai.providers', []);
        $out = [];

        foreach ($models as $id => $cfg) {
            $providerKey = (string) ($cfg['provider'] ?? '');
            $enabled = (bool) ($cfg['enabled'] ?? false);
            $hasKey = filled($providers[$providerKey]['api_key'] ?? null);
            $limit = $cfg['monthly_limit'] ?? null;
            $used = $this->usage->count($id);
            $remaining = $limit === null ? null : max(0, ((int) $limit) - $used);

            $reason = match (true) {
                ! $enabled => 'disabled',
                ! $hasKey => 'no_key',
                $remaining === 0 => 'limit_reached',
                default => null,
            };

            $out[] = [
                'id' => $id,
                'provider' => $providerKey,
                'model' => (string) ($cfg['model'] ?? ''),
                'label' => (string) ($cfg['label'] ?? $id),
                'tagline' => (string) ($cfg['tagline'] ?? ''),
                'enabled' => $enabled,
                'monthly_limit' => $limit === null ? null : (int) $limit,
                'used_this_month' => $used,
                'remaining' => $remaining,
                'available' => $reason === null,
                'unavailable_reason' => $reason,
            ];
        }

        return $out;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $modelId): ?array
    {
        foreach ($this->all() as $model) {
            if ($model['id'] === $modelId) {
                return $model;
            }
        }

        return null;
    }

    public function isAvailable(string $modelId): bool
    {
        return ($this->find($modelId)['available'] ?? false) === true;
    }

    public function donationUrl(): ?string
    {
        $url = config('ai.donation_url');

        return is_string($url) && trim($url) !== '' ? $url : null;
    }
}
