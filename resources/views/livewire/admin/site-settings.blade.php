@php([$sectionTitle, $sectionDescription] = \App\Livewire\Admin\SiteSettings::SECTIONS[$section])
<div style="max-width: 760px;">
    <div class="admin-page-header">
        <div>
            <h1>{{ $sectionTitle }}</h1>
            <p>{{ $sectionDescription }}</p>
        </div>
    </div>

    {{-- Section switcher (also in the sidebar); handy on phones --}}
    <nav class="admin-filter-pills mb-4" aria-label="Settings pages">
        @foreach(\App\Livewire\Admin\SiteSettings::SECTIONS as $key => [$label])
            <a href="{{ route('settings', $key) }}" wire:navigate @class(['btn btn-light', 'active' => $section === $key])
                @if($section === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    <form wire:submit="save">
        @if($section === 'general')
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2>Identity</h2>
                    <p>How your site is named and described.</p>
                </div>
                <div class="admin-card-body">
                    <x-form.input name="site_title" label="Site title" />
                    <x-form.input name="site_description" label="Site description" optional
                        hint="Used as the meta description for search engines and when your site is shared." />
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
        @endif

        @if($section === 'appearance')
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2>Colors and fonts</h2>
                    <p>The accent color is used for links and buttons on your feed.</p>
                </div>
                <div class="admin-card-body">
                    <x-form.color name="accent_color" label="Accent color" />
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <x-form.select name="font" label="Body font" wrapper="mb-0"
                                :options="array_combine($availableFonts, $availableFonts)" />
                        </div>
                        <div class="col-sm-6">
                            <x-form.select name="heading_font" label="Heading font" wrapper="mb-0" placeholder="Same as body"
                                :options="array_combine($availableFonts, $availableFonts)" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2>Cards</h2>
                    <p>How posts look in the feed.</p>
                </div>
                <div class="admin-card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <x-form.select name="card_corners" label="Corners" wrapper="mb-0"
                                :options="['square' => 'Square', 'rounded' => 'Rounded']" />
                        </div>
                        <div class="col-sm-6">
                            <x-form.select name="max_columns" label="Columns on wide screens" wrapper="mb-0"
                                :options="['4' => 'Up to 4', '3' => 'Up to 3']" />
                        </div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="card-shadow" wire:model="card_shadow">
                        <label class="form-check-label" for="card-shadow">Show a soft shadow under cards</label>
                    </div>
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
        @endif

        @if($section === 'feed')
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2>Loading</h2>
                </div>
                <div class="admin-card-body">
                    <x-form.input name="posts_per_page" type="number" label="Posts per page" min="1" max="100"
                        style="max-width: 120px;" hint="Also how many more load each time." />
                    <x-form.select name="feed_loading" label="Loading more posts" wrapper="mb-3"
                        :options="['infinite' => 'Automatically as visitors scroll', 'button' => 'With a Load more button']" />
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" id="show-dates" wire:model="show_dates">
                        <label class="form-check-label" for="show-dates">Show the post date on each card</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="show-tag-bar" wire:model="show_tag_bar">
                        <label class="form-check-label" for="show-tag-bar">Show a tag bar above the feed</label>
                        <div class="form-text small">Tags on cards always filter the feed when clicked.</div>
                    </div>
                </div>
            </div>

            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2>Post types on the feed</h2>
                    <p>Unticked types stay in the admin but don't appear on your home page.</p>
                </div>
                <div class="admin-card-body">
                    <div class="row row-cols-1 row-cols-sm-2 g-2">
                        @foreach($this->postTypes as $type => $label)
                            <div class="col">
                                <div class="form-check">
                                    {{-- Ticked means shown, so the box is the inverse of $hidden_types --}}
                                    <input class="form-check-input" type="checkbox" id="feed-type-{{ $type }}"
                                        @checked(! in_array($type, $hidden_types, true))
                                        wire:click="toggleHiddenType('{{ $type }}')">
                                    <label class="form-check-label" for="feed-type-{{ $type }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('hidden_types.*') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>
        @endif

        @if($section === 'seo')
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2>Sharing</h2>
                    <p>Your site title and description are used when links to your site are shared.</p>
                </div>
                <div class="admin-card-body">
                    <x-form.field name="share_image_upload" for="share-image" label="Share image" optional
                        hint="Shown in link previews on social media and chat apps. 1200×630 works best; up to 5 MB.">
                        @if($this->shareImagePreview)
                            <img src="{{ $this->shareImagePreview }}" alt="Current share image" class="img-fluid rounded border mb-2" style="max-height: 160px;">
                        @endif
                        <div class="d-flex gap-2">
                            <input type="file" id="share-image" wire:model="share_image_upload" accept="image/*" class="form-control">
                            @if($share_image_upload || $seo_share_image)
                                <button type="button" class="btn btn-light flex-shrink-0" wire:click="removeShareImage">Remove</button>
                            @endif
                        </div>
                    </x-form.field>
                    <x-form.input name="seo_twitter_handle" label="X (Twitter) handle" optional placeholder="@yourname" wrapper="mb-0" />
                </div>
            </div>

            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2>Search engines</h2>
                </div>
                <div class="admin-card-body">
                    <x-form.input name="seo_google_verification" label="Google Search Console verification" optional
                        placeholder="abc123XYZ"
                        hint="From Google's HTML tag option, paste only the value inside content=&quot;...&quot;." />
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="seo-noindex" wire:model="seo_noindex">
                        <label class="form-check-label" for="seo-noindex">Hide my site from search engines</label>
                    </div>
                </div>
            </div>
        @endif

        @if($section === 'integrations')
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2>Analytics</h2>
                    <p>Count visits to your site. The tracking script loads on public pages only.</p>
                </div>
                <div class="admin-card-body">
                    <x-form.select name="analytics_provider" label="Provider" live :options="\App\Support\SiteOptions::ANALYTICS_PROVIDERS" />

                    @if($analytics_provider === 'ga4')
                        <x-form.input name="analytics_ga_id" label="Measurement ID" placeholder="G-ABC123XYZ"
                            hint="In Google Analytics: Admin, Data streams, your web stream. This ID is public, not a secret." />
                        <p class="small text-muted">Google Analytics sets cookies. If you have visitors from the EU or UK, you may need a cookie consent banner.</p>
                    @elseif($analytics_provider === 'plausible')
                        <x-form.input name="analytics_plausible_domain" label="Domain" placeholder="example.com"
                            hint="The domain you added in Plausible. Plausible uses no cookies." />
                    @endif

                    @if($analytics_provider !== 'none')
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="exclude-admins" wire:model="analytics_exclude_admins">
                            <label class="form-check-label" for="exclude-admins">Don't count my own visits while I'm logged in</label>
                        </div>
                    @endif
                </div>
            </div>

            <div class="admin-card mb-4">
                <div class="admin-card-header d-flex align-items-start justify-content-between gap-3">
                    <div>
                        <h2>GitHub</h2>
                        <p>Used by repository posts. Without a token, GitHub allows about 60 lookups an hour.</p>
                    </div>
                    @if($has_github_token)
                        <span class="admin-badge is-published flex-shrink-0">Connected</span>
                    @endif
                </div>
                <div class="admin-card-body">
                    <x-form.input name="github_token_input" type="password" :label="$has_github_token ? 'Replace token' : 'Personal access token'"
                        optional autocomplete="off" placeholder="github_pat_..." wrapper="mb-2"
                        hint="A fine-grained token with read-only access to public repositories is enough. It's stored encrypted and never shown again." />
                    @if($has_github_token)
                        <button type="button" class="btn btn-light btn-sm text-danger" wire:click="removeGithubToken"
                            wire:confirm="Remove the saved GitHub token?">Remove token</button>
                    @endif
                </div>
            </div>
        @endif

        @if($section === 'ai')
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2>Provider</h2>
                    <p>Text you send to AI goes to this provider and is billed to your API key.</p>
                </div>
                <div class="admin-card-body">
                    <x-form.select name="ai_provider" label="AI provider" live
                        :options="['none' => 'Off', ...\App\Support\AiWriting::PROVIDERS]" />

                    @if($ai_provider !== 'none')
                        @php($keySaved = $ai_keys_saved[$ai_provider] ?? false)
                        <x-form.input name="ai_key_input" type="password" autocomplete="off" wrapper="mb-2"
                            :label="$keySaved ? 'Replace API key' : 'API key'" :optional="$keySaved"
                            :placeholder="match ($ai_provider) { 'anthropic' => 'sk-ant-...', 'openai' => 'sk-...', default => 'AIza...' }"
                            hint="Stored encrypted and never shown again. Leave blank to keep the saved key." />
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                            @if($keySaved)
                                <span class="admin-badge is-published"><i class="bi bi-key"></i> Key saved</span>
                                <button type="button" class="btn btn-light btn-sm text-danger" wire:click="removeAiKey"
                                    wire:confirm="Remove the saved API key for this provider?">Remove key</button>
                            @endif
                            <span class="small text-muted">
                                Get a key from
                                @switch($ai_provider)
                                    @case('anthropic') the Claude Console (console.anthropic.com) @break
                                    @case('openai') the OpenAI dashboard (platform.openai.com) @break
                                    @default Google AI Studio (aistudio.google.com)
                                @endswitch
                            </span>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <x-form.select name="ai_model_tier" label="Model" :options="\App\Support\AiWriting::MODEL_TIERS" wrapper="mb-0" />
                            </div>
                            <div class="col-sm-6">
                                <x-form.input name="ai_model" label="Custom model ID" optional wrapper="mb-0"
                                    placeholder="Overrides the model above" />
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
                            <button type="button" class="btn btn-light" wire:click="testAi" wire:loading.attr="disabled" wire:target="testAi">
                                <span wire:loading.remove wire:target="testAi"><i class="bi bi-plug me-1"></i> Test connection</span>
                                <span wire:loading wire:target="testAi">Testing…</span>
                            </button>
                            @if($ai_test_result)
                                <span @class(['small', 'text-success' => $ai_test_result['ok'], 'text-danger' => ! $ai_test_result['ok']])>
                                    <i @class(['bi', 'bi-check-circle-fill' => $ai_test_result['ok'], 'bi-exclamation-circle-fill' => ! $ai_test_result['ok']])></i>
                                    {{ $ai_test_result['message'] }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($ai_provider !== 'none')
                <div class="admin-card mb-4">
                    <div class="admin-card-header">
                        <h2>Writing style</h2>
                        <p>Added to every AI request, so suggestions sound like you.</p>
                    </div>
                    <div class="admin-card-body">
                        <x-form.textarea name="ai_style" label="Style notes" optional rows="3" wrapper="mb-0"
                            placeholder="Casual, first person, short sentences. Keep Malaysian English spelling." />
                    </div>
                </div>
            @endif
        @endif

        @if($section === 'maintenance')
            <div class="admin-card mb-4">
                <div class="admin-card-header">
                    <h2>Coming soon mode</h2>
                    <p>Visitors see a simple page instead of your feed. You still see the site while logged in.</p>
                </div>
                <div class="admin-card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="maintenance-enabled" wire:model="maintenance_enabled">
                        <label class="form-check-label" for="maintenance-enabled">Turn on coming soon mode</label>
                    </div>
                    <x-form.textarea name="maintenance_message" label="Message" optional rows="3" wrapper="mb-0"
                        placeholder="Something new is on the way. Check back soon." />
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Save settings</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>

    @if($section === 'maintenance')
        <div class="admin-card mt-4">
            <div class="admin-card-header">
                <h2>Cache</h2>
                <p>Clear cached data and compiled views if a change doesn't show up.</p>
            </div>
            <div class="admin-card-body">
                <button type="button" class="btn btn-light" wire:click="clearCache" wire:loading.attr="disabled">
                    <i class="bi bi-arrow-repeat me-1"></i> Clear cache
                </button>
            </div>
        </div>
    @endif
</div>
