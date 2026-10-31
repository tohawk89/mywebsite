---
name: post-type-development
description: 'ACTIVATE when adding, changing or debugging a post type (content type) in this app: anything under resources/views/components/posts/{type}/ (Handler.php, card.blade.php, form.blade.php), App\PostTypes\* (PostTypeHandler, PostTypeRegistry, DefaultHandler, ProvidesDefaultTitle), the make:post-type command or stubs/post-type/*. Also activate when the user asks for a new kind of post or card on the feed (podcast, book review, gallery, link, event, etc.), a new admin form field for a post type, or changes to how a post type is validated, saved or shown. Do NOT activate for auth, site settings or generic Livewire work unrelated to post types.'
metadata:
  author: project
---

# Post Type Development

Every post on the feed is one row in `posts` with a `type` string. Each type is a self-contained folder; the admin panel and feed discover types from folder names, so there is no registration step.

```
resources/views/components/posts/{type}/
├── Handler.php      App\PostTypes\{StudlyType}\Handler: label, rules, meta_data mapping, media
├── card.blade.php   Livewire SFC rendered on the public feed as posts.{type}.card
└── form.blade.php   Blade component rendered in the admin PostForm as posts.{type}.form
```

## Creating a post type

Always scaffold with the command, then customize the three files:

```bash
php artisan make:post-type {name} --label="Human Label" --media=cover
```

- `name` must be kebab-case (`podcast`, `book-review`); it becomes the folder, the component name and `App\PostTypes\{Studly}`.
- `--media` accepts only `cover` and/or `avatar` (the collections `Post` registers and `PostForm` can upload). Anything else needs changes to `Post::registerMediaCollections()` and `PostForm`.
- The command runs `composer dump-autoload`. If you create or rename a `Handler.php` by hand, run it yourself: handlers live under `resources/` and load via Composer's classmap. Without that, `PostTypeRegistry` silently falls back to `DefaultHandler`.

## Key pieces

- `App\PostTypes\PostTypeRegistry`: `all()`, `exists()`, `handler($type)`, `handlerClass($type)`, `path($type)`. Use it instead of building class names by hand.
- `App\PostTypes\PostTypeHandler`: `label()`, `rules()`, `buildMetaData(array $typeData)`, `mountData(Post $post)`, `mediaCollections()`.
- `App\PostTypes\ProvidesDefaultTitle`: optional; implement `defaultTitle($metaData, $typeData)` to fill an empty title on save (see the `repository` type).
- `App\Livewire\Admin\PostForm`: owns shared fields `title`, `content`, `tags`, `is_pinned`, `posted_at`, draft/publish, `cover_image`, `avatar_image`. Type-specific inputs bind to `typeData.*`.

## Handler rules

- Keys for type-specific fields are prefixed `typeData.` (`'typeData.url' => ['required', 'url']`). Shared fields (`title`, `content`) can be re-validated by the handler.
- Store type-specific data only through `buildMetaData()`; it is saved to the `meta_data` JSON column. Mirror it back in `mountData()` so editing pre-fills the form.
- Do expensive or derived work (parsing IDs, fetching remote data) in `buildMetaData()` at save time, not in the card. Fake HTTP with `Http::fake()` in tests (see `repository`).
- Prefer a custom validation closure over regex when a value must be parseable (see `youtube` `videoId()`).

## Card rules (feed)

- Root element must be `<div class="feed-item">`. The feed is a Pinterest-style grid: every card is the same width and the layout script measures each card's natural height.
- Never use fixed heights, `h-100` or Bootstrap column classes (`col-*`) on the card.
- Match the existing look: Bootstrap 5 + Bootstrap Icons, `card border-0 shadow-sm rounded-0 overflow-hidden text-start`, an optional pastel `background-color`, `<x-ui.post-tags :tags="$post->tags" />` in the body.
- Images: `loading="lazy"`, natural height (`w-100 h-auto` / `card-img-top`). Get URLs with `$post->getFirstMediaUrl('cover')`.
- Third-party embeds: wrap in `wire:ignore`; use a click-to-load thumbnail for heavy players (see `youtube`); give iframes a fixed known height or a `.ratio` box so layout does not jump.
- Open the detail modal with `@click="$dispatch('open-modal', { id: {{ $post->id }} })"`.

## Form rules (admin)

- Start with `@props(['typeData' => []])`.
- Bind shared fields with `wire:model="title"` / `wire:model="content"`, type fields with `wire:model="typeData.field"`, and show `@error('typeData.field')`.
- Inputs use `form-control rounded-0 bg-light border-0`; labels use `form-label fw-bold small text-uppercase`.
- Cover/avatar upload inputs appear automatically in the sidebar from `mediaCollections()`; do not add them to the form.

## Testing

Add a feature test per type (`tests/Feature/{Type}PostTest.php` or extend an existing one). Cover:

1. Creating via `Livewire::actingAs($admin)->test(PostForm::class)->set('type', '{type}')->set('typeData', [...])->call('save')` and asserting `meta_data`.
2. Validation errors for bad input (`assertHasErrors(['typeData.field'])`).
3. The card rendering on `/` with the expected output.
4. Editing: `mountData()` pre-fills `typeData`.

Admins are `User::factory()->create(['is_admin' => true])`. Run `php artisan test --compact` and `vendor/bin/pint --dirty`.

## Gotchas

- The `page` type is excluded from the feed by `pages::post-feed`; it is meant for standalone pages.
- The `profile` type's social links use `PostForm::addSns()` / `removeSns()`; that is the only type-specific logic left in `PostForm`. Keep new type logic inside the handler.
- After editing CSS/JS, run `npm run build` (or `npm run dev`) to see changes.
