@props(['typeData' => []])

<div>
    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Name</label>
        <input type="text" wire:model="title"
            class="form-control rounded-0 form-control-lg bg-light border-0">
        @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="mb-4">
        <label class="form-label fw-bold small text-uppercase">Description</label>
        <textarea wire:model="content" class="form-control rounded-0 bg-light border-0" rows="4"
            placeholder="Short bio or description..."></textarea>
        @error('content') <span class="text-danger small">{{ $message }}</span> @enderror
    </div>

    <div class="mb-2">
        <label class="form-label fw-bold small text-uppercase">Social Links</label>
        @foreach((array) ($typeData['sns'] ?? []) as $index => $link)
            <div class="d-flex gap-2 mb-2 align-items-start" wire:key="sns-{{ $index }}">
                <select wire:model="typeData.sns.{{ $index }}.platform"
                    class="form-select rounded-0 bg-light border-0" style="max-width: 160px;">
                    @foreach(\App\Enums\SnsType::cases() as $snsType)
                        <option value="{{ $snsType->value }}">{{ $snsType->label() }}</option>
                    @endforeach
                </select>
                <div class="flex-grow-1">
                    <input type="url" wire:model="typeData.sns.{{ $index }}.url"
                        class="form-control rounded-0 bg-light border-0"
                        placeholder="https://...">
                    @error("typeData.sns.{$index}.url")
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <button type="button" wire:click="removeSns({{ $index }})"
                    class="btn btn-outline-danger rounded-0 btn-sm px-2">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endforeach
        <button type="button" wire:click="addSns"
            class="btn btn-outline-secondary rounded-0 btn-sm mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add Social Link
        </button>
    </div>
</div>
