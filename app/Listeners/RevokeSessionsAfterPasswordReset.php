<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RevokeSessionsAfterPasswordReset
{
    public function handle(PasswordReset $event): void
    {
        if ($event->user instanceof User) {
            $event->user->forceFill(['remember_token' => Str::random(60)])->saveQuietly();
            DB::table('sessions')->where('user_id', $event->user->getKey())->delete();
        }
    }
}
