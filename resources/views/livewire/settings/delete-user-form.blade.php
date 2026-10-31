<div class="admin-card border-danger-subtle" x-data="{ confirming: @js($errors->has('password')) }">
    <div class="admin-card-header">
        <h2 class="text-danger">Delete account</h2>
        <p>Permanently delete your account and all of its data.</p>
    </div>
    <div class="admin-card-body">
        <button type="button" class="btn btn-outline-danger" x-show="! confirming" @click="confirming = true">
            Delete account
        </button>

        <form wire:submit="deleteUser" x-show="confirming" x-cloak>
            <p class="small mb-3">This can't be undone. Enter your password to confirm.</p>
            <x-form.input name="password" type="password" label="Password" autocomplete="current-password" />
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light" @click="confirming = false">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete my account</button>
            </div>
        </form>
    </div>
</div>
