@props(['typeData' => []])

<div>
    <x-form.input name="typeData.spotify_url" type="url" label="Spotify URL"
        placeholder="https://open.spotify.com/track/..." />
    <x-form.input name="title" label="Title" optional />
    <x-form.textarea name="content" label="Content" rows="4" optional placeholder="What you think about it" />
</div>
