@php($fee = (float) config('services.tixswap.commission_percent'))
<x-tixswap-layout title="Sell a ticket" width="max-w-5xl">
    <x-ui.page-header title="Sell a ticket" :back="route('seller.dashboard')" back-label="My listings"
        description="Our team checks every listing before it goes live, usually within a few hours." />

    @if ($errors->any())
        <x-ui.alert tone="error" class="mb-6" title="Please fix the highlighted fields">
            {{ $errors->count() === 1 ? $errors->first() : 'A few details need your attention.' }}
        </x-ui.alert>
    @endif

    <form action="{{ route('seller.tickets.store') }}" method="POST" enctype="multipart/form-data"
          x-data="{ price: @js(old('price')), fee: {{ $fee }}, cover: null, proofName: null, submitting: false }"
          @submit="submitting = true">
        @csrf
        <x-ui.card>
            <div class="divide-y divide-gray-100">
                @component('seller.tickets._section', ['step' => 1, 'title' => 'The event', 'description' => 'What and where. Buyers search by event name and city.'])
                    <div x-data="eventPicker(@js(route('seller.events.search')), @js(old('event_name')), @js(old('location')), @js(old('date')))" class="space-y-5">
                        <input type="hidden" name="event_id" :value="picked ? picked.id : ''">

                        {{-- Event name with suggestions from events already on TixSwap --}}
                        <div class="relative" @click.outside="open = false">
                            <label for="event_name" class="block text-sm font-medium text-gray-800">Event name</label>
                            <input id="event_name" name="event_name" type="text" required autocomplete="off" placeholder="e.g. Blankets & Wine"
                                   x-model="name" @input.debounce.250ms="search()" @focus="open = results.length > 0" :readonly="picked"
                                   role="combobox" aria-autocomplete="list" :aria-expanded="open" aria-controls="event-suggestions"
                                   @class(['form-control mt-1.5', 'form-control-error' => $errors->has('event_name')]) :class="picked && 'bg-gray-50'">
                            <ul id="event-suggestions" x-cloak x-show="open" role="listbox"
                                class="absolute z-20 mt-1 max-h-72 w-full overflow-auto rounded-xl bg-white py-1 shadow-lift ring-1 ring-gray-200">
                                <li class="px-4 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Already on TixSwap</li>
                                <template x-for="e in results" :key="e.id">
                                    <li role="option">
                                        <button type="button" @click="pick(e)" class="w-full px-4 py-2.5 text-left hover:bg-gray-50">
                                            <span class="block text-sm font-medium text-gray-900" x-text="e.name"></span>
                                            <span class="block text-xs text-gray-500" x-text="e.label"></span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                            @error('event_name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <template x-if="picked">
                            <x-ui.alert tone="success">
                                Your ticket will be listed under this event, next to the others.
                                <button type="button" @click="clear()" class="ml-1 font-semibold underline">Not this event?</button>
                            </x-ui.alert>
                        </template>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-800">Venue and city</label>
                                <input id="location" name="location" type="text" required placeholder="e.g. Carnivore Grounds, Nairobi" x-model="location" :readonly="picked"
                                       @class(['form-control mt-1.5', 'form-control-error' => $errors->has('location')]) :class="picked && 'bg-gray-50'">
                                @error('location') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-800">Date and start time</label>
                                <input id="date" name="date" type="datetime-local" required x-model="date" :readonly="picked"
                                       @class(['form-control mt-1.5', 'form-control-error' => $errors->has('date')]) :class="picked && 'bg-gray-50'">
                                @error('date') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div x-show="!picked">
                            <span class="block text-sm font-medium text-gray-800">Cover image</span>
                            <label for="event_image" class="mt-1.5 flex cursor-pointer flex-col items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 transition hover:border-brand-400 hover:bg-brand-50/40 @error('event_image') border-red-400 @enderror">
                                <img x-cloak x-show="cover" :src="cover" alt="" class="aspect-[16/9] w-full object-cover">
                                <div x-show="!cover" class="flex flex-col items-center px-6 py-8 text-center">
                                    <x-ui.icon name="photo" class="h-8 w-8 text-gray-400" />
                                    <p class="mt-2 text-sm font-medium text-gray-700"><span class="text-brand-600">Upload a photo</span> of the event poster</p>
                                    <p class="mt-0.5 text-xs text-gray-500">JPG or PNG, up to 2 MB</p>
                                </div>
                            </label>
                            <input id="event_image" name="event_image" type="file" accept="image/jpeg,image/png" :required="!picked" class="sr-only"
                                   @change="const f = $event.target.files[0]; cover = f ? URL.createObjectURL(f) : null">
                            @error('event_image') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endcomponent

                @component('seller.tickets._section', ['step' => 2, 'title' => 'Your ticket', 'description' => 'Be specific about the seat or ticket type so buyers know exactly what they get.'])
                    <x-ui.field label="Seat or ticket type" name="seat_details" required placeholder="e.g. VIP, or Block C, Row 4, Seat 12" />
                    <div>
                        <x-ui.field label="Your price" name="price" type="number" prefix="KES" min="1" step="1" required inputmode="numeric" x-model="price" placeholder="0" />
                        <p class="mt-1.5 text-sm text-gray-500" x-show="price > 0" x-cloak>
                            You'll receive <strong class="money text-gray-900" x-text="'KES ' + Math.floor(Math.ceil(price) * (1 - fee / 100)).toLocaleString()"></strong>
                            after the {{ rtrim(rtrim(number_format($fee, 1), '0'), '.') }}% TixSwap fee.
                        </p>
                    </div>
                @endcomponent

                @component('seller.tickets._section', ['step' => 3, 'title' => 'Proof it\'s real', 'description' => 'Only our review team sees this. It\'s never shown to buyers.'])
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <x-ui.field label="Where you bought it" name="original_vendor" optional placeholder="e.g. Ticketsasa, Mookh" />
                        <x-ui.field label="Ticket or order number" name="ticket_reference" optional maxlength="100" placeholder="e.g. TKT-48213-AB"
                                    hint="Listings with a number are approved faster." />
                    </div>
                    <div>
                        <span class="block text-sm font-medium text-gray-800">Proof of purchase</span>
                        <label for="purchase_proof" class="mt-1.5 flex cursor-pointer items-center gap-4 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-4 transition hover:border-brand-400 hover:bg-brand-50/40 @error('purchase_proof') border-red-400 @enderror">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-gray-500 ring-1 ring-gray-200"><x-ui.icon name="document" /></span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-medium text-gray-700" x-text="proofName ?? 'Upload your receipt or e-ticket'"></span>
                                <span class="block text-xs text-gray-500">Screenshot, photo or PDF, up to 2 MB</span>
                            </span>
                        </label>
                        <input id="purchase_proof" name="purchase_proof" type="file" accept=".jpg,.jpeg,.png,.pdf" required class="sr-only"
                               @change="proofName = $event.target.files[0]?.name ?? null">
                        @error('purchase_proof') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endcomponent
            </div>
        </x-ui.card>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
            <x-ui.button variant="secondary" :href="route('seller.dashboard')">Cancel</x-ui.button>
            <x-ui.button size="lg" x-bind:disabled="submitting">
                <span x-show="!submitting">Submit for review</span>
                <span x-cloak x-show="submitting">Uploading…</span>
            </x-ui.button>
        </div>
    </form>

    @push('scripts')
        <script>
            function eventPicker(url, name, location, date) {
                return {
                    name: name || '', location: location || '', date: date || '',
                    results: [], open: false, picked: null,
                    async search() {
                        if (this.picked || this.name.trim().length < 2) { this.results = []; this.open = false; return; }
                        try {
                            const res = await fetch(url + '?q=' + encodeURIComponent(this.name.trim()), { headers: { Accept: 'application/json' } });
                            this.results = res.ok ? await res.json() : [];
                        } catch (e) { this.results = []; }
                        this.open = this.results.length > 0;
                    },
                    pick(e) { this.picked = e; this.name = e.name; this.location = e.location; this.date = e.date; this.open = false; },
                    clear() { this.picked = null; this.location = ''; this.date = ''; this.$nextTick(() => document.getElementById('event_name').focus()); },
                };
            }
        </script>
    @endpush
</x-tixswap-layout>
