<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\PostTypes\PostTypeRegistry;
use App\Support\AiWriting;
use App\Support\SiteBackground;
use App\Support\SiteOptions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Site settings, split into pages (sections). All settings are loaded and saved together,
 * so saving on any page keeps every value consistent; each page only shows its own fields.
 */
#[Layout('layouts::admin')]
class SiteSettings extends Component
{
    use WithFileUploads;

    /**
     * Section key => [title, description, icon].
     *
     * @var array<string, array{0: string, 1: string, 2: string}>
     */
    public const SECTIONS = [
        'general' => ['General', 'Your site\'s name, description and icon.', 'bi-sliders'],
        'appearance' => ['Appearance', 'Colors, fonts, card style and background.', 'bi-palette'],
        'feed' => ['Feed', 'How posts load and what shows on the home page.', 'bi-grid-3x3-gap'],
        'seo' => ['SEO and sharing', 'How your site looks in search results and when shared.', 'bi-search'],
        'integrations' => ['Integrations', 'Analytics and connected services.', 'bi-plug'],
        'ai' => ['AI writing', 'Grammar fixes, rewrites and tag suggestions in the post editor.', 'bi-stars'],
        'maintenance' => ['Maintenance', 'Coming soon mode and cache.', 'bi-tools'],
    ];

    private const HEX = 'regex:/^#[0-9a-fA-F]{6}$/';

    public string $section = 'general';

    // General
    public string $site_title = '';

    public string $site_description = '';

    public string $footer_text = '';

    /** Stored path of the uploaded site icon (favicon); empty for the default icon. */
    public string $site_icon = '';

    /** @var TemporaryUploadedFile|null */
    public $site_icon_upload;

    // Appearance
    public string $background_color = '#fdfbf7';

    public string $accent_color = '#2c3e50';

    public string $font = 'Instrument Sans';

    public string $heading_font = '';

    public string $card_corners = 'square';

    public bool $card_shadow = true;

    public string $max_columns = '4';

    /** @var array<string, mixed> Background options, see App\Support\SiteBackground */
    public array $background = [];

    /** @var TemporaryUploadedFile|null */
    public $background_image;

    // Feed
    public int $posts_per_page = 10;

    public string $feed_loading = 'infinite';

    public bool $show_dates = false;

    /** @var array<int, string> Post types left off the home page feed. */
    public array $hidden_types = [];

    // SEO and sharing
    public string $seo_share_image = '';

    /** @var TemporaryUploadedFile|null */
    public $share_image_upload;

    public string $seo_twitter_handle = '';

    public bool $seo_noindex = false;

    public string $seo_google_verification = '';

    // Integrations
    public string $analytics_provider = 'none';

    public string $analytics_ga_id = '';

    public string $analytics_plausible_domain = '';

    public bool $analytics_exclude_admins = true;

    /** A new GitHub token; left blank to keep the saved one. Never filled with the saved token. */
    public string $github_token_input = '';

    public bool $has_github_token = false;

    // AI writing
    public string $ai_provider = 'none';

    public string $ai_model_tier = 'smartest';

    public string $ai_model = '';

    public string $ai_style = '';

    /** A new API key for the selected provider; blank keeps the saved one. Never filled with a saved key. */
    public string $ai_key_input = '';

    /** @var array<string, bool> Provider => whether a key is saved. */
    public array $ai_keys_saved = [];

    /** @var array{ok: bool, message: string}|null */
    public ?array $ai_test_result = null;

    // Maintenance
    public bool $maintenance_enabled = false;

    public string $maintenance_message = '';

    /** @var array<string> */
    public array $availableFonts = SiteOptions::FONTS;

