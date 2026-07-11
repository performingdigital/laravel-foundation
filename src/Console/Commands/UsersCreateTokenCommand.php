<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation\Console\Commands;

use Closure;
use Illuminate\Console\Command;
use Performing\LaravelFoundation\Support\UserModelResolver;

use function Laravel\Prompts\text;

final class UsersCreateTokenCommand extends Command
{
    private const SANCTUM_TRAIT = 'Laravel\\Sanctum\\HasApiTokens';

    protected $signature = 'users:create-token
        {email? : User email address}
        {--name=cli : Token name}
        {--ability=* : Token ability}';

    protected $description = 'Create a Sanctum personal access token for a user';

    public function handle(UserModelResolver $users): int
    {
        $emailArgument = $this->argument('email');
        $email = is_string($emailArgument) && $emailArgument !== ''
            ? $emailArgument
            : text(label: 'Email', required: true);
        $user = $users->findByEmail((string) $email);

        if ($user === null) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        if (!in_array(self::SANCTUM_TRAIT, class_uses_recursive($user), true)) {
            $this->error('The configured user model must use Laravel\\Sanctum\\HasApiTokens.');

            return self::FAILURE;
        }

        if (!method_exists($user, 'createToken')) {
            $this->error('The configured user model cannot create Sanctum tokens.');

            return self::FAILURE;
        }

        $name = $this->option('name');
        $abilities = array_values(array_filter($this->option('ability'), 'is_string'));
        $token = Closure::fromCallable([$user, 'createToken'])(
            is_string($name) && $name !== '' ? $name : 'cli',
            $abilities !== [] ? $abilities : ['*'],
        );

        if (!is_object($token) || !isset($token->plainTextToken) || !is_string($token->plainTextToken)) {
            $this->error('Sanctum did not return a plain text token.');

            return self::FAILURE;
        }

        $this->line($token->plainTextToken);
        $this->warn('Copy this token now; it will not be shown again.');

        return self::SUCCESS;
    }
}
