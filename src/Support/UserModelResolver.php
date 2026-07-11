<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation\Support;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

final class UserModelResolver
{
    /**
     * @return class-string<Model>
     */
    public function modelClass(): string
    {
        $modelClass = config('foundation.users.model', 'App\\Models\\User');

        if (! is_string($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            throw new RuntimeException('The configured foundation user model must be an Eloquent model.');
        }

        return $modelClass;
    }

    public function newModel(): Model
    {
        $modelClass = $this->modelClass();

        return new $modelClass();
    }

    public function findByEmail(string $email): ?Model
    {
        return $this->newModel()->newQuery()->where('email', $email)->first();
    }
}
