# Plan

Known bugs and the next improvements, roughly in priority order. Check items off (`[x]`) as they land.

## Bugs

### High

- [x] **Drafts and unpublished posts can be opened in the modal.** `nav-modal` loaded `Post::find($id)` without checking `is_draft` or `posted_at`, so anyone could dispatch `open-modal` with a draft's ID and read it. Fixed: the modal uses `Post::published()`.
- [x] **Saving some tags crashes.** Tags were looked up by name but `tags.slug` is unique, so `Laravel` vs `laravel` or `C#` vs `C` failed with a unique-constraint error. Fixed: tags are looked up by `Tag::slugFor()`, which is case-insensitive, keeps `#`/`+` distinct and supports non-Latin names.
- [x] **The feed shows scheduled posts early and orders by the wrong date.** Fixed: the feed uses `Post::published()` (`posted_at <= now()`), and `pinnedFirst()` sorts by `posted_at`.

### Medium

- [x] **The font setting has no effect.** Only Instrument Sans was loaded. Fixed: the public layout loads the selected font from Bunny Fonts.
- [x] **N+1 queries on the feed.** Fixed: the feed eager-loads `tags` and `media`; a test checks the query count stays flat as posts grow.
- [x] **Infinite scroll ignores the posts-per-page setting.** Fixed: `loadMore()` adds `config('site.posts_per_page')`.
- [x] **Spotify links with a locale prefix break.** Fixed: `Spotify\Handler::embedUrl()` parses share, `/intl-xx/` and embed links, drops query strings, and validation requires an `open.spotify.com` track, album, playlist, artist, episode or show link.
- [x] **Account and password-reset pages render unstyled.** They used Flux, which needs Tailwind (not installed). Fixed: rebuilt in Bootstrap; account pages now live in the admin.
- [x] **The accent color setting has no effect.** Fixed in admin phase 3: it now colors buttons, Read more links, link previews and footer links.

### Low / local

- [ ] **Mixed-content warnings locally.** `.env` has `APP_URL=http://mywebsite.test` while Herd serves https, so media URLs come out as `http://`. Set `APP_URL=https://mywebsite.test` (local `.env`, not committed).

## Next improvements

### Admin

- [x] Phase 1: sidebar layout with mobile menu, toasts, account pages in the admin, posts list with search, filters, bulk actions and quick pin/publish, post type picker with icons.
- [x] Phase 2: dashboard (stat tiles, continue editing, coming up, posts per month, content mix, quick create, site health).
- [x] Phase 3: settings split into pages (General, Appearance, Feed, SEO and sharing, Integrations, Maintenance) with a working accent color, heading font, card style, columns, feed loading/dates/hidden types, Open Graph and search tags, analytics (GA4 or Plausible), an encrypted GitHub token, and coming soon mode.
- [ ] Later and optional: cookie consent banner for Google Analytics; visit stats in the dashboard via the GA Data API (needs an encrypted service-account key); a logo once the public site has a header.
- [ ] Remove the now-unused `livewire/flux` dependency (no views use it anymore).

### Post types

- [ ] Move the profile type's social-link actions (`addSns` / `removeSns`) out of `PostForm` into the profile type, so `PostForm` has no type-specific logic.
- [ ] Make media collections generic (handler declares collections; `PostForm` and `Post::registerMediaCollections()` follow) so types like a gallery don't need core changes.
- [ ] Render feed cards as plain Blade components instead of a Livewire component per card (less JSON state sent to the browser and faster infinite scroll). Keep Livewire only for cards that need interactivity.

### Feed and content

- [ ] Responsive images: add Media Library conversions (thumbnail and WebP) and use them in cards instead of original uploads.
- [ ] Shareable permalinks for posts (`/posts/{uuid}`) with Open Graph and Twitter meta, and a sitemap.
- [ ] Filter the feed by tag (clickable tag badges).
- [ ] Self-host Bootstrap Icons and fonts through Vite instead of jsDelivr and Bunny CDNs.

### AI writing (later)

- [ ] AI on type-specific fields (link captions, repository notes).
- [ ] Suggest the site description and share text with AI.
- [ ] Image descriptions (alt text) for image posts with a vision model.

### Code quality

- [ ] Apply the newer Pint rules across the codebase in one formatting-only commit (`vendor/bin/pint`, about 20 files).
- [x] Add feed tests for pinned ordering, scheduled posts and modal visibility (`tests/Feature/PostVisibilityTest.php`).

## Done recently

- AI writing (Laravel AI SDK: Claude, Gemini or GPT) for titles and content with review before applying, AI tag suggestions; tag picker and Tags page; Markdown editor for blog and page; content for YouTube and Spotify posts.
- Admin phase 3: settings pages, SEO and sharing tags, analytics, GitHub token, coming soon mode; settings now load with one query per request.
- Admin phase 2: dashboard; site icon (favicon) upload in site settings.
- Website link post type: reads the shared page's title, description, image and icon (blocks private network addresses).
- Image posts: multiple photos per post, fullscreen viewer with slider (GLightbox); optional `HandlesMedia` hook for post types that manage their own uploads.
- Configurable home page background (color, gradient, image, pattern, animated effects) with live preview in site settings.
- Admin redesign phase 1 (see Admin above).
- Pinterest-style masonry feed; YouTube click-to-play thumbnails; Spotify player height by content type.
- `make:post-type` command, `PostTypeRegistry`, and the `post-type-development` AI skill.
- Upgrade to Laravel 13, Livewire 4.4 and Laravel Boost 2.