    public function mount(string $section = 'general'): void
    {
        abort_unless(array_key_exists($section, self::SECTIONS), 404);
        $this->section = $section;

        foreach ([
            'site_title', 'site_description', 'footer_text', 'site_icon', 'background_color', 'accent_color',
            'font', 'heading_font', 'card_corners', 'max_columns', 'feed_loading', 'seo_share_image',
            'seo_twitter_handle', 'seo_google_verification', 'analytics_provider', 'analytics_ga_id',
            'analytics_plausible_domain', 'maintenance_message', 'ai_provider', 'ai_model_tier', 'ai_model', 'ai_style',
        ] as $key) {
            $this->{$key} = SiteOptions::get($key);
        }

        foreach (['card_shadow', 'show_dates', 'seo_noindex', 'analytics_exclude_admins', 'maintenance_enabled'] as $key) {
            $this->{$key} = SiteOptions::flag($key);
        }

        $this->posts_per_page = (int) SiteOptions::get('posts_per_page');
        $this->hidden_types = SiteOptions::hiddenTypes();
        $this->has_github_token = SiteOptions::get('github_token') !== '';
        $this->background = SiteBackground::load();
        $this->refreshAiKeys();
    }

    public function rules(): array
    {
        return [
            'site_title' => ['required', 'string', 'max:100'],
            'site_description' => ['nullable', 'string', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            // No SVG: an uploaded SVG can carry scripts.
            'site_icon_upload' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,ico', 'max:1024'],
            'background_color' => ['required', self::HEX],
            'accent_color' => ['required', self::HEX],
            'font' => ['required', Rule::in(SiteOptions::FONTS)],
            'heading_font' => ['nullable', Rule::in(SiteOptions::FONTS)],
            'card_corners' => ['required', Rule::in(['square', 'rounded'])],
            'card_shadow' => ['boolean'],
            'max_columns' => ['required', Rule::in(['3', '4'])],
            'background_image' => ['nullable', 'image', 'max:5120'],
            ...SiteBackground::rules('background'),
            'posts_per_page' => ['required', 'integer', 'min:1', 'max:100'],
            'feed_loading' => ['required', Rule::in(['infinite', 'button'])],
            'show_dates' => ['boolean'],
            'hidden_types' => ['array'],
            'hidden_types.*' => [Rule::in(PostTypeRegistry::all())],
            'share_image_upload' => ['nullable', 'image', 'max:5120'],
            'seo_twitter_handle' => ['nullable', 'regex:/^@?[A-Za-z0-9_]{1,15}$/'],
            'seo_noindex' => ['boolean'],
            'seo_google_verification' => ['nullable', 'regex:/^[A-Za-z0-9_-]{10,100}$/'],
            'analytics_provider' => ['required', Rule::in(array_keys(SiteOptions::ANALYTICS_PROVIDERS))],
            'analytics_ga_id' => ['nullable', 'required_if:analytics_provider,ga4', 'regex:/^G-[A-Z0-9]{4,20}$/'],
            'analytics_plausible_domain' => ['nullable', 'required_if:analytics_provider,plausible', 'regex:/^(?:[a-z0-9-]+\.)+[a-z]{2,}$/i'],
            'analytics_exclude_admins' => ['boolean'],
            'github_token_input' => ['nullable', 'string', 'max:255'],
            'ai_provider' => ['required', Rule::in(['none', ...array_keys(AiWriting::PROVIDERS)])],
            'ai_model_tier' => ['required', Rule::in(array_keys(AiWriting::MODEL_TIERS))],
            'ai_model' => ['nullable', 'regex:/^[A-Za-z0-9._:\/-]{1,100}$/'],
            'ai_style' => ['nullable', 'string', 'max:300'],
            'ai_key_input' => ['nullable', 'string', 'max:500'],
            'maintenance_enabled' => ['boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'analytics_ga_id.regex' => 'Enter a GA4 Measurement ID like G-ABC123XYZ.',
            'analytics_ga_id.required_if' => 'Enter your GA4 Measurement ID.',
            'analytics_plausible_domain.regex' => 'Enter a domain like example.com.',
            'analytics_plausible_domain.required_if' => 'Enter the domain you added in Plausible.',
            'seo_twitter_handle.regex' => 'Enter an X handle like @yourname.',
            'seo_google_verification.regex' => 'Paste only the content value from Google\'s meta tag.',
        ];
    }

    public function updatedSiteIconUpload(): void
    {
        $this->validateOnly('site_icon_upload');
    }

    public function updatedBackgroundImage(): void
    {
        $this->validateOnly('background_image');
    }

    public function updatedShareImageUpload(): void
    {
        $this->validateOnly('share_image_upload');
    }

    public function removeSiteIcon(): void
    {
        $this->site_icon_upload = null;
        $this->site_icon = '';
    }

    public function removeBackgroundImage(): void
    {
        $this->background_image = null;
        $this->background['image_path'] = '';
    }

    public function removeShareImage(): void
    {
        $this->share_image_upload = null;
        $this->seo_share_image = '';
    }

    public function toggleHiddenType(string $type): void
    {
        $this->hidden_types = in_array($type, $this->hidden_types, true)
            ? array_values(array_diff($this->hidden_types, [$type]))
            : [...$this->hidden_types, $type];
    }

    public function removeGithubToken(): void
    {
        Setting::set('github_token', '');
        $this->has_github_token = false;
        $this->github_token_input = '';
        $this->dispatch('notify', message: 'GitHub token removed.');
    }

    public function updatedAiProvider(): void
    {
        $this->ai_key_input = '';
        $this->ai_test_result = null;
    }

    public function removeAiKey(): void
    {
        if (array_key_exists($this->ai_provider, AiWriting::PROVIDERS)) {
            Setting::set("ai_key_{$this->ai_provider}", '');
            $this->refreshAiKeys();
            $this->dispatch('notify', message: 'API key removed.');
        }
    }

    /**
     * Check the key and model with a tiny request, using the unsaved values on the page.
     */
    public function testAi(): void
    {
        $this->validateOnly('ai_provider');
        $this->validateOnly('ai_model');

        if (! array_key_exists($this->ai_provider, AiWriting::PROVIDERS)) {
            $this->ai_test_result = ['ok' => false, 'message' => 'Choose a provider first.'];

            return;
        }

        try {
            $model = AiWriting::testConnection(
                $this->ai_provider,
                filled($this->ai_key_input) ? trim($this->ai_key_input) : null,
                $this->ai_model_tier,
                trim($this->ai_model),
            );
            $this->ai_test_result = ['ok' => true, 'message' => "Connected. Requests will use {$model}."];
        } catch (\Throwable $exception) {
            report($exception);
            $this->ai_test_result = ['ok' => false, 'message' => AiWriting::errorMessage($exception)];
        }
    }

    private function refreshAiKeys(): void
    {
        $this->ai_keys_saved = collect(AiWriting::PROVIDERS)
            ->map(fn ($label, string $provider) => SiteOptions::get("ai_key_{$provider}") !== '')
            ->all();
    }

    public function clearCache(): void
    {
        Cache::flush();
        Artisan::call('view:clear');
        $this->dispatch('notify', message: 'Cache cleared.');
    }

    #[Computed]
    public function siteIconPreview(): ?string
    {
        return $this->filePreview($this->site_icon_upload, 'site_icon_upload', $this->site_icon);
    }

    #[Computed]
    public function shareImagePreview(): ?string
    {
        return $this->filePreview($this->share_image_upload, 'share_image_upload', $this->seo_share_image);
    }

    /**
     * Options for the live background preview: unsaved values, with invalid colors swapped for defaults
     * and a newly picked image shown from its temporary upload.
     *
     * @return array<string, mixed>
     */
    #[Computed]
    public function previewBackground(): array
    {
        $options = array_merge(SiteBackground::defaults(), $this->background);

        foreach ($options as $key => $value) {
            if (str_ends_with($key, '_color') && ! preg_match('/^#[0-9a-fA-F]{6}$/', (string) $value)) {
                $options[$key] = SiteBackground::defaults()[$key];
            }
        }

        if ($this->background_image && ! $this->getErrorBag()->has('background_image')) {
            $options['image_url'] = $this->background_image->temporaryUrl();
        }

        return $options;
    }

    #[Computed]
    public function previewBaseColor(): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $this->background_color) ? $this->background_color : '#fdfbf7';
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function postTypes(): array
    {
        return collect(PostTypeRegistry::all())
            ->reject(fn (string $type) => $type === 'page')
            ->mapWithKeys(fn (string $type) => [$type => PostTypeRegistry::handler($type)->label()])
            ->all();
    }

    public function save(): void
    {
        $this->validate();

        foreach ([
            'site_title', 'site_description', 'footer_text', 'background_color', 'accent_color', 'font',
            'heading_font', 'card_corners', 'max_columns', 'feed_loading', 'seo_google_verification',
            'analytics_provider', 'analytics_ga_id', 'maintenance_message', 'ai_provider', 'ai_model_tier', 'ai_style',
        ] as $key) {
            Setting::set($key, (string) $this->{$key});
        }

        Setting::set('ai_model', trim($this->ai_model));

        if (filled($this->ai_key_input) && array_key_exists($this->ai_provider, AiWriting::PROVIDERS)) {
            Setting::set("ai_key_{$this->ai_provider}", Crypt::encryptString(trim($this->ai_key_input)));
            $this->ai_key_input = '';
            $this->refreshAiKeys();
        }

        foreach (['card_shadow', 'show_dates', 'seo_noindex', 'analytics_exclude_admins', 'maintenance_enabled'] as $key) {
            Setting::set($key, $this->{$key} ? '1' : '0');
        }

        Setting::set('posts_per_page', (string) $this->posts_per_page);
        Setting::set('hidden_types', json_encode(array_values(array_unique($this->hidden_types))));
        Setting::set('seo_twitter_handle', ltrim($this->seo_twitter_handle, '@'));
        Setting::set('analytics_plausible_domain', strtolower($this->analytics_plausible_domain));

        if (filled($this->github_token_input)) {
            Setting::set('github_token', Crypt::encryptString(trim($this->github_token_input)));
            $this->github_token_input = '';
            $this->has_github_token = true;
        }

        $this->site_icon = $this->storeFile('site_icon', $this->site_icon_upload, $this->site_icon, 'site');
        $this->site_icon_upload = null;

        $this->seo_share_image = $this->storeFile('seo_share_image', $this->share_image_upload, $this->seo_share_image, 'site');
        $this->share_image_upload = null;

        $savedImage = SiteBackground::load()['image_path'];
        if ($this->background_image) {
            $this->background['image_path'] = $this->background_image->store('backgrounds', SiteBackground::disk());
            $this->background_image = null;
        }
        if ($savedImage && $savedImage !== $this->background['image_path']) {
            Storage::disk(SiteBackground::disk())->delete($savedImage);
        }
        SiteBackground::save($this->background);

        $this->dispatch('notify', message: 'Settings saved.');
    }

    /**
     * Store an uploaded file for a setting, delete the file it replaces (or that was removed),
     * and save the resulting path.
     */
    private function storeFile(string $key, ?TemporaryUploadedFile $upload, string $path, string $directory): string
    {
        $saved = SiteOptions::get($key);

        if ($upload) {
            $path = $upload->store($directory, SiteBackground::disk());
        }

        if ($saved && $saved !== $path) {
            Storage::disk(SiteBackground::disk())->delete($saved);
        }

        Setting::set($key, $path);

        return $path;
    }

    private function filePreview(?TemporaryUploadedFile $upload, string $field, string $path): ?string
    {
        if ($upload && ! $this->getErrorBag()->has($field) && $upload->isPreviewable()) {
            return $upload->temporaryUrl();
        }

        return $path ? Storage::disk(SiteBackground::disk())->url($path) : null;
    }

    public function render(): View
    {
        return view('livewire.admin.site-settings')->title(self::SECTIONS[$this->section][0].' settings');
    }
}
