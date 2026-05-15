<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\PostForm;
use App\Livewire\Admin\PostIndex;
use App\Livewire\Admin\SiteSettings;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::livewire('/', 'pages::post-feed')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', Profile::class)->name('profile.edit');
    Route::livewire('settings/password', Password::class)->name('user-password.edit');
    Route::livewire('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::livewire('settings/two-factor', TwoFactor::class)
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
        Route::livewire('dashboard', Dashboard::class)->name('dashboard');
        Route::livewire('posts', PostIndex::class)->name('posts.index');
        Route::livewire('posts/create', PostForm::class)->name('posts.create');
        Route::livewire('posts/{post}/edit', PostForm::class)->name('posts.edit');
        Route::livewire('settings', SiteSettings::class)->name('settings');
    });
});
