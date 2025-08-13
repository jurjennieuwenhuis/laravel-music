<?php

namespace App\Providers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use TomatoPHP\FilamentUsers\Facades\FilamentUser;
use TomatoPHP\FilamentUsers\Filament\Resources\Users\Tables\UsersTable;

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
        Model::unguard();

        //$this->bootUserManager();

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }

    private function bootUserManager(): void
    {
        FilamentUser::register([
            RelationManager::make(),
        ]);
    }
}
