<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::admin')]
class SiteSettings extends Component
{
    // Identity
    public string $site_title = '';

    public string $site_description = '';

    public string $footer_text = '';

    // Appearance
    public string $background_color = '#fdfbf7';

    public string $accent_color = '#2c3e50';

    public string $font = 'Instrument Sans';

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
        $this->background_color = Setting::get('background_color', '#fdfbf7');
        $this->accent_color = Setting::get('accent_color', '#2c3e50');
        $this->font = Setting::get('font', 'Instrument Sans');
        $this->posts_per_page = (int) Setting::get('posts_per_page', 10);
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
        ];
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

        session()->flash('status', 'Settings saved successfully.');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.admin.site-settings');
    }
}
