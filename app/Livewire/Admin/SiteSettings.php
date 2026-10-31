<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Support\SiteBackground;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts::admin')]
#[Title('Site settings')]
class SiteSettings extends Component
{
    use WithFileUploads;

    // Identity
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

    /** @var array<string, mixed> Background options, see App\Support\SiteBackground */
    public array $background = [];

    /** @var TemporaryUploadedFile|null */
    public $background_image;

    // Feed
    public int $posts_per_page = 10;

    /** @var array<string> */
    public array $availableFonts = [
        'Instrument Sans',
        'Inter',
        'Merriweather',
        'Playfair Display',
        'Roboto Mono',
        'Lora',
    ];

    public function mount(): void
    {
        $this->site_title = Setting::get('site_title', 'My Website');
        $this->site_description = Setting::get('site_description', '');
        $this->footer_text = Setting::get('footer_text', '');
        $this->site_icon = (string) Setting::get('site_icon', '');
        $this->background_color = Setting::get('background_color', '#fdfbf7');
        $this->accent_color = Setting::get('accent_color', '#2c3e50');
        $this->font = Setting::get('font', 'Instrument Sans');
        $this->posts_per_page = (int) Setting::get('posts_per_page', 10);
        $this->background = SiteBackground::load();
    }

    public function rules(): array
    {
        return [
            'site_title' => ['required', 'string', 'max:100'],
            'site_description' => ['nullable', 'string', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'background_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'font' => ['required', 'string', 'in:'.implode(',', $this->availableFonts)],
            'posts_per_page' => ['required', 'integer', 'min:1', 'max:100'],
            'background_image' => ['nullable', 'image', 'max:5120'],
            // No SVG: an uploaded SVG can carry scripts.
            'site_icon_upload' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,ico', 'max:1024'],
            ...SiteBackground::rules('background'),
        ];
    }

    public function updatedSiteIconUpload(): void
    {
        $this->validateOnly('site_icon_upload');
    }

    public function removeSiteIcon(): void
    {
        $this->site_icon_upload = null;
        $this->site_icon = '';
    }

    #[Computed]
    public function siteIconPreview(): ?string
    {
        if ($this->site_icon_upload && ! $this->getErrorBag()->has('site_icon_upload') && $this->site_icon_upload->isPreviewable()) {
            return $this->site_icon_upload->temporaryUrl();
        }

        return $this->site_icon ? Storage::disk(SiteBackground::disk())->url($this->site_icon) : null;
    }

    public function updatedBackgroundImage(): void
    {
        $this->validateOnly('background_image');
    }

    public function removeBackgroundImage(): void
    {
        $this->background_image = null;
        $this->background['image_path'] = '';
    }

    /**
     * Options for the live preview: unsaved values, with invalid colors swapped for defaults
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

    public function save(): void
    {
        $this->validate();

        Setting::set('site_title', $this->site_title);
        Setting::set('site_description', $this->site_description);
        Setting::set('footer_text', $this->footer_text);
        Setting::set('background_color', $this->background_color);
        Setting::set('accent_color', $this->accent_color);
        Setting::set('font', $this->font);
        Setting::set('posts_per_page', (string) $this->posts_per_page);

        $this->saveSiteIcon();

        $savedImage = SiteBackground::load()['image_path'];

        if ($this->background_image) {
            $this->background['image_path'] = $this->background_image->store('backgrounds', SiteBackground::disk());
            $this->background_image = null;
        }

        // Delete the old file when the image was replaced or removed.
        if ($savedImage && $savedImage !== $this->background['image_path']) {
            Storage::disk(SiteBackground::disk())->delete($savedImage);
        }

        SiteBackground::save($this->background);

        $this->dispatch('notify', message: 'Settings saved.');
    }

    private function saveSiteIcon(): void
    {
        $savedIcon = (string) Setting::get('site_icon', '');

        if ($this->site_icon_upload) {
            $this->site_icon = $this->site_icon_upload->store('site', SiteBackground::disk());
            $this->site_icon_upload = null;
        }

        // Delete the old file when the icon was replaced or removed.
        if ($savedIcon && $savedIcon !== $this->site_icon) {
            Storage::disk(SiteBackground::disk())->delete($savedIcon);
        }

        Setting::set('site_icon', $this->site_icon);
    }

    public function render(): View
    {
        return view('livewire.admin.site-settings');
    }
}
