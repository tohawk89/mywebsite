@props(['typeData' => []])

<div>
    <x-form.input name="typeData.instagram_url" type="url" label="Instagram Post URL"
        placeholder="https://www.instagram.com/p/... or /reel/..." />
    <x-form.input name="title" label="Title" optional />
    <x-form.textarea name="content" label="Content" optional />
</div>
