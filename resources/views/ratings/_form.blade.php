{{-- After the event: rate the seller, or show the rating already given. Expects $order (nullable). --}}
@if ($order)
    @php($rating = \App\Models\SellerRating::where('order_id', $order->id)->first())
    @if ($rating)
        <p class="rounded-2xl bg-white p-4 text-center text-sm text-gray-600 ring-1 ring-gray-200">
            You rated this seller {{ $rating->positive ? '👍 positive' : '👎 negative' }}. Thanks!
        </p>
    @elseif (\App\Http\Controllers\RatingController::canRate($order))
        <form method="POST" action="{{ route('ratings.store', $order) }}" x-data="{ positive: null }" class="rounded-2xl bg-white p-5 ring-1 ring-gray-200">
            @csrf
            <p class="text-center font-semibold text-gray-900">How did it go with {{ Str::of($order->ticket->seller?->name ?? 'the seller')->explode(' ')->first() }}?</p>
            <p class="mt-1 text-center text-sm text-gray-500">Your rating helps other fans buy safely.</p>
            <div class="mt-4 grid grid-cols-2 gap-3">
                @foreach ([1 => ['👍', 'Ticket worked'], 0 => ['👎', 'Had a problem']] as $value => [$emoji, $label])
                    <label class="flex cursor-pointer flex-col items-center rounded-xl border border-gray-200 p-3 transition hover:bg-gray-50 has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50 has-[:checked]:ring-1 has-[:checked]:ring-gray-900">
                        <input type="radio" name="positive" value="{{ $value }}" class="sr-only" required x-model="positive">
                        <span class="text-2xl" aria-hidden="true">{{ $emoji }}</span>
                        <span class="mt-1 text-sm font-medium text-gray-900">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <div x-cloak x-show="positive !== null" class="mt-4 space-y-3">
                <label for="comment" class="sr-only">Comment</label>
                <textarea id="comment" name="comment" rows="2" maxlength="500" class="form-control text-sm" placeholder="Anything to add? (optional)"></textarea>
                <x-ui.button class="w-full">Submit rating</x-ui.button>
            </div>
        </form>
    @endif
@endif
