<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class PasswordReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets a users password';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->ask('What is the email address of the user?');
        if (!$email)
            return $this->info("No email, exiting");
        $user = User::where('email',$email)->first();
        if (!$user)
            return $this->info("No user found with email {$email}. Exiting");

        $password = $this->secret("Enter password for {$email}:");
        if ($password) {
            $user->password = \Hash::make($password);
            $user->save();
        }
        return $this->info("Password updated for user: {$email}");
    }
}
