# My Website

A personal website and CMS built with **Laravel 12**, **Livewire**, **Bootstrap 5.3**, and **Flux UI**. The feed uses a masonry card layout with infinite scroll. The core design goal is a **plugin-style post type system** — adding a new content type requires only dropping three files into one folder.

## 📋 Requirements

- **PHP**: ^8.3
- **Node.js** & **NPM**: (Latest LTS recommended)
- **Composer**
- **Database**: MySQL (recommended) or SQLite

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/nazar89/mywebsite.git
cd mywebsite
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure your database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mywebsite
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrate & Seed
```bash
php artisan migrate --seed
php artisan storage:link
```

### 5. Build Frontend Assets
```bash
npm run build
```

## 🛠 Usage

### Running Locally
The site is served via **Laravel Herd** at `https://mywebsite.test`.

For active frontend development, run:
```bash
npm run dev
```

### Creating an Admin User
Public registration is **disabled**. Create an admin user via the Artisan command:

```bash
php artisan user:create "Your Name" "your@email.com" "password123"
```

Then log in at `/login`.

---

## 🧩 Adding a Post Type

Post types live entirely inside `resources/views/components/posts/`. Each type is a folder with three files. The admin panel auto-discovers all types — no config or registration needed.

### Folder structure

```
resources/views/components/posts/
└── mytype/
    ├── Handler.php      ← business logic & validation
    ├── card.blade.php   ← public feed card (Livewire component)
    └── form.blade.php   ← admin create/edit form fields
```

### Step 1 — Create the Handler

`Handler.php` implements `App\PostTypes\PostTypeHandler`. The namespace must follow the pattern `App\PostTypes\{PascalCaseType}`.

```php
<?php

namespace App\PostTypes\Mytype;

use App\Models\Post;
use App\PostTypes\PostTypeHandler;

class Handler implements PostTypeHandler
{
    public function label(): string
    {
        return 'My Type';  // shown in the admin type selector
    }

    public function rules(): array
    {
        // Merged with the base PostForm rules.
        // Type-specific fields live in typeData.*
        return [
            'typeData.my_field' => ['required', 'string'],
        ];
    }

    public function buildMetaData(array $typeData): array
    {
        // Persist whatever you need into the post's meta_data JSON column.
        return [
            'my_field' => $typeData['my_field'] ?? null,
        ];
    }

    public function mountData(Post $post): array
    {
        // Pre-populate typeData when editing an existing post.
        return [
            'my_field' => $post->meta_data['my_field'] ?? '',
        ];
    }

    public function mediaCollections(): array
    {
        // Return Spatie MediaLibrary collection names this type uses.
        // Common options: 'cover', 'avatar'. Return [] if none.
        return ['cover'];
    }
}
```

### Step 2 — Create the card

`card.blade.php` is a Livewire component rendered in the public feed. It receives the `$post` model.

```blade
<?php
use App\Models\Post;
use Livewire\Component;

new class extends Component {
    public Post $post;
};
?>

<div class="col-12 col-md-6 col-lg-4 mb-2">
    <div class="card border-0 shadow-sm rounded-0 h-100">
        <div class="card-body">
            <h5 class="card-title">{{ $post->title }}</h5>
            <p class="card-text">{{ $post->meta_data['my_field'] ?? '' }}</p>
        </div>
    </div>
</div>
```

Use Bootstrap column classes (`col-lg-4` for narrow, `col-lg-8` for wide) to control the card's width in the masonry grid.

### Step 3 — Create the form

`form.blade.php` is rendered inside the admin post form for this type. Bind type-specific fields with `wire:model="typeData.your_field"`.

```blade
@props(['typeData' => []])

<div>
    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">My Field</label>
        <input type="text" wire:model="typeData.my_field"
            class="form-control rounded-0 bg-light border-0">
        @error('typeData.my_field')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
    </div>
</div>
```

### Step 4 — Regenerate the autoloader

Because `Handler.php` lives inside `resources/` (added to Composer's classmap), you must run:

```bash
composer dump-autoload
```

That's it. The type now appears in the admin type selector and posts of that type will render with your card on the public feed.

---

### Interface reference

| Method | Purpose |
|--------|---------|
| `label(): string` | Label shown in the admin type dropdown |
| `rules(): array` | Extra validation rules (keys prefixed `typeData.`) merged into `PostForm` |
| `buildMetaData(array $typeData): array` | Transforms `typeData` into the `meta_data` JSON saved on the post |
| `mountData(Post $post): array` | Populates `typeData` when editing an existing post |
| `mediaCollections(): array` | Spatie MediaLibrary collections the type uses (e.g. `['cover']`) |

If no `Handler.php` exists for a type, `App\PostTypes\DefaultHandler` is used as a fallback (no extra fields, label derived from the folder name).

---

## ✨ Other Features

- **Draft / Publish workflow** — keep posts hidden until ready
- **Pinned posts** with drag-to-reorder sort order
- **Tag-based organisation**
- **Media management** via Spatie MediaLibrary (cover & avatar images, AWS S3 support)
- **Site settings**: title, description, footer, accent colour, background colour, font, posts per page
- **6 font choices**: Instrument Sans, Inter, Merriweather, Playfair Display, Roboto Mono, Lora
- **Social profiles** for 12 platforms configurable from the admin panel
- **Authentication** via Laravel Fortify with two-factor support (TOTP + recovery codes)

## 📦 Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| Public Frontend | Bootstrap 5.3 (SCSS) + Bootstrap Icons + masonry-layout |
| Admin / Settings UI | Livewire + Flux UI 2 |
| Auth | Laravel Fortify (2FA) |
| Media | Spatie MediaLibrary 11 + AWS S3 |
| Frontend Bundler | Vite |
| Dev Server | Laravel Herd |
| Testing | PHPUnit 11 |

## 🤝 Contributing
Pull requests welcome for bug fixes, improvements, or new post types.

## 📄 License
[MIT](https://opensource.org/licenses/MIT)
