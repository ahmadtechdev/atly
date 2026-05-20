<?php

namespace App\Providers;

use App\Models\Invitation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination.atly');

        View::composer('components.dashboard.sidebar', function ($view): void {
            $user = auth()->user();

            $count = 0;

            if ($user !== null) {
                $count = Invitation::query()
                    ->pending()
                    ->forRecipient($user)
                    ->count();
            }

            $view->with('pendingInvitationsCount', $count);
        });
    }
}
