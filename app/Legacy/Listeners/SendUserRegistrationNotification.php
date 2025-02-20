<?php

declare(strict_types=1);

namespace App\Legacy\Listeners;

use App\Legacy\Models\User;
use App\Legacy\Notifications\UserRegistrationNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Notification;

class SendUserRegistrationNotification
{
    public function handle(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;
        Notification::route('webhook', config('services.discord.webhook.users'))
            ->notify(new UserRegistrationNotification($user));
    }
}
