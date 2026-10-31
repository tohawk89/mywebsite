@props(['typeData' => []])

@php
    $existing = $typeData['existing'] ?? [];
    $removing = array_map('intval', $typeData['remove'] ?? []);
    $uploads = array_filter((array) ($typeData['images'] ?? []), fn ($file) => $file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile);
@endphp

<div>
    <x-form.input name="title" label="Title" size="lg" optional placeholder="Optional caption" />

    <x-form.field name="typeData.images" for="field-typeData-images" label="Photos"
        :hint="'Select several at once. Up to '.\App\PostTypes\Image\Handler::MAX_IMAGES.' photos, 5 MB each. The first photo shows on the feed.'">
        <input type="file" id="field-typeData-images" wire:model="typeData.images" multiple accept="image/*" class="form-control">
        <div wire:loading wire:target="typeData.images" class="small text-muted mt-1">Uploading…</div>
        @foreach($errors->get('typeData.images.*') as $messages)
            <span class="text-danger small d-block">{{ $messages[0] }}</span>
        @endforeach
    </x-form.field>

    @if($existing || $uploads)
        <div class="row row-cols-3 row-cols-sm-4 g-2 mb-2">
            @foreach($existing as $image)
                @php($marked = in_array($image['id'], $removing, true))
                <div class="col" wire:key="existing-image-{{ $image['id'] }}">
                    <label @class(['image-post-thumb', 'is-removed' => $marked])
                        title="{{ $marked ? 'Keep this photo' : 'Remove this photo' }}">
                        <img src="{{ $image['url'] }}" alt="">
                        <input type="checkbox" class="visually-hidden" wire:model.live="typeData.remove" value="{{ $image['id'] }}">
                        <span class="image-post-thumb-action"><i @class(['bi', 'bi-arrow-counterclockwise' => $marked, 'bi-x-lg' => ! $marked])></i></span>
                    </label>
                </div>
            @endforeach

            @foreach($uploads as $index => $upload)
                <div class="col" wire:key="new-image-{{ $index }}">
                    <div class="image-post-thumb is-new">
                        @if($upload->isPreviewable())
                            <img src="{{ $upload->temporaryUrl() }}" alt="">
                        @else
                            {{-- Not an image: show the name until validation rejects it on save --}}
                            <span class="d-flex h-100 align-items-center justify-content-center p-2 small text-break text-center">
                                {{ $upload->getClientOriginalName() }}
                            </span>
                        @endif
                        <span class="image-post-thumb-badge">New</span>
                    </div>
                </div>
            @endforeach
        </div>
        @if($removing)
            <p class="small text-danger mb-0">{{ count($removing) }} {{ str('photo')->plural(count($removing)) }} will be removed when you save.</p>
        @endif
    @endif
</div>
