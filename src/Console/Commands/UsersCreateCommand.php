<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Performing\LaravelFoundation\Support\UserModelResolver;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class UsersCreateCommand extends Command
{
    protected $signature = 'users:create
        {--name= : User name}
        {--email= : User email address}
        {--password= : User password}';

    protected $description = 'Create an application user';

    public function handle(UserModelResolver $users): int
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:' . config('foundation.defaults.passwords.min', 12)],
        ];
        $name = $this->stringOption('name') ?? text(
            label: 'Name',
            validate: ['name' => $rules['name']],
        );
        $email = $this->stringOption('email') ?? text(
            label: 'Email',
            validate: ['email' => $rules['email']],
        );
        $password = $this->stringOption('password') ?? password(
            label: 'Password',
            validate: ['password' => $rules['password']],
        );

        $validator = Validator::make(compact('name', 'email', 'password'), $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if ($users->findByEmail((string) $email) !== null) {
            $this->error('A user with this email address already exists.');

            return self::FAILURE;
        }

        $user = $users->newModel();
        $user->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make((string) $password),
        ])->save();

        $this->info("User {$email} created successfully.");

        return self::SUCCESS;
    }

    private function stringOption(string $name): ?string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
