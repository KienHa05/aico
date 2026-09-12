<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user): ?bool {
            return $user->hasRole('superadmin') ? true : null;
        });

        Gate::define('manage-users', function (User $user): bool {
            return $user->hasPermission('manage-users');
        });

        Gate::policy(User::class, UserPolicy::class);

        VerifyEmail::createUrlUsing(function ($notifiable): string {
            return URL::temporarySignedRoute(
                'api.v1.auth.verify-email',
                now()->addMinutes(60),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
        });

        ResetPassword::createUrlUsing(
            function ($notifiable, string $token): string {
                return sprintf(
                    '%s/reset-password?token=%s&email=%s',
                    rtrim(config('app.frontend_url'), '/'),
                    urlencode($token),
                    urlencode($notifiable->getEmailForPasswordReset())
                );
            }
        );
    }
}
