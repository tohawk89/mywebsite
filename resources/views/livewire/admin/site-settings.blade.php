<div style="max-width: 760px;">
    <div class="admin-page-header">
        <div>
            <h1>Site settings</h1>
            <p>Your site's name, look and feed.</p>
        </div>
    </div>

    <form wire:submit="save">
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2>Identity</h2>
                <p>How your site is named and described.</p>
            </div>
            <div class="admin-card-body">
                <x-form.input name="site_title" label="Site title" />
                <x-form.input name="site_description" label="Site description" optional
                    hint="Used as the meta description for search engines." />
                <x-form.input name="footer_text" label="Footer text" optional wrapper="mb-0"
                    placeholder="© 2026 My Website. All rights reserved." />
            </div>
        </div>

        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2>Appearance</h2>
                <p>Colors and font used on the public site.</p>
            </div>
            <div class="admin-card-body">
                <div class="row g-4 mb-4">
                    @foreach(['background_color' => ['Background color', '#fdfbf7'], 'accent_color' => ['Accent color', '#2c3e50']] as $field => [$label, $placeholder])
                        <div class="col-sm-6">
                            <x-form.field :name="$field" :for="'field-'.$field" :label="$label" wrapper="">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="color" wire:model.live="{{ $field }}" class="form-control form-control-color"
                                        style="width: 48px;" aria-label="{{ $label }} picker">
                                    <input type="text" id="field-{{ $field }}" wire:model.live="{{ $field }}"
                                        class="form-control font-monospace" maxlength="7" placeholder="{{ $placeholder }}">
                                </div>
                            </x-form.field>
                        </div>
                    @endforeach
                </div>

                <x-form.select name="font" label="Font" wrapper="mb-0"
                    :options="array_combine($availableFonts, $availableFonts)" />
            </div>
        </div>

        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2>Feed</h2>
                <p>How posts load on the home page.</p>
            </div>
            <div class="admin-card-body">
                <x-form.input name="posts_per_page" type="number" label="Posts per page" min="1" max="100"
                    wrapper="mb-0" style="max-width: 120px;" hint="Also the number loaded each time visitors scroll." />
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Save settings</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
