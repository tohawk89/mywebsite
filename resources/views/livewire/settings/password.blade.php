<div style="max-width: 760px;">
    <div class="admin-page-header">
        <div>
            <h1>Password</h1>
            <p>Use a long, random password to keep your account secure.</p>
        </div>
    </div>

    <form wire:submit="updatePassword" class="admin-card">
        <div class="admin-card-body">
            <x-form.input name="current_password" type="password" label="Current password" required autocomplete="current-password" />
            <x-form.input name="password" type="password" label="New password" required autocomplete="new-password" />
            <x-form.input name="password_confirmation" type="password" label="Confirm new password" required
                autocomplete="new-password" wrapper="mb-0" />
        </div>
        <div class="admin-card-body border-top d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Update password</button>
        </div>
    </form>
</div>
