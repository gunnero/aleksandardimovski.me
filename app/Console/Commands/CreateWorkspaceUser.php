<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

final class CreateWorkspaceUser extends Command
{
    protected $signature = 'workspace:user-create';

    protected $description = 'Interactively create the sole private workspace owner';

    public function handle(): int
    {
        if (User::where('is_workspace_owner', true)->exists()) {
            $this->error('A workspace owner already exists.');

            return self::FAILURE;
        }
        $name = $this->ask('Full name');
        $email = $this->ask('Email');
        $password = $this->secret('Password (minimum 16 characters, mixed case, number, symbol)');
        $confirmation = $this->secret('Confirm password');
        $validator = Validator::make(compact('name', 'email', 'password', 'confirmation'), [
            'name' => 'required|string|max:200', 'email' => 'required|email:rfc|max:254|unique:users,email',
            'password' => ['required', 'same:confirmation', Password::min(16)->mixedCase()->numbers()->symbols()],
        ]);
        if ($validator->fails() || in_array(strtolower((string) $password), ['password', 'changeme', 'defaultpassword'], true)) {
            $this->error('Unsafe or invalid credentials refused.');
            foreach ($validator->errors()->all() as $error) {
                $this->line($error);
            }

            return self::INVALID;
        }
        $user = new User(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->email_verified_at = now();
        $user->is_workspace_owner = true;
        $user->save();
        $this->info('Workspace owner created. The password was not logged.');

        return self::SUCCESS;
    }
}
