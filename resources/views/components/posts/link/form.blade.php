@props(['typeData' => []])

<div>
    <x-form.input name="typeData.url" type="url" label="Website URL" placeholder="https://example.com/article"
        hint="The page's title, description and preview image are read automatically when you save." />
    <x-form.input name="title" label="Title" optional placeholder="Why you're sharing it" />
    <x-form.textarea name="content" label="Content" rows="4" optional />
</div>
