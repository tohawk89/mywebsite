# My Website

A personal website and CMS built with **Laravel 13**, **Livewire 4** and **Bootstrap 5.3**. Posts appear in a Pinterest-style masonry feed with infinite scroll, and an admin panel manages posts and site settings. Content types are plugins: each post type is one folder, generated with a single command.

Known bugs and planned work are tracked in [Plan.md](Plan.md).

## Requirements

- PHP 8.3+
- Composer
- Node.js (LTS) and npm
- SQLite (default) or MySQL

## Setup

```bash
git clone https://github.com/nazar89/mywebsite.git
cd mywebsite
composer setup
php artisan storage:link
php artisan user:create "Your Name" "you@example.com" "password"
```

`composer setup` installs dependencies, creates `.env`, generates the app key, runs migrations and builds assets. Public registration is disabled, so `user:create` is how you get an admin account. Log in at `/login`; the admin panel is at `/admin/dashboard`.

Media is stored on the disk named in `MEDIA_DISK` (`local` by default; set it to `s3` with the `AWS_*` variables for S3).

## Development

The site is served by [Laravel Herd](https://herd.laravel.com) at `https://mywebsite.test`.

```bash
npm run dev          # Vite with hot reload
composer test        # run the test suite
vendor/bin/pint      # format PHP
```

## Post types

Each post type lives in `resources/views/components/posts/{type}/` and is discovered automatically. Create one with:

```bash
php artisan make:post-type podcast --label="Podcast Episode" --media=cover
```

| Option | Purpose |
|--------|---------|
| `name` | kebab-case type name (`podcast`, `book-review`) |
| `--label` | Label in the admin type selector (defaults to the headline of the name) |
| `--media` | Upload fields to enable: `cover`, `avatar` (repeatable) |
| `--force` | Overwrite an existing type |

This generates three files from the templates in `stubs/post-type/` and rebuilds the autoloader:

| File | Purpose |
|------|---------|
| `Handler.php` | `App\PostTypes\{Studly}\Handler`: label, admin icon (a Bootstrap Icons class), validation rules (`typeData.*`), how form data maps to the `meta_data` column, media collections |
| `card.blade.php` | The feed card (Livewire single-file component), built with `<x-post-card>` |
| `form.blade.php` | The admin form fields, built with `<x-form.*>` components |

If you create or rename a `Handler.php` by hand, run `composer dump-autoload`: handlers are loaded through Composer's classmap.

### Shared form and card components

Forms and cards are assembled from shared components, so changing a component restyles every post type.

```blade
{{-- form.blade.php --}}
<x-form.input name="typeData.instagram_url" type="url" label="Instagram Post URL" placeholder="https://..." />
<x-form.input name="title" label="Title" optional />
<x-form.textarea name="content" label="Content" rows="4" optional />
<x-form.select name="typeData.kind" label="Kind" :options="['a' => 'A']" />

{{-- card.blade.php --}}
<x-post-card :post="$post" color="#FFE4D6" inset-media>
    <x-slot:media><iframe ...></iframe></x-slot:media>
</x-post-card>
```

- **Form fields** (`resources/views/components/form/`): `name` sets the `wire:model` binding and error message. Options: `label`, `optional`, `hint`, `live`, `wrapper`, plus `type`/`size` (input) and `rows` (textarea). Other attributes go to the control. Use `<x-form.field>` to wrap custom layouts.
- **Cards** (`post-card`, `post-card-body`): the default body shows tags, title and a three-line excerpt. Use the `media` slot above it (`inset-media` pads embeds), the default slot to append content, or the `body` slot for a fully custom layout. `:title="false"` and `:excerpt="false"` hide parts. Avoid fixed heights so the masonry grid can size the card.

Current types: blog, image, instagram, page, profile, project, quote, repository, spotify, youtube.

### AI assistance

[Laravel Boost](https://github.com/laravel/boost) provides guidelines (`CLAUDE.md`, `AGENTS.md`), skills and an MCP server for Claude Code and GitHub Copilot. The project's own `post-type-development` skill teaches agents the conventions above. Its source is `.ai/skills/post-type-development/SKILL.md`; run `php artisan boost:update` after editing it.

## Features

- Draft/publish workflow, pinned posts with drag-to-reorder, tags
- Images via Spatie Media Library (local or S3)
- Site settings: title, description, footer, colours, font, posts per page
- Configurable home page background: color, gradient, image, CSS pattern, or animated effect (matrix rain, particles, starfield, waves) with a live preview. Effects live in `resources/js/backgrounds/effects/`, load on demand, pause in hidden tabs and respect reduced motion
- Social profile links for 12 platforms
- Fortify authentication with two-factor (TOTP and recovery codes)

## Tech stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 |
| Public site | Bootstrap 5.3 (SCSS), Bootstrap Icons |
| Admin and settings | Livewire 4, Flux UI 2 |
| Auth | Laravel Fortify |
| Media | Spatie Media Library 11 |
| Build | Vite |
| Testing | PHPUnit 12 |
| AI tooling | Laravel Boost 2 |

## License

[MIT](https://opensource.org/licenses/MIT)
