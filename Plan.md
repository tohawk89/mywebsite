# Plan

Known bugs and the next improvements, roughly in priority order. Check items off (`[x]`) as they land.

## Bugs

### High

- [ ] **Drafts and unpublished posts can be opened in the modal.** `nav-modal` loads `Post::find($id)` without checking `is_draft` or `posted_at`, so anyone can dispatch `open-modal` with a draft's ID from the browser console and read it. Apply the same rules as the feed.
- [ ] **Saving some tags crashes.** `PostForm` calls `Tag::firstOrCreate(['name' => $name], ['slug' => Str::slug($name)])` and `tags.slug` is unique. Tags like `Laravel` vs `laravel`, or `C#` vs `C`, produce the same slug, and the save fails with a unique-constraint error. Look tags up by slug.
- [ ] **The feed shows scheduled posts early and orders by the wrong date.** `pages::post-feed` only checks `whereNotNull('posted_at')`, so posts dated in the future appear immediately. `Post::scopePinnedFirst()` sorts by `created_at`, so a backdated `posted_at` has no effect. Filter `posted_at <= now()` and sort by `posted_at`.

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
- [ ] Add feed tests for pinned ordering, scheduled posts and modal visibility (these cover the high-priority bugs above).

## Done recently

- Pinterest-style masonry feed; YouTube click-to-play thumbnails; Spotify player height by content type.
- `make:post-type` command, `PostTypeRegistry`, and the `post-type-development` AI skill.
- Upgrade to Laravel 13, Livewire 4.4 and Laravel Boost 2.
