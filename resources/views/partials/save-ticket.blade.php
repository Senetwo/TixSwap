{{-- "Save as image" for the ticket pass (#ticket-pass). --}}
@once
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" defer></script>
        <script>
            document.addEventListener('click', function (e) {
                const button = e.target.closest('[data-save-ticket]');
                if (!button || typeof html2canvas === 'undefined') return;
                const label = button.querySelector('span');
                const original = label.textContent;
                label.textContent = 'Saving…';
                html2canvas(document.getElementById('ticket-pass'), { backgroundColor: '#ffffff', scale: 2, useCORS: true }).then(function (canvas) {
                    const link = document.createElement('a');
                    link.download = button.dataset.saveTicket;
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                }).finally(function () { label.textContent = original; });
            });
        </script>
    @endpush
@endonce
<button type="button" data-save-ticket="{{ $filename }}"
        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
    <x-ui.icon name="download" class="h-4 w-4" /><span>Save as image</span>
</button>
