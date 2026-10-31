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
                <x-form.input name="footer_text" label="Footer text" optional
                    placeholder="© 2026 My Website. All rights reserved." />

                <x-form.field name="site_icon_upload" for="site-icon" label="Site icon" optional wrapper="mb-0"
                    hint="Shown in browser tabs and bookmarks. Square PNG, JPG, WebP or ICO up to 1 MB; 512×512 works best.">
                    <div class="d-flex align-items-center gap-3">
                        <span class="admin-thumb flex-shrink-0">
                            @if($this->siteIconPreview)
                                <img src="{{ $this->siteIconPreview }}" alt="Current site icon" class="w-100 h-100 object-fit-contain p-1">
                            @else
                                <i class="bi bi-globe2"></i>
                            @endif
                        </span>
                        <input type="file" id="site-icon" wire:model="site_icon_upload" accept=".png,.jpg,.jpeg,.webp,.ico"
                            class="form-control">
                        @if($site_icon_upload || $site_icon)
                            <button type="button" class="btn btn-light flex-shrink-0" wire:click="removeSiteIcon">Use default</button>
                        @endif
                    </div>
                </x-form.field>
            </div>
        </div>

        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2>Appearance</h2>
                <p>Accent color and font used on the public site.</p>
            </div>
            <div class="admin-card-body">
                <x-form.color name="accent_color" label="Accent color" />
                <x-form.select name="font" label="Font" wrapper="mb-0"
                    :options="array_combine($availableFonts, $availableFonts)" />
            </div>
        </div>

        @php($bgType = $background['type'] ?? 'color')
        <div class="admin-card mb-4">
            <div class="admin-card-header">
                <h2>Background</h2>
                <p>What sits behind the feed on your home page.</p>
            </div>
            <div class="admin-card-body">
                {{-- Live preview of the unsaved settings --}}
                <div class="site-bg-preview mb-4" wire:key="bg-preview-{{ md5(json_encode([$this->previewBackground, $this->previewBaseColor])) }}">
                    <x-site-background :options="$this->previewBackground" :base-color="$this->previewBaseColor" preview />
                    <div class="site-bg-preview-card" style="left: 8%; top: 18%;">
                        <div class="fw-semibold small">Welcome to my world</div>
                        <div class="text-muted small">Cards stay readable on top.</div>
                    </div>
                    <div class="site-bg-preview-card" style="left: 52%; top: 38%; background: #90EE90;">
                        <div class="fw-semibold small">Spotify card</div>
                    </div>
                </div>

                <div class="admin-filter-pills mb-4" role="radiogroup" aria-label="Background type">
                    @foreach(\App\Support\SiteBackground::TYPES as $value => $label)
                        <button type="button" wire:click="$set('background.type', '{{ $value }}')" role="radio"
                            aria-checked="{{ $bgType === $value ? 'true' : 'false' }}"
                            @class(['btn btn-light', 'active' => $bgType === $value])>{{ $label }}</button>
                    @endforeach
                </div>

                @if($bgType !== 'gradient')
                    <x-form.color name="background_color" :label="$bgType === 'color' ? 'Background color' : 'Base color'" live
                        :hint="$bgType === 'color' ? null : 'Shown behind the pattern, image or effect, and while it loads.'" />
                @endif

                @if($bgType === 'gradient')
                    <div class="row g-3">
                        <div class="col-sm-4"><x-form.color name="background.gradient_from" label="From" live /></div>
                        <div class="col-sm-4"><x-form.color name="background.gradient_via" label="Middle" optional live /></div>
                        <div class="col-sm-4"><x-form.color name="background.gradient_to" label="To" live /></div>
                    </div>
                    <x-form.range name="background.gradient_angle" label="Angle" min="0" max="360" suffix="°" live />
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="bg-gradient-animate"
                            wire:model.live="background.gradient_animate">
                        <label class="form-check-label" for="bg-gradient-animate">Slowly shift the colors</label>
                    </div>
                @endif

                @if($bgType === 'image')
                    <x-form.field name="background_image" for="bg-image" label="Image" hint="JPG, PNG or WebP up to 5 MB.">
                        <div class="d-flex gap-2">
                            <input type="file" id="bg-image" wire:model="background_image" accept="image/*" class="form-control">
                            @if($background_image || filled($background['image_path'] ?? ''))
                                <button type="button" class="btn btn-light" wire:click="removeBackgroundImage">Remove</button>
                            @endif
                        </div>
                    </x-form.field>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <x-form.select name="background.image_fit" label="Fit" live
                                :options="['cover' => 'Fill the screen', 'tile' => 'Repeat (tile)']" />
                        </div>
                        <div class="col-sm-6">
                            <x-form.range name="background.image_blur" label="Blur" min="0" max="20" suffix="px" live />
                        </div>
                        <div class="col-sm-6">
                            <x-form.color name="background.image_overlay_color" label="Tint color" live wrapper="mb-0" />
                        </div>
                        <div class="col-sm-6">
                            <x-form.range name="background.image_overlay_opacity" label="Tint strength" min="0" max="90" suffix="%" live
                                wrapper="mb-0" />
                        </div>
                    </div>
                @endif

                @if($bgType === 'pattern')
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <x-form.select name="background.pattern" label="Pattern" live :options="\App\Support\SiteBackground::PATTERNS" />
                        </div>
                        <div class="col-sm-6">
                            <x-form.color name="background.pattern_color" label="Pattern color" live />
                        </div>
                    </div>
                    <x-form.range name="background.pattern_size" label="Size" min="8" max="80" suffix="px" live wrapper="mb-0" />
                @endif

                @if($bgType === 'animated')
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <x-form.select name="background.effect" label="Effect" live :options="\App\Support\SiteBackground::EFFECTS" />
                        </div>
                        <div class="col-sm-6">
                            <x-form.color name="background.effect_color" label="Effect color" live />
                        </div>
                        <div class="col-sm-4">
                            <x-form.range name="background.effect_speed" label="Speed" min="1" max="10" live />
                        </div>
                        <div class="col-sm-4">
                            <x-form.range name="background.effect_density" label="Density" min="1" max="10" live />
                        </div>
                        <div class="col-sm-4">
                            <x-form.range name="background.effect_opacity" label="Opacity" min="10" max="100" suffix="%" live />
                        </div>
                    </div>

                    @if(($background['effect'] ?? '') === 'matrix')
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <x-form.select name="background.matrix_charset" label="Characters" live
                                    :options="\App\Support\SiteBackground::CHARSETS" />
                            </div>
                            @if(($background['matrix_charset'] ?? '') === 'custom')
                                <div class="col-sm-6">
                                    <x-form.input name="background.matrix_text" label="Your text" live maxlength="40"
                                        placeholder="NAZAR" />
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="bg-disable-mobile"
                            wire:model.live="background.disable_on_mobile">
                        <label class="form-check-label" for="bg-disable-mobile">Turn off on phones to save battery</label>
                    </div>
                    <p class="form-text small mb-0">
                        Visitors who have "reduce motion" turned on see a still version.
                    </p>
                @endif
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
