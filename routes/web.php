<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\PostForm;
use App\Livewire\Admin\PostIndex;
use App\Livewire\Admin\SiteSettings;
use App\Livewire\PostFeed;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', PostFeed::class)->name('home');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // admin group
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('dashboard', Dashboard::class)->name('dashboard');
        Route::get('posts', PostIndex::class)->name('posts.index');
        Route::get('posts/create', PostForm::class)->name('posts.create');
        Route::get('posts/{post}/edit', PostForm::class)->name('posts.edit');
        Route::get('settings', SiteSettings::class)->name('settings');
    });
});
