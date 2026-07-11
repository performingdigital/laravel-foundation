<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Performing\LaravelFoundation\Support\UserModelResolver;

use function Laravel\Prompts\table;

final class UsersListCommand extends Command
{
    protected $signature = 'users:list {--limit=100 : Maximum number of users to display}';

    protected $description = 'List application users';

    public function handle(UserModelResolver $users): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 1000],
        ]);

        if ($limit === false) {
            $this->error('The limit must be between 1 and 1000.');

            return self::FAILURE;
        }

        $model = $users->newModel();
        $columns = $this->columns();
        $records = $model->newQuery()
            ->orderByDesc($model->getKeyName())
            ->limit($limit)
            ->get($columns)
            ->map(static fn (Model $user): array => array_values(array_map(
                static fn (mixed $value): string => self::displayValue($value),
                $user->only($columns),
            )))
            ->all();

        table($columns, $records);

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function columns(): array
    {
        $columns = config('foundation.users.list_columns', ['id', 'name', 'email', 'created_at']);

        return is_array($columns)
            ? array_values(array_filter($columns, 'is_string'))
            : ['id', 'name', 'email', 'created_at'];
    }

    private static function displayValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        return is_scalar($value) ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR);
    }
}
