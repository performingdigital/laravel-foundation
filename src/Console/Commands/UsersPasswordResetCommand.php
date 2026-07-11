<?php

declare(strict_types=1);

namespace Performing\LaravelFoundation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Performing\LaravelFoundation\Support\UserModelResolver;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class UsersPasswordResetCommand extends Command
{
    protected $signature = 'users:password-reset
        {email? : User email address}
        {--password= : New password}';

    protected $description = 'Reset an application user password';

    public function handle(UserModelResolver $users): int
    {
        $emailArgument = $this->argument('email');
        $email = is_string($emailArgument) && $emailArgument !== ''
            ? $emailArgument
            : text(label: 'Email', required: true);

        $passwordOption = $this->option('password');
        $password = is_string($passwordOption) && $passwordOption !== ''
            ? $passwordOption
            : password(label: 'New password', required: true);

        $validator = Validator::make(compact('email', 'password'), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:'.config('foundation.defaults.passwords.min', 12)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = $users->findByEmail((string) $email);

        if ($user === null) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        $user->forceFill(['password' => Hash::make((string) $password)])->save();

        $this->info("Password reset for {$email}.");

        return self::SUCCESS;
    }
}
