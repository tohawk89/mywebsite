{{--
    Tag picker bound to a comma-separated string property (PostForm::$tags).
    Shows chips, suggests existing tags as you type; Enter or comma adds, Backspace removes the last.
    <x-form.tags name="tags" :suggestions="$allTagNames" />
--}}
@props([
    'name' => 'tags',
    'label' => 'Tags',
    'suggestions' => [],
    'hint' => null,
    'wrapper' => 'mb-4',
])

@php($id = 'field-'.str_replace('.', '-', $name))

<x-form.field :$name :for="$id" :$label :$hint :$wrapper :ai="false">
    <div class="tag-picker position-relative" @click.outside="open = false" x-data="{
            value: $wire.entangle(@js($name)),
            query: '',
            open: false,
            active: 0,
            navigated: false,
            all: @js(array_values($suggestions)),
            get chips() {
                return (this.value || '').split(',').map((tag) => tag.trim()).filter(Boolean);
            },
            get matches() {
                const query = this.query.trim().toLowerCase();
                const taken = this.chips.map((tag) => tag.toLowerCase());

                // Tags starting with what you typed come first, then ones containing it.
                return this.all
                    .filter((tag) => ! taken.includes(tag.toLowerCase()) && (query === '' || tag.toLowerCase().includes(query)))
                    .sort((a, b) => Number(! a.toLowerCase().startsWith(query)) - Number(! b.toLowerCase().startsWith(query)))
                    .slice(0, 8);
            },
            add(tag) {
                tag = tag.replaceAll(',', ' ').trim();
                if (tag !== '' && ! this.chips.some((chip) => chip.toLowerCase() === tag.toLowerCase())) {
                    this.value = [...this.chips, tag].join(', ');
                }
                this.query = '';
                this.active = 0;
                this.navigated = false;
            },
            remove(index) {
                const chips = this.chips;
                chips.splice(index, 1);
                this.value = chips.join(', ');
            },
            onKeydown(event) {
                if (event.key === 'Enter' || event.key === ',') {
                    event.preventDefault();
                    // Use the highlighted suggestion only if you picked it with the arrow keys; an exact match
                    // reuses the existing tag's spelling. Otherwise add exactly what you typed.
                    const typed = this.query.trim().toLowerCase();
                    const exact = this.all.find((tag) => tag.toLowerCase() === typed);
                    this.add(this.navigated && this.matches[this.active] ? this.matches[this.active] : (exact ?? this.query));
                } else if (event.key === 'Backspace' && this.query === '' && this.chips.length) {
                    this.remove(this.chips.length - 1);
                } else if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    this.open = true;
                    this.active = this.navigated ? Math.min(this.active + 1, this.matches.length - 1) : 0;
                    this.navigated = true;
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.active = Math.max(this.active - 1, 0);
                    this.navigated = true;
                } else if (event.key === 'Escape') {
                    this.open = false;
                }
            },
        }">
        <div class="form-control tag-picker-box" @click="$refs.input.focus()">
            <template x-for="(chip, index) in chips" :key="chip">
                <span class="tag-chip">
                    <span x-text="chip"></span>
                    <button type="button" @click.stop="remove(index)" :aria-label="'Remove ' + chip">&times;</button>
                </span>
            </template>
            <input type="text" id="{{ $id }}" x-ref="input" x-model="query" autocomplete="off"
                :placeholder="chips.length ? '' : 'Add a tag'" role="combobox" :aria-expanded="open && matches.length > 0"
                @focus="open = true" @input="open = true; active = 0; navigated = false" @keydown="onKeydown($event)">
        </div>
        <div class="dropdown-menu w-100 shadow-sm" :class="{ show: open && matches.length > 0 }" role="listbox">
            <template x-for="(tag, index) in matches" :key="tag">
                <button type="button" class="dropdown-item" :class="{ active: navigated && index === active }" role="option"
                    @mousedown.prevent="add(tag)" x-text="tag"></button>
            </template>
        </div>
    </div>

    {{ $slot }}
</x-form.field>
