# Plan

Known bugs and the next improvements, roughly in priority order. Check items off (`[x]`) as they land.

## Bugs

### High

- [x] **Drafts and unpublished posts can be opened in the modal.** `nav-modal` loaded `Post::find($id)` without checking `is_draft` or `posted_at`, so anyone could dispatch `open-modal` with a draft's ID and read it. Fixed: the modal uses `Post::published()`.
- [x] **Saving some tags crashes.** Tags were looked up by name but `tags.slug` is unique, so `Laravel` vs `laravel` or `C#` vs `C` failed with a unique-constraint error. Fixed: tags are looked up by `Tag::slugFor()`, which is case-insensitive, keeps `#`/`+` distinct and supports non-Latin names.
- [x] **The feed shows scheduled posts early and orders by the wrong date.** Fixed: the feed uses `Post::published()` (`posted_at <= now()`), and `pinnedFirst()` sorts by `posted_at`.

### Medium

- [ ] **The font setting has no effect.** Admin can choose 6 fonts, but `layouts/app.blade.php` only loads Instrument Sans from Bunny Fonts, so every other choice falls back to the browser's sans-serif. Load the chosen font.
- [ ] **N+1 queries on the feed.** The feed query doesn't eager-load `tags` or `media`, so each card runs its own queries. Add `->with(['tags', 'media'])`.
- [ ] **Infinite scroll ignores the posts-per-page setting.** `loadMore()` always adds 10 instead of `config('site.posts_per_page')`.
- [ ] **Spotify links with a locale prefix probably break.** `open.spotify.com/intl-id/track/...` becomes `embed/intl-id/track/...`. Strip the `intl-xx/` segment (and query string) when building the embed URL, and validate the host properly instead of `regex:/spotify\.com/`.

### Low / local

- [ ] **Mixed-content warnings locally.** `.env` has `APP_URL=http://mywebsite.test` while Herd serves https, so media URLs come out as `http://`. Set `APP_URL=https://mywebsite.test` (local `.env`, not committed).

## Next improvements

### Post types

- [ ] Move the profile type's social-link actions (`addSns` / `removeSns`) out of `PostForm` into the profile type, so `PostForm` has no type-specific logic.
- [ ] Make media collections generic (handler declares collections; `PostForm` and `Post::registerMediaCollections()` follow) so types like a gallery don't need core changes.
- [ ] Render feed cards as plain Blade components instead of a Livewire component per card (less JSON state sent to the browser and faster infinite scroll). Keep Livewire only for cards that need interactivity.

### Feed and content

- [ ] Responsive images: add Media Library conversions (thumbnail and WebP) and use them in cards instead of original uploads.
- [ ] Shareable permalinks for posts (`/posts/{uuid}`) with Open Graph and Twitter meta, and a sitemap.
- [ ] Filter the feed by tag (clickable tag badges).
- [ ] Self-host Bootstrap Icons and fonts through Vite instead of jsDelivr and Bunny CDNs.

### Code quality

- [ ] Apply the newer Pint rules across the codebase in one formatting-only commit (`vendor/bin/pint`, about 20 files).
- [x] Add feed tests for pinned ordering, scheduled posts and modal visibility (`tests/Feature/PostVisibilityTest.php`).

## Done recently

- Pinterest-style masonry feed; YouTube click-to-play thumbnails; Spotify player height by content type.
- `make:post-type` command, `PostTypeRegistry`, and the `post-type-development` AI skill.
- Upgrade to Laravel 13, Livewire 4.4 and Laravel Boost 2.
