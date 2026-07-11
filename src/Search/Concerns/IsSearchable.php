<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation\Search\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Scout\Searchable as ScoutSearchable;
use Meilisearch\Client;
use Performing\LaravelFoundation\Search\Enums\SearchStrategy;

trait IsSearchable
{
    use ScoutSearchable;

    /**
     * @param Builder<static> $query
     * @return Builder<static>
     */
    #[Scope]
    public function search(
        Builder $query,
        ?string $term = '',
        SearchStrategy|string|null $strategy = SearchStrategy::Keyword,
        int $limit = 5000,
    ): Builder {
        $strategy = SearchStrategy::fromValue($strategy);
        $keyName = $this->getKeyName();
        $hits = app(Client::class)
            ->getIndex($this->searchableAs())
            ->search($term ?? '', [
                'limit' => max(1, $limit),
                ...$strategy->meilisearchOptions(),
            ])
            ->getHits();
        $identifiers = array_values(array_filter(
            array_column($hits, $keyName),
            static fn(mixed $identifier): bool => is_int($identifier) || is_string($identifier),
        ));

        $query->whereIn($this->qualifyColumn($keyName), $identifiers);

        return $query;
    }
}
