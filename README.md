# Masonry Blog

A modern, infinite-scrolling blog built with **Laravel 11**, **Livewire**, and **Bootstrap 5.3**. featuring a dynamic masonry grid layout, dedicated post types (Spotify, YouTube, Quotes), and a secure, private authentication system.

![Masonry Blog Preview](C:/Users/nazar/.gemini/antigravity/brain/69b32642-6f3b-477f-8878-583581a2fd23/final_refactored_view_1766572717909.png)

## 📋 Requirements
Ensure your environment meets the following dependencies:

- **PHP**: ^8.2
- **Node.js** & **NPM**: (Latest LTS recommended)
- **Composer**
- **Database**: MySQL (recommended) or SQLite

## 🚀 Installation & Onboarding

### 1. Clone the Repository
```bash
git clone https://github.com/nazar89/mywebsite.git
cd mywebsite
```

### 2. Install Dependencies
**Backend (PHP/Laravel):**
```bash
composer install
```

**Frontend (Assets):**
```bash
npm install
```

### 3. Environment Setup
Copy the example environment file and configure your database settings:
```bash
cp .env.example .env
php artisan key:generate
```
Open `.env` and set your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mywebsite
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Migration & Seeding
Run the migrations to set up the database schema:
```bash
php artisan migrate --seed
```
*The `--seed` flag will populate the database with demo content, including blog posts, tags, and a default profile.*

### 5. Link Storage
Create a symbolic link for the storage directory to serve images:
```bash
php artisan storage:link
```

## 🛠 Usage

### Running the Application
Start the local development server:
```bash
npm run dev
```
This command runs both the Laravel server (via `php artisan serve`) and the Vite development server concurrently.
Access the site at: `http://localhost:8000` (or `http://mywebsite.test` if using Herd/Valet).

### 🔐 Authentication (Private)
Public registration is **disabled** by default to keep the blog private. To create an admin user, use the custom Artisan command:

```bash
php artisan user:create "Your Name" "your@email.com" "password123"
```
Once created, you can log in at `/login`.

## ✨ Key Features

- **Masonry Grid Layout**: Automatically arranges posts in a staggered grid using `masonry-layout`.
- **Infinite Scroll**: Seamlessly loads more posts as you scroll down using Livewire.
- **Rich Post Types**:
    - **Blog**: Standard posts with cover images (via Spatie Media Library).
    - **Spotify**: Embeds Spotify tracks with a custom player card.
    - **YouTube**: Embeds YouTube videos with a red-themed card.
    - **Quote**: Stylized yellow cards for quotes.
- **Dynamic Modals**: Blog posts open in a modal with top-alignment, cover images, and tagging metadata.
- **Tagging System**: Auto-colored tags for organizing content.
- **Secure Auth**: Custom login page styling and CLI-based user registration.

## 📦 Tech Stack
- **Framework**: Laravel 11 / 12
- **Frontend**: Livewire + Bootstrap 5.3 (SCSS)
- **Bundler**: Vite
- **Media**: Spatie Laravel Medialibrary
- **Icons**: Bootstrap Icons

## 🤝 Contributing
This is a private personal blog project. Pull requests are welcome for bug fixes or optimizations.

## 📄 License
[MIT](https://opensource.org/licenses/MIT)
