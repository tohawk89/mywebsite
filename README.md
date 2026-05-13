# My Website

A modern personal website and content management platform built with **Laravel 12**, **Livewire 3**, **Bootstrap 5.3**, and **Flux UI**. The public post feed uses a Bootstrap masonry card layout, while the admin and settings panels are powered by Livewire + Flux UI. Includes an infinite-scrolling feed, a full admin panel, site-wide customization, and secure authentication with two-factor support.

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

## ✨ Features

### Content Management
- **9 Post Types**: Blog, Spotify, YouTube, Instagram, Quote, Page, Image, Project, Profile
- **Draft / Publish workflow** — keep posts hidden until ready
- **Pinned posts** with custom sort order
- **Tag-based organization**
- **Media management** via Spatie MediaLibrary (cover & avatar images, AWS S3 support)

### Admin Panel (`/admin`)
- Post index with drag-to-reorder for pinned content
- Create / edit posts with rich metadata
- Site settings: title, description, footer, accent color, background color, font, posts per page

### Site Customization
- **6 font choices**: Instrument Sans, Inter, Merriweather, Playfair Display, Roboto Mono, Lora
- **Accent & background colors** configurable from the admin panel
- **Social profiles** for 12 platforms: GitHub, Twitter/X, LinkedIn, Instagram, YouTube, TikTok, Facebook, Discord, Twitch, Telegram, Reddit, Website

### Authentication & User Settings
- Email/password login via **Laravel Fortify**
- **Two-factor authentication** (TOTP + recovery codes)
- User settings: profile, password, appearance, 2FA, account deletion

### Public Feed
- Infinite-scrolling post feed (Livewire-powered)
- Configurable posts-per-page from site settings

## 📦 Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| Public Frontend | Bootstrap 5.3 (SCSS) + Bootstrap Icons + masonry-layout |
| Admin / Settings UI | Livewire 3 + Flux UI 2 |
| Auth | Laravel Fortify (2FA) |
| Media | Spatie MediaLibrary 11 + AWS S3 |
| Frontend Bundler | Vite |
| Dev Server | Laravel Herd |
| Testing | PHPUnit 11 |

## 🤝 Contributing
This is a personal project. Pull requests are welcome for bug fixes or improvements.

## 📄 License
[MIT](https://opensource.org/licenses/MIT)
