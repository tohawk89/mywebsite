<?php

use App\Livewire\PostFeed;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Admin\PostIndex;
use App\Livewire\Admin\PostForm;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', PostFeed::class)->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
    Route::get('posts', PostIndex::class)->name('posts.index');
    Route::get('posts/create', PostForm::class)->name('posts.create');
    Route::get('posts/{post}/edit', PostForm::class)->name('posts.edit');
});
