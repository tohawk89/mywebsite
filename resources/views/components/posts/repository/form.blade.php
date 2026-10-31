@props(['typeData' => []])

<div>
    <x-form.input name="typeData.repo_url" type="url" label="Repository URL"
        placeholder="https://github.com/owner/repo"
        hint="GitHub, GitLab or Bitbucket repository URL. Metadata will be fetched automatically on save." />
    <x-form.input name="typeData.repo_title" label="Personal Caption" optional
        placeholder="e.g. What I used for my website" />
    <x-form.textarea name="typeData.repo_note" label="Personal Note" rows="3" optional
        placeholder="Great package, makes uploads trivial..." />
</div>
