@props(['typeData' => []])

<div>
    <x-form.input name="typeData.youtube_url" type="url" label="YouTube Video URL"
        placeholder="https://www.youtube.com/watch?v=..." />
    <x-form.input name="title" label="Title" optional />
    <x-form.textarea name="content" label="Content" rows="4" optional placeholder="What you think about it" />
</div>
