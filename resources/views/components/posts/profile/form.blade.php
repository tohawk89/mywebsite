@props(['typeData' => []])

@php
    $platforms = collect(\App\Enums\SnsType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])->all();
@endphp

<div>
    <x-form.input name="title" label="Name" size="lg" />
    <x-form.textarea name="content" label="Description" placeholder="Short bio or description..." />

    <x-form.field name="typeData.sns" label="Social Links" wrapper="mb-2">
        @foreach((array) ($typeData['sns'] ?? []) as $index => $link)
            <div class="d-flex gap-2 mb-2 align-items-start" wire:key="sns-{{ $index }}">
                <x-form.select name="typeData.sns.{{ $index }}.platform" :options="$platforms"
                    wrapper="" style="max-width: 160px;" />
                <x-form.input name="typeData.sns.{{ $index }}.url" type="url"
                    wrapper="flex-grow-1" placeholder="https://..." />
                <button type="button" wire:click="removeSns({{ $index }})"
                    class="btn btn-outline-danger rounded-0 btn-sm px-2" aria-label="Remove link">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endforeach
        <button type="button" wire:click="addSns"
            class="btn btn-outline-secondary rounded-0 btn-sm mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add Social Link
        </button>
    </x-form.field>
</div>
