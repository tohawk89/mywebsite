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
- `App\PostTypes\PostTypeHandler`: `label()`, `icon()` (Bootstrap Icons class such as `bi-youtube`, shown in the admin type picker and posts list), `rules()`, `buildMetaData(array $typeData)`, `mountData(Post $post)`, `mediaCollections()`.
- `App\PostTypes\HandlesMedia`: optional; implement `saveMedia(Post $post, array $typeData)` when a type manages its own uploads (bind `wire:model="typeData.images" multiple` in its form, validate `typeData.images.*`, return `[]` from `mediaCollections()`). See the `image` type's multi-photo gallery.
- `App\PostTypes\ProvidesDefaultTitle`: optional; implement `defaultTitle($metaData, $typeData)` to fill an empty title on save (see the `repository` type).
- `App\Livewire\Admin\PostForm`: owns shared fields `title`, `content`, `tags`, `is_pinned`, `posted_at`, draft/publish, `cover_image`, `avatar_image`. Type-specific inputs bind to `typeData.*`.

## Handler rules

- Keys for type-specific fields are prefixed `typeData.` (`'typeData.url' => ['required', 'url']`). Shared fields (`title`, `content`) can be re-validated by the handler.
- Store type-specific data only through `buildMetaData()`; it is saved to the `meta_data` JSON column. Mirror it back in `mountData()` so editing pre-fills the form.
- Do expensive or derived work (parsing IDs, fetching remote data) in `buildMetaData()` at save time, not in the card. Fake HTTP with `Http::fake()` in tests (see `repository`).
- Prefer a custom validation closure over regex when a value must be parseable (see `youtube` `videoId()`).

## Card rules (feed)

Build every card with `<x-post-card>` (`resources/views/components/post-card.blade.php`); never hand-write the `feed-item` / `.card` wrapper.

```blade
<x-post-card :post="$post" color="#FFE4D6" inset-media>
    <x-slot:media>
        <iframe wire:ignore src="..." width="100%" height="352" loading="lazy"></iframe>
    </x-slot:media>
</x-post-card>
```

- Default body (`<x-post-card-body>`): tags, title, three-line content excerpt; omitted entirely when all are empty.
- `media` slot: above the body, flush. Add `inset-media` for padded embeds (players, iframes).
- Default slot: extra content appended to the default body (e.g. a "Read More" button).
- `body` slot: replaces the default body for fully custom layouts (see `quote`, `profile`, `repository`, `image`).
- `:title="false"` / `:excerpt="false"` hide parts of the default body; `color` sets the pastel background; other attributes (`class`, `@click`, `style`) go on the card element.
- The feed is a Pinterest-style grid that measures each card's natural height: never use fixed heights, `h-100` or `col-*` classes.
- Images: `loading="lazy"`, natural height (`card-img-top` / `w-100 h-auto`), URLs from `$post->getFirstMediaUrl('cover')`.
- Heavy embeds: click-to-load thumbnail (see `youtube`); otherwise a fixed known height or `.ratio` box. Instagram iframes with `data-instagram-embed` auto-resize.
- Open the detail modal with `@click="$dispatch('open-modal', { id: {{ $post->id }} })"`.

## Form rules (admin)

Build forms from the field components in `resources/views/components/form/`; do not hand-write labels, inputs or `@error` blocks.

```blade
@props(['typeData' => []])

<div>
    <x-form.input name="typeData.url" type="url" label="URL" placeholder="https://..." />
    <x-form.input name="title" label="Title" size="lg" optional />
    <x-form.textarea name="content" label="Content" rows="6" optional />
    <x-form.select name="typeData.kind" label="Kind" :options="['a' => 'A', 'b' => 'B']" placeholder="Choose..." />
</div>
```

- `name` is the PostForm property: shared fields (`title`, `content`) or `typeData.*`. It drives `wire:model`, the label `for` id and the error message.
- Common props: `label`, `optional` (adds "(optional)"; keep it in sync with the handler rules), `hint`, `live` (`wire:model.live`), `wrapper` (wrapper classes, default `mb-4`; `wrapper=""` inside custom rows). `x-form.input` also takes `type` and `size="lg"`; `x-form.textarea` takes `rows`.
- Any other attribute (`placeholder`, `maxlength`, `style`...) is passed to the control.
- For custom layouts (repeaters, rows) wrap with `<x-form.field name="..." label="...">` and use label-less controls inside (see `profile`).
- Cover/avatar upload inputs appear automatically in the sidebar from `mediaCollections()`; do not add them to the form.
- To change how every form or card looks, edit the components, not the individual post types.

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
