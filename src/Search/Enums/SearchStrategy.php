<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation\Search\Enums;

enum SearchStrategy: string
{
    case Keyword = 'keyword';
    case Hybrid = 'hybrid';
    case Semantic = 'semantic';

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            static fn (self $strategy): array => [
                'value' => $strategy->value(),
                'label' => $strategy->label(),
            ],
            self::cases(),
        );
    }

    public static function fromValue(self|string|null $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom((string) $value) ?? self::Keyword;
    }

    /**
     * @return array<string, mixed>
     */
    public function meilisearchOptions(): array
    {
        if ($this === self::Keyword) {
            return [];
        }

        return [
            'hybrid' => [
                'embedder' => config('foundation.search.meilisearch.embedder', 'default'),
                'semanticRatio' => $this === self::Semantic
                    ? 1.0
                    : (float) config('foundation.search.meilisearch.hybrid_semantic_ratio', 0.75),
            ],
        ];
    }
}
