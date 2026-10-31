<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * The landing page background: a solid color, gradient, image, CSS pattern or animated canvas effect.
 * Options are stored as JSON in the "background" setting; the base color stays in "background_color".
 */
class SiteBackground
{
    public const TYPES = [
        'color' => 'Color',
        'gradient' => 'Gradient',
        'image' => 'Image',
        'pattern' => 'Pattern',
        'animated' => 'Animated',
    ];

    public const PATTERNS = [
        'dots' => 'Dots',
        'grid' => 'Grid',
        'lines' => 'Diagonal lines',
        'grain' => 'Grain',
    ];

    public const EFFECTS = [
        'matrix' => 'Matrix rain',
        'particles' => 'Particles',
        'starfield' => 'Starfield',
        'waves' => 'Waves',
    ];

    public const CHARSETS = [
        'katakana' => 'Katakana',
        'binary' => 'Binary (0 and 1)',
        'custom' => 'Custom text',
    ];

    private const HEX = 'regex:/^#[0-9a-fA-F]{6}$/';

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'type' => 'color',
            'gradient_from' => '#fdfbf7',
            'gradient_via' => '',
            'gradient_to' => '#c4d1ec',
            'gradient_angle' => 135,
            'gradient_animate' => false,
            'image_path' => '',
            'image_fit' => 'cover',
            'image_blur' => 0,
            'image_overlay_color' => '#000000',
            'image_overlay_opacity' => 0,
            'pattern' => 'dots',
            'pattern_color' => '#d6d3cb',
            'pattern_size' => 24,
            'effect' => 'matrix',
            'effect_color' => '#22c55e',
            'effect_speed' => 5,
            'effect_density' => 5,
            'effect_opacity' => 100,
            'matrix_charset' => 'katakana',
            'matrix_text' => '',
            'disable_on_mobile' => false,
        ];
    }

    /**
     * Saved options merged over the defaults (unknown keys dropped).
     *
     * @return array<string, mixed>
     */
    public static function load(): array
    {
        $saved = json_decode((string) Setting::get('background', ''), true);

        return array_merge(self::defaults(), array_intersect_key(is_array($saved) ? $saved : [], self::defaults()));
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public static function save(array $options): void
    {
        Setting::set('background', json_encode(array_intersect_key($options, self::defaults())));
    }

    /**
     * Validation rules, keyed for a Livewire property named $prefix (e.g. "background.type").
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(string $prefix = 'background'): array
    {
        $rules = [
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'gradient_from' => ['required', self::HEX],
            'gradient_via' => ['nullable', self::HEX],
            'gradient_to' => ['required', self::HEX],
            'gradient_angle' => ['required', 'integer', 'between:0,360'],
            'gradient_animate' => ['boolean'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'image_fit' => ['required', Rule::in(['cover', 'tile'])],
            'image_blur' => ['required', 'integer', 'between:0,20'],
            'image_overlay_color' => ['required', self::HEX],
            'image_overlay_opacity' => ['required', 'integer', 'between:0,90'],
            'pattern' => ['required', Rule::in(array_keys(self::PATTERNS))],
            'pattern_color' => ['required', self::HEX],
            'pattern_size' => ['required', 'integer', 'between:8,80'],
            'effect' => ['required', Rule::in(array_keys(self::EFFECTS))],
            'effect_color' => ['required', self::HEX],
            'effect_speed' => ['required', 'integer', 'between:1,10'],
            'effect_density' => ['required', 'integer', 'between:1,10'],
            'effect_opacity' => ['required', 'integer', 'between:10,100'],
            'matrix_charset' => ['required', Rule::in(array_keys(self::CHARSETS))],
            'matrix_text' => ['nullable', 'string', 'max:40'],
            'disable_on_mobile' => ['boolean'],
        ];

        return collect($rules)->mapWithKeys(fn ($rule, $key) => ["{$prefix}.{$key}" => $rule])->all();
    }

    /**
     * Disk for uploaded background images: the media disk, unless that disk is private.
     */
    public static function disk(): string
    {
        $disk = config('media-library.disk_name', 'public');

        return $disk === 'local' ? 'public' : $disk;
    }

    /**
     * URL of the background image; "image_url" (not saved) overrides it for previews.
     *
     * @param  array<string, mixed>  $options
     */
    public static function imageUrl(array $options): ?string
    {
        if (filled($options['image_url'] ?? null)) {
            return $options['image_url'];
        }

        return filled($options['image_path'] ?? null) ? Storage::disk(self::disk())->url($options['image_path']) : null;
    }

    /**
     * Inline CSS for the fixed background layer. All values are validated (hex colors, integers, enums).
     *
     * @param  array<string, mixed>  $options
     */
    public static function layerStyle(array $options, string $baseColor): string
    {
        $styles = ["background-color: {$baseColor}"];

        switch ($options['type']) {
            case 'gradient':
                $stops = array_filter([$options['gradient_from'], $options['gradient_via'] ?: null, $options['gradient_to']]);
                $styles[] = 'background-image: linear-gradient('.(int) $options['gradient_angle'].'deg, '.implode(', ', $stops).')';
                if ($options['gradient_animate']) {
                    $styles[] = 'background-size: 300% 300%';
                }
                break;

            case 'image':
                if ($url = self::imageUrl($options)) {
                    // Blade escapes the style attribute; only keep quotes from breaking out of url("...").
                    $styles[] = 'background-image: url("'.str_replace('"', '%22', $url).'")';
                    $styles[] = $options['image_fit'] === 'tile'
                        ? 'background-repeat: repeat'
                        : 'background-size: cover; background-position: center';
                    if ((int) $options['image_blur'] > 0) {
                        $styles[] = 'filter: blur('.(int) $options['image_blur'].'px)';
                        $styles[] = 'transform: scale(1.06)';
                    }
                }
                break;

            case 'pattern':
                $color = $options['pattern_color'];
                $size = (int) $options['pattern_size'];
                $styles[] = match ($options['pattern']) {
                    'dots' => "background-image: radial-gradient({$color} 1.5px, transparent 1.5px); background-size: {$size}px {$size}px",
                    'grid' => "background-image: linear-gradient({$color} 1px, transparent 1px), linear-gradient(90deg, {$color} 1px, transparent 1px); background-size: {$size}px {$size}px",
                    'lines' => "background-image: repeating-linear-gradient(45deg, {$color} 0 1px, transparent 1px {$size}px)",
                    'grain' => "background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.12'/%3E%3C/svg%3E\")",
                };
                break;
        }

        return implode('; ', $styles).';';
    }

    /**
     * Options passed to the canvas effect script.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public static function effectConfig(array $options, string $baseColor): array
    {
        return [
            'effect' => $options['effect'],
            'color' => $options['effect_color'],
            'background' => $baseColor,
            'speed' => (int) $options['effect_speed'],
            'density' => (int) $options['effect_density'],
            'charset' => $options['matrix_charset'],
            'text' => (string) $options['matrix_text'],
            'disableOnMobile' => (bool) $options['disable_on_mobile'],
        ];
    }
}
